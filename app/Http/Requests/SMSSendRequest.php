<?php

namespace App\Http\Requests;

use App\Rules\MobileRule;
use Illuminate\Foundation\Http\FormRequest;

class SMSSendRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'mobile' => [
                "required",
                new MobileRule(),
            ],
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            "mobile.required" => "手机号不能为空",
        ];
    }
}
