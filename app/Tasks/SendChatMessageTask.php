<?php
/**
 * Created by PhpStorm.
 * User: lingan
 * Date: 2019/6/13
 * Time: 9:24 PM
 */

namespace App\Tasks;


use App\Services\SMSService;
use Illuminate\Support\Facades\Redis;
use LSwoole\Swoole\Task\Task;

class SendChatMessageTask extends Task
{

    /**
     * @return mixed|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $online_users = $this->data['online_users'];
        $send_data = $this->data['send_data'];

        $res = $this->sendChat($online_users, json_encode($send_data));
        $this->result = $res;
    }

    /**
     * @param array $users
     * @param string $message
     * @return string
     */
    private function sendChat(array $users, string $message)
    {
        $server = app('swoole');
        foreach ($users as $user) {
            if ($server->exist($user)) {
                try {
                    $server->push($user, $message);
                } catch (\Exception $exception) {
                    // 删除已断开的连接fd
                    Redis::connection()->srem('online_users', $user);
                }
            } else {
                // 删除已断开的连接fd
                Redis::connection()->srem('online_users', $user);
            }
        }
        return 'success';
    }

}