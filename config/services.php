<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model'   => App\User::class,
        'key'     => env('STRIPE_KEY'),
        'secret'  => env('STRIPE_SECRET'),
        'webhook' => [
            'secret'    => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],
    'monyun' => [
        'user_id'  => env('MONYUN_USER_ID',''),
        // E 1085J
        'password' => env('MONYUN_PASSWORD'.''),
        // r 2kF0Q
        'api_key'  => env('MONYUN_API_KEY',''),
        // a77d7e4 8b8d2c6f2 6b14af54f 87f4d0e
        'nodes'    => [
            'south_sms_url'   => env('SOUTH_SMS_URL', 'http://api01.monyun.cn:7901/sms/v2/std/single_send'), // 南方短信
            'north_sms_url'   => env('NORTH_SMS_URL', 'http://api02.monyun.cn:7901/sms/v2/std/single_send'),// 北方短信
            'south_voice_url' => env('SOUTH_VOICE_URL', 'http://api01.monyun.cn:7901/voice/v2/std/template_send'), // 南方语音
            'north_voice_url' => env('NORTH_VOICE_URL', 'http://api02.monyun.cn:7901/voice/v2/std/template_send'), // 北方语音
        ],
    ],
];
