<?php
/**
 * Created by PhpStorm.
 * User: lingan
 * Date: 2019/6/14
 * Time: 10:31 PM
 */

namespace LSwoole\Swoole\ServerMonitor;

use LSwoole\Illuminate\Laravel;
use Swoole\Server;

abstract class ServerMonitor
{


    protected function __construct()
    {
    }

    abstract public static function monitor(Server $server, Laravel $laravel);


}