<?php
/**
 * Created by PhpStorm.
 * User: lingan
 * Date: 2019/6/14
 * Time: 10:35 PM
 */

namespace LSwoole\Swoole\ServerMonitor;

use LSwoole\Illuminate\Laravel;
use LSwoole\Request;
use LSwoole\Response;
use LSwoole\Swoole\Traits\ServerMonitorTrait;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Server;

class HttpServerMonitor extends ServerMonitor
{
    use ServerMonitorTrait;

    /**
     * @var Laravel
     */
    protected $laravel;

    /**
     * HttpServerMonitor constructor.
     * @param Laravel $laravel
     */
    protected function __construct(Laravel $laravel)
    {
        $this->laravel = $laravel;
    }

    /**
     * @param Server $server
     * @param Laravel $laravel
     */
    public static function monitor(Server $server, Laravel $laravel)
    {
        $self = new self($laravel);

        $server->on('request', [$self, 'onRequest']);

        self::registerCommonMonitor($server, $self);
    }


    /**
     * @param SwooleRequest $request
     * @param SwooleResponse $response
     * @throws \Exception
     */
    public function onRequest(SwooleRequest $request, SwooleResponse $response)
    {
        $illuminate_request = Request::toIlluminateRequest($request);

        // 处理请求
        $symfony_response = $this->laravel->http_kernel->handle($illuminate_request);


        // 处理返回
        $l_response = new Response($this->laravel, $response, $illuminate_request, $symfony_response);

        $response = $l_response->toSwooleResponse();
        $content = $l_response->getResponseContent();

        // 输出返回
        $response->end($content);
    }


}