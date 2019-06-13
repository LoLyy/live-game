<?php

$host = "0.0.0.0";
$port = 9090;

$static_root = __DIR__ . '/../resources/live';

$http = new swoole_http_server($host, $port);

// 开启静态资源处理
$http->set([
    'enable_static_handler' => true,
    'document_root'         => $static_root,
    'worker_num'            => 8,
]);


//工作进程启动
$http->on('workerStart', function ($server, $worker_id) {
    // 加载Laravel启动必须的文件
    require __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../bootstrap/app.php';
});


// 监听请求
$http->on('request', function (swoole_http_request $request, swoole_http_response $response) {

    // 将 swoole 的请求参数交给 laravel request
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


    \Illuminate\Http\Request::enableHttpMethodParameterOverride();
    $laravel_request = \Illuminate\Http\Request::createFromBase(new \Symfony\Component\HttpFoundation\Request($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER, $request->rawContent()));

    if (0 === strpos($laravel_request->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
        && in_array(strtoupper($laravel_request->server->get('REQUEST_METHOD', 'GET')), ['PUT', 'DELETE', 'PATCH'])
    ) {
        parse_str($laravel_request->getContent(), $data);
        $laravel_request->request = new \Symfony\Component\HttpFoundation\ParameterBag($data);
    }


    // laravel 内核处理请求
    $kernel = app()->make(Illuminate\Contracts\Http\Kernel::class);

    $laravel_response = $kernel->handle(
        $laravel_request
    );

    // 将 laravel 的响应交给 swoole 的响应处理 header & cookies
    collect($laravel_response->headers->allPreserveCaseWithoutCookies())->each(function ($values, $key) use ($response) {
        collect($values)->each(function ($value) use ($response, $key) {
            $response->header($key, $value);
        });
    });

    // cookies
    collect($laravel_response->headers->getCookies())->each(function ($cookie) use ($response) {
        $response->cookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
    });

    // 开启缓冲区  将laravel执行的输出放入缓冲区
    ob_start();
    $laravel_response->send();
    $kernel->terminate($laravel_request, $laravel_response);
    $result = ob_get_clean();

    // 输出返回
    $response->end($result);
});

$http->start();