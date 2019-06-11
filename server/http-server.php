<?php

$host = "0.0.0.0";
$port = 9090;

$static_root = __DIR__ . '/../resources/live';

$http = new swoole_http_server($host, $port);

// 开启静态资源处理
$http->set([
    'enable_static_handler' => true,
    'document_root'         => $static_root,
]);

$http->on('request', function (swoole_http_request $request, swoole_http_response $response) {
});

$http->start();