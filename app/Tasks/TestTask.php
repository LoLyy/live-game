<?php
/**
 * Created by PhpStorm.
 * User: lingan
 * Date: 2019/6/13
 * Time: 9:24 PM
 */

namespace App\Tasks;


use LSwoole\Swoole\Task\Task;

class TestTask extends Task
{

    /**
     * @return $this|mixed
     */
    public function handle()
    {
        // 任务传递的参数
        $this->data;

        // 模拟耗时功能
        sleep(3);

        $this->result = "测试任务完成";
    }

}