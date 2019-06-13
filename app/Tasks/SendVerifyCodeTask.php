<?php
/**
 * Created by PhpStorm.
 * User: lingan
 * Date: 2019/6/13
 * Time: 9:24 PM
 */

namespace App\Tasks;


use App\Services\SMSService;
use LSwoole\Swoole\Task\Task;

class SendVerifyCodeTask extends Task
{

    /**
     * @return mixed|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        if (!$this->data['code'] || !$this->data['mobile']) {
            throw new \InvalidArgumentException("参数错误");
        }

        // send sms verify code
//        $content = str_replace('#code#',$code,SMS_VERIFY_CODE_TEMPLATE);
//        $res = SMSService::create()->mock()->sms($content)->send($mobile);

        // send voice verify code
        $res = SMSService::create()->mock()->voice($this->data['code'])->send($this->data['mobile']);

        // result
        $this->result = $res;
    }

}