<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    /**
     * 成功返回
     * @param array $data
     * @param int $code
     * @param string $message
     * @param array $headers
     * @return \Illuminate\Http\JsonResponse
     */
    public function success(array $data = [], int $code = RESPONSE_CODE_SUCCESS, string $message = RESPONSE_MESSAGES[RESPONSE_CODE_SUCCESS], $headers = [])
    {
        $response = [
            'code'    => $code,
            'data'    => $data ?? (object)[],
            'message' => $message,
        ];
        return response()->json($response, Response::HTTP_OK, $headers);
    }


    /**
     * 失败返回
     * @param int $code
     * @param string $message
     * @param int $http_code
     * @param array $data
     * @param array $headers
     * @return \Illuminate\Http\JsonResponse
     */
    public function fail(int $code = RESPONSE_CODE_FAILED, string $message = RESPONSE_MESSAGES[RESPONSE_CODE_FAILED], $http_code = Response::HTTP_OK, array $data = [], $headers = [])
    {
        $response = [
            RESPONSE_CODE    => $code,
            RESPONSE_DATA    => $data ?? (object)[],
            RESPONSE_MESSAGE => $message,
        ];
        return response()->json($response, $http_code, $headers);
    }
}
