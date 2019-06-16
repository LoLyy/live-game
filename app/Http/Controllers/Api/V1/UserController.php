<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Faker\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;


class UserController extends Controller
{

    /**
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $mobile = $request['mobile'];
        $code = $request['code'];

        if (Cache::get(verifyCodeKey($mobile)) != $code) {
            return $this->fail(RESPONSE_CODE_INVALID_VERIFY_CODE, RESPONSE_MESSAGES[RESPONSE_CODE_INVALID_VERIFY_CODE]);
        }

        // register user or get user info
        $factory = Factory::create('zh_CN');
        if (!$user = Redis::connection()->hgetall($mobile)) {
            $id = uniqid();
            $username = $factory->name();
            $token = md5($mobile);
            $background = $factory->safeHexColor;
            $color = colorReverse($background);
            $avatar = 'https://picsum.photos/120/80?time=' . time();
            $user = compact('id', 'username', 'token','avatar', 'background','color');
            Redis::connection()->hmset($mobile, $user);
        }

        return $this->success(compact('user'));
    }
}
