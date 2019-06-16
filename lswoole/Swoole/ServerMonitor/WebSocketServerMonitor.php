<?php

namespace LSwoole\Swoole\ServerMonitor;

use App\Tasks\SendChatMessageTask;
use App\Tasks\SendWebSocketMessageTask;
use App\User;
use Illuminate\Support\Facades\Redis;
use LSwoole\Illuminate\Laravel;
use LSwoole\Swoole\Task\Task;
use Swoole\Server;
use Swoole\WebSocket\Server as WebSocketServer;


class WebSocketServerMonitor extends HttpServerMonitor
{

    /**
     * @param Server $server
     * @param Laravel $laravel
     */
    public static function monitor(Server $server, Laravel $laravel)
    {

        $self = new self($laravel);

        // 监听 WebSocket 连接
        $server->on('open', [$self, 'onOpen']);

        //  监听 WebSocket 消息
        $server->on('message', [$self, 'onMessage']);

        parent::monitor($server, $laravel);
    }


    /**
     * @param \Swoole\WebSocket\Server $server
     * @param $frame
     */
    public function onOpen(\Swoole\WebSocket\Server $server, $frame)
    {
        Redis::connection()->sadd('online_users', [$frame->fd]);
    }


    /**
     * @param \Swoole\WebSocket\Server $server
     * @param $frame
     */
    public function onMessage(\Swoole\WebSocket\Server $server, $frame)
    {
        $data = json_decode($frame->data,true);

        switch ($data['event'] ?? '') {
            case 'chat':
                $message = $data['message'];
                $online_users = User::getOnlineUsers([$frame->fd]);
                $send_data = [
                    'event'=>'chat',
                    'message'=>$message
                ];
                Task::transfer(new SendChatMessageTask(compact('online_users', 'send_data')));
                break;
            case 'ping':
            default:
                $server->push($frame->fd, json_encode([
                    'event'    => 'heart',
                    'message' => 'pong',
                ]));
                break;
        }
    }

    /**
     * @param $server
     * @param int $fd
     */
    public function onClose($server, $fd)
    {
//        if ($server instanceof WebSocketServer) {
            Redis::connection()->srem('online_users', [$fd]);
//        }
        dump($fd);

    }


}