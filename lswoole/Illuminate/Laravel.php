<?php

namespace LSwoole\Illuminate;

use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Application;
use Swoole\WebSocket\Server;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class Laravel
{

    /**
     * @var Kernel $http_kernel
     */
    public $http_kernel;
    public $app;
    private $swoole;
    protected $conf;

    /**
     * Laravel constructor.
     * @param $conf
     * @param $swoole
     */
    private function __construct($conf, $swoole)
    {
        if (!$conf || !is_array($conf) || !$swoole || !($swoole instanceof Server)) {
            throw new InvalidParameterException("参数错误");
        }
        $this->swoole = $swoole;
        $this->conf = $conf;
    }


    /**
     * @param $conf
     * @param $swoole
     * @return Laravel
     */
    public static function create($conf, $swoole)
    {
        return new self($conf, $swoole);
    }


    /**
     * init laravel
     */
    public function initLaravel()
    {
        $this->autoload();
        list($this->app, $this->http_kernel) = $this->createAppKernel();

        return $this;
    }

    /**
     * autoload
     */
    protected function autoload()
    {
        // autoload
        try {
            require($this->conf['root_path'] . '/vendor/autoload.php');
        } catch (\Exception $exception) {
            new InvalidParameterException("项目根目录地址错误");
        }
    }


    /**
     * @return array
     */
    protected function createAppKernel()
    {
        // get a Application
        try {
            /**
             * @var Application $app
             */
            $app = require($this->conf['root_path'] . '/bootstrap/app.php');
        } catch (\Exception $exception) {
            new InvalidParameterException("项目根目录地址错误");
        }

        //  laravel http kernel
        $http_kernel = $app->make(HttpKernel::class);

        // laravel  console kernel bootstrap
        $app->make(ConsoleKernel::class)->bootstrap();

        // bind swoole to laravel
        $app->singleton('swoole', function () {
            return $this->swoole;
        });

        return [$app, $http_kernel];
    }


}