<?php

namespace LSwoole\Swoole;

use Illuminate\Support\Facades\Log;
use LSwoole\Illuminate\Laravel;
use LSwoole\Swoole\ServerMonitor\CommonMonitor;
use LSwoole\Swoole\ServerMonitor\HttpServerMonitor;
use LSwoole\Swoole\Task\Task;
use Swoole\WebSocket\Server as SwooleServer;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use  \Illuminate\Http\Request as IlluminateRequest;

class WebSocketServer
{
    const HOST = "0.0.0.0";
    const PORT = 9090;
    const WORKER_NUM = 4;
    const TASK_WORKER_NUM = 2;
    const IS_ENABLE_STATIC_HANDLER = true;
    const STATIC_ROOT = __DIR__ . '/../../resources/live';
    const ROOT_PATH = __DIR__ . '/../../';

    public $server = null;

    protected $http_kernel;
    /**
     * @var Laravel $laravel
     */
    protected $laravel;

    /**
     * WebServer constructor.
     */
    public function __construct()
    {
        $this->server = new SwooleServer(self::HOST, self::PORT);

        $this->server->set([
            'enable_static_handler' => self::IS_ENABLE_STATIC_HANDLER,
            'document_root'         => self::STATIC_ROOT,
            'worker_num'            => self::WORKER_NUM,
            'task_worker_num'       => self::TASK_WORKER_NUM,
        ]);

        $config = [
            'root_path' => self::ROOT_PATH,
        ];

        $this->laravel = Laravel::create($config, $this->server)->initLaravel();
    }

    /**
     * 启动 server
     */
    public function run()
    {

        CommonMonitor::monitor($this->server);

        HttpServerMonitor::monitor($this->server, $this->laravel);

        // 监听 WebSocket 连接
        $this->server->on('open', [$this, 'onOpen']);

        //  监听 WebSocket 消息
        $this->server->on('message', [$this, 'onMessage']);

        // 启动
        $this->server->start();
    }


    public function onOpen(SwooleServer $server, $fd)
    {
//        dump($fd);
    }


    public function onMessage(SwooleServer $server, $fd, $data)
    {
        dump($data);
    }


}