<?php

class WebServer
{
    const HOST = "0.0.0.0";
    const PORT = 9090;
    const WORKER_NUM = 8;
    const IS_ENABLE_STATIC_HANDLER = true;
    const STATIC_ROOT = __DIR__ . '/../resources/live';

    public $server = null;

    /**
     * WebServer constructor.
     */
    public function __construct()
    {
        $this->server = new swoole_http_server(self::HOST, self::PORT);
        $this->server->set([
            'enable_static_handler' => self::IS_ENABLE_STATIC_HANDLER,
            'document_root'         => self::STATIC_ROOT,
            'worker_num'            => self::WORKER_NUM,
        ]);


    }

    /**
     * 启动 server
     */
    public function run()
    {
        // 监听工作进程启动
        $this->server->on('workerStart', [$this, 'onWorkerStart']);
        // 监听请求
        $this->server->on('request', [$this, 'onRequest']);

        // 启动
        $this->server->start();
    }

    /**
     * 进程启动
     * @param swoole_server $server
     * @param $worker_id
     */
    public function onWorkerStart(swoole_server $server, $worker_id)
    {
        // 加载Laravel启动必须的文件
        require __DIR__ . '/../vendor/autoload.php';
        // get a Application
        require_once __DIR__ . '/../bootstrap/app.php';
    }

    /**
     * 监听请求
     * @param swoole_http_request $swoole_request
     * @param swoole_http_response $swoole_response
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function onRequest(swoole_http_request $swoole_request, swoole_http_response $swoole_response)
    {
        $laravel_request = $this->swooleRequestTransferToLaravelRequest($swoole_request);

        // 获取 laravel http kernel
        $kernel = app()->make(\Illuminate\Contracts\Http\Kernel::class);

        // 处理请求
        $laravel_response = $kernel->handle($laravel_request);

        $swoole_response = $this->laravelResponseTransferToSwoole($swoole_response, $laravel_response);


        $content = $this->getLaravelResponseContent($kernel, $laravel_request, $laravel_response);

        // 输出返回
        $swoole_response->end($content);
    }

    /**
     * 获取 laravel 响应返回的内容
     * @param $kernel
     * @param $laravel_request
     * @param $laravel_response
     * @return false|string
     */
    private function getLaravelResponseContent($kernel, $laravel_request, $laravel_response)
    {
        // 开启缓冲区  将laravel执行的输出放入缓冲区
        ob_start();

        $laravel_response->send();
        $kernel->terminate($laravel_request, $laravel_response);

        return ob_get_clean();
    }

    /**
     * 将 laravel 的响应交给 swoole 的响应处理 header & cookies
     * @param swoole_http_response $swoole_response
     * @param \Symfony\Component\HttpFoundation\Response $laravel_response
     * @return swoole_http_response
     */
    private function laravelResponseTransferToSwoole(swoole_http_response $swoole_response, \Symfony\Component\HttpFoundation\Response $laravel_response)
    {
        // 将 laravel 的响应交给 swoole 的响应处理 header & cookies
        collect($laravel_response->headers->allPreserveCaseWithoutCookies())->each(function ($values, $key) use ($swoole_response) {
            collect($values)->each(function ($value) use ($swoole_response, $key) {
                $swoole_response->header($key, $value);
            });
        });

        // cookies
        collect($laravel_response->headers->getCookies())->each(function ($cookie) use ($swoole_response) {
            $swoole_response->cookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
        });

        return $swoole_response;
    }

    /**
     * 将 swoole 的请求参数交给 laravel request
     * @param swoole_http_request $request
     * @return \Illuminate\Http\Request
     */
    private function swooleRequestTransferToLaravelRequest(swoole_http_request $request)
    {
        $_GET = $request->get ?? [];
        $_POST = $request->post ?? [];
        $_COOKIE = $request->cookie ?? [];
        $_FILES = $request->files ?? [];
        $server = collect($request->server ?? [])->mapWithKeys(function ($value, $key) {
            return [strtoupper($key) => $value];
        })->toArray();
        $header = collect($request->header ?? [])->mapWithKeys(function ($value, $key) {
            return ['HTTP_' . str_replace('-', '_', strtoupper($key)) => $value];
        })->toArray();
        $cookie = [
            "HTTP_COOKIE" => collect($_COOKIE)->transform(function ($v, $k) {
                return $k . '=' . $v;
            })->implode("; "),
        ];
        $_SERVER = array_merge($server, $header, $cookie, ['argv' => []]);


        // Initialize laravel request
        \Illuminate\Http\Request::enableHttpMethodParameterOverride();

        $laravel_request = \Illuminate\Http\Request::createFromBase(new \Symfony\Component\HttpFoundation\Request($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER, $request->rawContent()));

        if (0 === strpos($laravel_request->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
            && in_array(strtoupper($laravel_request->server->get('REQUEST_METHOD', 'GET')), ['PUT', 'DELETE', 'PATCH'])
        ) {
            parse_str($laravel_request->getContent(), $data);
            $laravel_request->request = new \Symfony\Component\HttpFoundation\ParameterBag($data);
        }


        return $laravel_request;
    }

}

// 创建http-server 实例

$server = new WebServer();
$server->run();