<?php

if (!function_exists('verifyCodeKey')) {
    /**
     * @param string $mobile
     * @return string
     */
    function verifyCodeKey(string $mobile)
    {
        return "verify_code_{$mobile}";
    }
}

if (!function_exists('asyncTask')){
    /**
     * 触发异步任务
     * @param \LSwoole\Swoole\Task\Task $task
     * @return bool|Closure
     */
    function asyncTask(\LSwoole\Swoole\Task\Task $task){
        return LSwoole\Swoole\Task\Task::transfer($task);
    }
}