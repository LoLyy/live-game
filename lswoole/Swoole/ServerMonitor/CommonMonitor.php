<?php
/**
 * Created by PhpStorm.
 * User: lingan
 * Date: 2019/6/14
 * Time: 10:50 PM
 */

namespace LSwoole\Swoole\ServerMonitor;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use LSwoole\Illuminate\Laravel;
use LSwoole\Swoole\Task\Task;
use Swoole\Server;
use Swoole\WebSocket\Server as WebSocketServer;


class CommonMonitor extends ServerMonitor
{

    /**
     * @param Server $server
     * @param Laravel|null $laravel
     */
    public static function monitor(Server $server, Laravel $laravel = null)
    {
        $self = new self();

        $server->on('start', [$self, 'onStart']);
        $server->on('close', [$self, 'onClose']);

        // 监听工作进程启动
        $server->on('workerStart', [$self, 'onWorkerStart']);

        $server->on('task', [$self, 'onTask']);
        $server->on('finish', [$self, 'onFinish']);
    }

    /**
     * @param Server $server
     */
    public function onStart(Server $server)
    {
        echo "主进程启动：" . $server->master_pid . PHP_EOL;
        echo "管理进程启动：" . $server->manager_pid . PHP_EOL;
    }

    /**
     * @param Server $server
     * @param $worker_id
     */
    public function onWorkerStart(Server $server, $worker_id)
    {
        if ($server->taskworker) {
            $worker_name = "异步任务进程";
        } else {
            $worker_name = "子进程";
        }
        echo "$worker_name {$worker_id} 启动：" . $server->worker_pid . PHP_EOL;
    }


    /**
     * @param Server $server
     * @param int $task_id
     * @param int $worker_id
     * @param $data
     * @return Task
     */
    public function onTask(Server $server, int $task_id, int $worker_id, $data)
    {

        /**
         * @var Task $data
         */
        try {
            if ($data instanceof Task) {
                $data->handle();
            }
        } catch (\Exception $exception) {
            Log::error('task handle error: ' . $exception->getMessage());
        }
        return $data;
    }


    /**
     * @param Server $server
     * @param int $task_id
     * @param $data
     */
    public function onFinish(Server $server, int $task_id, $data)
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
     * @param $server
     * @param int $fd
     */
    public function onClose($server, int $fd)
    {
        if ($server instanceof WebSocketServer) {
            Redis::connection()->srem('online_users',[$fd]);
        }

    }

}