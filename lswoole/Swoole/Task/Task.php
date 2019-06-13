<?php

namespace LSwoole\Swoole\Task;

use Illuminate\Support\Facades\Log;

abstract class Task
{

    protected $delay = null;
    protected $result = null;
    protected $data;

    /**
     * Task constructor.
     * @param $data
     */
    public function __construct($data)
    {
        if (!$data) {
            throw new \InvalidArgumentException("task param is not empty");
        }

        $this->data = $data;
    }

    /**
     * @param int $second
     * @return $this
     */
    public function delay(int $second)
    {
        if ($second <= 0) {
            throw new \InvalidArgumentException("delay time must be greater than 0");
        }
        $this->delay = $second * 1000; // 换成毫秒

        return $this;
    }

    /**
     * transfer to swoole task
     * @param Task $task
     * @return bool|\Closure
     */
    public static function transfer(Task $task)
    {
        /**
         * @var \swoole_http_server
         */
        $swoole = app('swoole');

        $transfer = function () use ($swoole, $task) {
            return $swoole->task($task);
        };

        if ($task->delay && $task->delay > 0) {
            swoole_timer_after($task->delay, $transfer);
            return true;
        }

        return $transfer();
    }

    /**
     * handle task
     * @return mixed
     */
    abstract public function handle();

    /**
     * finish task
     * @return mixed
     */
    public function finish()
    {
        Log::info("task finished class:" . class_basename($this) . "  ||  params：" . json_encode($this->data) . "  ||  result: " . json_encode($this->result));
    }
}