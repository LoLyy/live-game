<?php
/**
 * Created by PhpStorm.
 * User: lingan
 * Date: 2019/6/15
 * Time: 12:28 AM
 */

namespace LSwoole;

use LSwoole\Illuminate\Laravel;
use Swoole\Http\Response as SwooleResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use  \Illuminate\Http\Request as IlluminateRequest;

class Response
{

    /**
     * @var Laravel
     */
    protected $laravel;
    /**
     * @var SwooleResponse
     */
    protected $response;
    /**
     * @var IlluminateRequest
     */
    protected $illuminate_request;
    /**
     * @var SymfonyResponse
     */
    protected $symfony_response;

    /**
     * Response constructor.
     * @param Laravel $laravel
     * @param SwooleResponse $response
     * @param IlluminateRequest $illuminate_request
     * @param SymfonyResponse $symfony_response
     */
    public function __construct(Laravel $laravel, SwooleResponse $response, IlluminateRequest $illuminate_request, SymfonyResponse $symfony_response)
    {
        $this->laravel = $laravel;
        $this->response = $response;
        $this->illuminate_request = $illuminate_request;
        $this->symfony_response = $symfony_response;
    }


    /**
     * @return false|string
     */
    public function getResponseContent()
    {
        // 开启缓冲区  将laravel执行的输出放入缓冲区
        ob_start();

        $this->symfony_response->send();
        $this->laravel->http_kernel->terminate($this->illuminate_request, $this->symfony_response);

        return ob_get_clean();
    }


    /**
     * @return SwooleResponse
     */
    public function toSwooleResponse()
    {
        // status
        $this->response->status($this->symfony_response->getStatusCode());

        // 将 laravel 的响应交给 swoole 的响应处理 header & cookies
        collect($this->symfony_response->headers->allPreserveCaseWithoutCookies())->each(function ($values, $key) {
            collect($values)->each(function ($value) use ($key) {
                $this->response->header($key, $value);
            });
        });

        // cookies
        collect($this->symfony_response->headers->getCookies())->each(function ($cookie) {
            /**
             * @var \Symfony\Component\HttpFoundation\Cookie $cookie
             */
            $this->response->cookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
        });

        return $this->response;
    }
}