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

if (!function_exists('asyncTask')) {
    /**
     * 触发异步任务
     * @param \LSwoole\Swoole\Task\Task $task
     * @return bool|Closure
     */
    function asyncTask(\LSwoole\Swoole\Task\Task $task)
    {
        return LSwoole\Swoole\Task\Task::transfer($task);
    }
}


if (!function_exists('colorReverse')) {

    /**
     * @param string $color
     * @return string
     */
    function colorReverse(string $color)
    {
        $color = "0x" . str_replace('#', '', $color);
         $str = "000000" .  dechex(0XFFFFFF - hexdec($color));

         $len = strlen($str);
         return "#" . substr($str,$len - 6,$len);

    }
}