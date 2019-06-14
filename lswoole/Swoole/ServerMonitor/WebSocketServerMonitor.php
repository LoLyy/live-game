<?php

namespace LSwoole\Swoole\ServerMonitor;


use LSwoole\Illuminate\Laravel;
use Swoole\Server;

class WebSocketServerMonitor extends ServerMonitor
{

    /**
     * @param Server $server
     * @param Laravel $laravel
     */
    public static function monitor(Server $server, Laravel $laravel = null)
    {
        $self = new self();
        // 监听 WebSocket 连接
        $server->on('open', [$self, 'onOpen']);

        //  监听 WebSocket 消息
        $server->on('message', [$self, 'onMessage']);

    }


    /**
     * @param \Swoole\WebSocket\Server $server
     * @param $frame
     */
    public function onOpen(\Swoole\WebSocket\Server $server, $frame)
    {
        dump($frame->fd);
    }


    /**
     * @param \Swoole\WebSocket\Server $server
     * @param $frame
     */
    public function onMessage(\Swoole\WebSocket\Server $server, $frame)
    {
        switch ($frame->data['event'] ?? '') {
            case 'ping':
                break;
            default:
                $server->push($frame->fd, 'pong');
                break;
        }
    }
}