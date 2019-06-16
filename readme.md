# 赛事直播项目

本项目项目接口基于 Laravel + Swoole，Websocket Server & Http Server 基于 [Swoole](https://wiki.swoole.com)，除了 Server 还使用到了 Swoole 的异步任务特性。

前端页面参考了慕课网`Singwa`老师的课程 [Swoole 入门到实战打造高性能赛事直播平台](https://coding.imooc.com/class/197.html),页面渲染使用前端 `MVVM` 框架 [VueJs](https://vuejs.org)。

## 预览

<figure class="third">
  <img src="https://s2.ax1x.com/2019/06/16/V7M51x.md.png" width="300">

<img src="https://s2.ax1x.com/2019/06/16/V7MojK.md.png" width="300">

<img src="https://s2.ax1x.com/2019/06/16/V7MIc6.md.png" width="300">
</figure>

## 安装项目依赖

```bash
composer install
```

## 启动 WebSocket Server

```bash

php lswoole/run.php

```

## 基于 `Swoole` 异步任务的封装的使用

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

## TODO

-   代码优化
-   Swoole 其他特性的使用

## 备注

本项目为上手 `Swoole` 的学习代码，仅供学习参考使用。
