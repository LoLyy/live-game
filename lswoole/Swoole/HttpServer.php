<?php

namespace LSwoole\Swoole;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Log;
use LSwoole\Swoole\Task\Task;
use Swoole\Http\Server as SwooleHttpServer;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use  \Illuminate\Http\Request as IlluminateRequest;

class HttpServer
{
    const HOST = "0.0.0.0";
    const PORT = 9090;
    const WORKER_NUM = 4;
    const TASK_WORKER_NUM = 2;
    const IS_ENABLE_STATIC_HANDLER = true;
    const STATIC_ROOT = __DIR__ . '/../../resources/live';

    public $server = null;

    /**
     * WebServer constructor.
     */
    public function __construct()
    {
        $this->server = new SwooleHttpServer(self::HOST, self::PORT);

        $this->server->set([
            'enable_static_handler' => self::IS_ENABLE_STATIC_HANDLER,
            'document_root'         => self::STATIC_ROOT,
            'worker_num'            => self::WORKER_NUM,
            'task_worker_num'       => self::TASK_WORKER_NUM,
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
        // 监听task
        $this->server->on('task', [$this, 'onTask']);
        // 监听 task 结束
        $this->server->on('finish', [$this, 'onFinish']);

        // 启动
        $this->server->start();
    }

    /**
     * @param SwooleHttpServer $server
     * @param $worker_id
     */
    public function onWorkerStart(SwooleHttpServer $server, $worker_id)
    {
        // 加载Laravel启动必须的文件
//        require __DIR__ . '/../vendor/autoload.php';
        // get a Application
        require_once __DIR__ . '/../../bootstrap/app.php';

        // bind swoole to laravel
        app()->singleton('swoole', function () {
            return $this->server;
        });

    }

    /**
     * 监听请求
     * @param SwooleRequest $swoole_request
     * @param SwooleResponse $swoole_response
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function onRequest(SwooleRequest $swoole_request, SwooleResponse $swoole_response)
    {
        $illuminate_request = $this->swooleRequestTransferToLaravelRequest($swoole_request);

        // 获取 laravel http kernel
        $kernel = app()->make(Kernel::class);

        // 处理请求
        $symfony_response = $kernel->handle($illuminate_request);

        $swoole_response = $this->laravelResponseTransferToSwoole($swoole_response, $symfony_response);

        $content = $this->getLaravelResponseContent($kernel, $illuminate_request, $symfony_response);

        // 输出返回
        $swoole_response->end($content);
    }


    /**
     * 监听任务
     * @param SwooleHttpServer $server
     * @param int $task_id
     * @param int $worker_id
     * @param $data
     * @return Task
     */
    public function onTask(SwooleHttpServer $server, int $task_id, int $worker_id, $data)
    {
        /**
         * @var Task $data
         */
        try {
            if ($data instanceof Task) {
                $data->handle();
                return $data;
            }
        } catch (\Exception $exception) {
            Log::error('task handle error: ' . $exception->getMessage());
        }
    }

    /**
     * 任务完成
     * @param SwooleHttpServer $server
     * @param int $task_id
     * @param $data
     */
    public function onFinish(SwooleHttpServer $server, int $task_id, $data)
    {
        try {
            if ($data instanceof Task) {
                $data->finish();
            }
        } catch (\Exception $exception) {
            Log::error('task finish error: ' . $exception->getMessage());
        }
    }

    /**
     * 获取 laravel 响应返回的内容
     * @param $kernel
     * @param $illuminate_request
     * @param $symfony_response
     * @return false|string
     */
    private function getLaravelResponseContent(Kernel $kernel, IlluminateRequest $illuminate_request, SymfonyResponse $symfony_response)
    {
        // 开启缓冲区  将laravel执行的输出放入缓冲区
        ob_start();

        $symfony_response->send();
        $kernel->terminate($illuminate_request, $symfony_response);

        return ob_get_clean();
    }

    /**
     * 将 laravel 的响应交给 swoole 的响应处理 header & cookies
     * @param SwooleResponse $swoole_response
     * @param SymfonyResponse $symfony_response
     * @return SwooleResponse
     */
    private function laravelResponseTransferToSwoole(SwooleResponse $swoole_response, SymfonyResponse $symfony_response)
    {
        // status
        $swoole_response->status($symfony_response->getStatusCode());

        // 将 laravel 的响应交给 swoole 的响应处理 header & cookies
        collect($symfony_response->headers->allPreserveCaseWithoutCookies())->each(function ($values, $key) use ($swoole_response) {
            collect($values)->each(function ($value) use ($swoole_response, $key) {
                $swoole_response->header($key, $value);
            });
        });

        // cookies
        collect($symfony_response->headers->getCookies())->each(function ($cookie) use ($swoole_response) {
            /**
             * @var \Symfony\Component\HttpFoundation\Cookie $cookie
             */
            $swoole_response->cookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
        });

        return $swoole_response;
    }

    /**
     * 将 swoole 的请求参数交给 laravel request
     * @param SwooleRequest $swoole_request
     * @return \Illuminate\Http\Request
     */
    private function swooleRequestTransferToLaravelRequest(SwooleRequest $swoole_request)
    {
        $_GET = $swoole_request->get ?? [];
        $_POST = $swoole_request->post ?? [];
        $_COOKIE = $swoole_request->cookie ?? [];
        $_FILES = $swoole_request->files ?? [];
        $server = collect($swoole_request->server ?? [])->mapWithKeys(function ($value, $key) {
            return [strtoupper($key) => $value];
        })->toArray();
        $header = collect($swoole_request->header ?? [])->mapWithKeys(function ($value, $key) {
            return ['HTTP_' . str_replace('-', '_', strtoupper($key)) => $value];
        })->toArray();
        $cookie = [
            "HTTP_COOKIE" => collect($_COOKIE)->transform(function ($v, $k) {
                return $k . '=' . $v;
            })->implode("; "),
        ];
        $_SERVER = array_merge($server, $header, $cookie, ['argv' => []]);


        // Initialize laravel request
        IlluminateRequest::enableHttpMethodParameterOverride();

        $illuminate_request = IlluminateRequest::createFromBase(new SymfonyRequest($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER, $swoole_request->rawContent()));

        if (0 === strpos($illuminate_request->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
            && in_array(strtoupper($illuminate_request->server->get('REQUEST_METHOD', 'GET')), ['PUT', 'DELETE', 'PATCH'])
        ) {
            parse_str($illuminate_request->getContent(), $data);

            $illuminate_request->request = new ParameterBag($data);
        }


        return $illuminate_request;
    }

}