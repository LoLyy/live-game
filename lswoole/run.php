<?php

include_once __DIR__ . '/../vendor/autoload.php';

// 创建http-server 实例
//use LSwoole\Swoole\HttpServer;
//
//$server = new HttpServer();
//$server->run();

// 创建 web socket-server 实例
use LSwoole\Swoole\WebSocketServer;

$server = new WebSocketServer();
$server->run();
