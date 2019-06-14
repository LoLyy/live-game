<?php
/**
 * Created by PhpStorm.
 * User: lingan
 * Date: 2019/6/15
 * Time: 12:12 AM
 */

namespace LSwoole;

use Swoole\Http\Request as SwooleRequest;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use  \Illuminate\Http\Request as IlluminateRequest;

class Request
{

    /**
     * swoole request to Illuminate request
     * @param SwooleRequest $request
     * @return \Illuminate\Http\Request
     */
    public static function toIlluminateRequest(SwooleRequest $request)
    {
        $_GET = $request->get ?? [];
        $_POST = $request->post ?? [];
        $_COOKIE = $request->cookie ?? [];
        $_FILES = $request->files ?? [];
        $server = collect($request->server ?? [])->mapWithKeys(function ($value, $key) {
            return [strtoupper($key) => $value];
        })->toArray();
        $header = collect($request->header ?? [])->mapWithKeys(function ($value, $key) {
            return ['HTTP_' . str_replace('-', '_', strtoupper($key)) => $value];
        })->toArray();
        $cookie = [
            "HTTP_COOKIE" => collect($_COOKIE)->transform(function ($v, $k) {
                return $k . '=' . $v;
            })->implode("; "),
        ];
        $_SERVER = array_merge($server, $header, $cookie, ['argv' => []]);


        // Initialize laravel request
        IlluminateRequest::enableHttpMethodParameterOverride();

        $illuminate_request = IlluminateRequest::createFromBase(new SymfonyRequest($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER, $request->rawContent()));

        if (0 === strpos($illuminate_request->headers->get('CONTENT_TYPE'), 'application/x-www-form-urlencoded')
            && in_array(strtoupper($illuminate_request->server->get('REQUEST_METHOD', 'GET')), ['PUT', 'DELETE', 'PATCH'])
        ) {
            parse_str($illuminate_request->getContent(), $data);

            $illuminate_request->request = new ParameterBag($data);
        }

        return $illuminate_request;
    }

}