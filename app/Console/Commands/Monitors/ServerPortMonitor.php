<?php

namespace App\Console\Commands\Monitors;

use Illuminate\Console\Command;

class ServerPortMonitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:server';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '没两秒检测一下当前端口监听是否出问题了';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        swoole_timer_after(2000, function () {
            $this->monitor();
        });
    }

    /**
     *
     */
    private function monitor()
    {
        $command = "lsof -i:9090 | grep php | wc -l";
        $res = shell_exec($command);

        if (!trim($res)) {
            // 系统报警
            $this->comment('服务挂啦');
        }
    }
}
