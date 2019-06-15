<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\SMSSendRequest;
use App\Services\SMSService;
use App\Tasks\SendVerifyCodeTask;
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

        if (php_sapi_name() == 'cli') {
            $res = asyncTask(new SendVerifyCodeTask(compact('mobile', 'code')));
        } else {
            // send voice verify code
            $res = SMSService::create()->mock()->voice($code)->send($mobile);
        }
        if ($res !== false) {
            Cache::put(verifyCodeKey($mobile), $code, 5 * 60);
        }

        return $this->success(compact('code'));
    }
}
