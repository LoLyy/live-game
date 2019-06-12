<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SMSController extends Controller
{
    public function sms(Request $request)
    {
        return $this->success(["a" => "a"]);
    }
}
