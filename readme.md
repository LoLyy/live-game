# 赛事直播项目

本项目项目接口基于 Laravel + Swoole，Websocket Server & Http Server 基于 [Swoole](https://wiki.swoole.com)，除了 Server 还使用到了 Swoole 的异步任务特性。

前端页面参考了慕课网`Singwa`老师的课程 [Swoole 入门到实战打造高性能赛事直播平台](https://coding.imooc.com/class/197.html),页面渲染使用前端 `MVVM` 框架 [VueJs](https://vuejs.org)。

## 预览

|                       Login                       |                       Match Info                       |                       Chat                       |
| :-----------------------------------------------: | :-----------------------------------------------: | :-----------------------------------------------: |
| ![](https://graph.linganmin.cn/190725/49e40fafa8339cf90d39438452fce72e?x-oss-process=image/format,webp/quality,q_60) | ![](https://graph.linganmin.cn/190725/fdfe1c9ed8c723c68191e828b34bca3b?x-oss-process=image/format,webp/quality,q_60) | ![](https://graph.linganmin.cn/190725/191bbf7b2c4c535b9d5b2b87f72929c4?x-oss-process=image/format,webp/quality,q_60) |

## 环境

- PHP

  ```bash
  $ php -v

  PHP 7.3.3 (cli) (built: Mar  8 2019 16:40:07) ( NTS )
  Copyright (c) 1997-2018 The PHP Group
  Zend Engine v3.3.3, Copyright (c) 1998-2018 Zend Technologies with Zend OPcache v7.3.3, Copyright (c) 1999-2018, by Zend Technologies
  ```

- Swoole

  ```bash
  $ php --ri swoole

  swoole

  Swoole => enabled
  Author => Swoole Team <team@swoole.com>
  Version => 4.3.4
  Built => Jun 10 2019 15:59:43
  coroutine => enabled
  kqueue => enabled
  rwlock => enabled
  http2 => enabled
  pcre => enabled
  zlib => enabled
  brotli => enabled
  async_redis => enabled

  Directive => Local Value => Master Value
  swoole.enable_coroutine => On => On
  swoole.display_errors => On => On
  swoole.use_shortname => On => On
  swoole.unixsock_buffer_size => 262144 => 262144
  ```

- Composer

  ```bash
  $ composer -V
  Composer version 1.8.4 2019-02-11 10:52:10
  ```

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

- 代码优化
- Swoole 其他特性的使用

## 备注

本项目为上手 `Swoole` 的学习代码，仅供学习参考使用。
