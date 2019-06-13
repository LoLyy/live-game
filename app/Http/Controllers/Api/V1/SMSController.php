<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\SMSSendRequest;
use App\Services\SMSService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class SMSController extends Controller
{
    public function sms(SMSSendRequest $request)
    {
        if (!$mobile = $request->get('mobile')) {
            return $this->fail(RESPONSE_CODE_INVALID_PARAM, RESPONSE_MESSAGES[RESPONSE_CODE_INVALID_PARAM]);
        }
        $code = random_int(10000, 99999);

        // send sms verify code
//        $content = str_replace('#code#',$code,SMS_VERIFY_CODE_TEMPLATE);
//        $res = SMSService::create()->mock()->sms($content)->send($mobile);

        // send voice verify code
        $res = SMSService::create()->mock()->voice($code)->send($mobile);
        if ($res) {
            Cache::put(verifyCodeKey($mobile), $code, 5 * 60);
        }

        return $this->success();
    }
}
