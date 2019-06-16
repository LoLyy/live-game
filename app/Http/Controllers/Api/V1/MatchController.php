<?php

namespace App\Http\Controllers\Api\V1;

use App\User;
use Faker\Factory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class MatchController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function publish()
    {
        $facker = Factory::create('zh_CN');
        $message = [
            'id'      => uniqid(),
            'author'  => $facker->name,
            'avatar'  => 'https://picsum.photos/40?time=' . time(),
            'content' => $facker->paragraph,
            'image'   => ['https://picsum.photos/120/80?time=' . time(), ''][rand(0, 1)],
        ];

        $online_users = User::getOnlineUsers();

        foreach ($online_users as $user) {
            if (app('swoole')->exist($user)) {
                app('swoole')->push($user, json_encode([
                    'event'    => 'match',
                    'message' => $message,
                ]));
            } else {
                // 删除已断开的连接fd
                Redis::connection()->srem('online_users', $user);
            }

        }

        return $this->success();

    }
}
