<?php

namespace LSwoole\Swoole\Traits;

use Illuminate\Support\Facades\Log;
use LSwoole\Swoole\Task\Task;
use Swoole\Server;

trait ServerMonitorTrait
{
    /**
     * @param Server $server
     * @param $obj
     */
    public static function registerCommonMonitor(Server $server, $obj)
    {
        $server->on('start', [$obj, 'onStart']);
        $server->on('close', [$obj, 'onClose']);

        // 监听工作进程启动
        $server->on('workerStart', [$obj, 'onWorkerStart']);

        $server->on('task', [$obj, 'onTask']);
        $server->on('finish', [$obj, 'onFinish']);
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
        // todo connected close
    }
}