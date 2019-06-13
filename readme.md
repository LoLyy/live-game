# 赛事直播项目

> 基于 Laravel + Swoole

## 安装项目依赖

```bash
composer install
```

## 启动 web server

```bash

php lswoole/run.php

```

## 使用异步任务

```php

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

## 使用静态方法

 \LSwoole\Swoole\Task\Task::transfer(new \App\Tasks\TestTask("test for async task"));


## 使用辅助函数

    asyncTask(new \App\Tasks\TestTask("test for async task"));
```