<?php


define('RESPONSE_CODE', 'code');
define('RESPONSE_DATA', 'data');
define('RESPONSE_MESSAGE', 'message');

# code
define('RESPONSE_CODE_SUCCESS', 0);
define('RESPONSE_CODE_FAILED', 1);
define('RESPONSE_CODE_INVALID_PARAM', 4220); // 参数错误

# message
define('RESPONSE_MESSAGES', [
    RESPONSE_CODE_SUCCESS       => "ok",
    RESPONSE_CODE_FAILED        => "服务器开了小差~",
    RESPONSE_CODE_INVALID_PARAM => "参数错误",
]);