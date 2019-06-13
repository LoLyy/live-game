<?php

if (!function_exists('verifyCodeKey')) {
    /**
     * @param string $mobile
     * @return string
     */
    function verifyCodeKey(string $mobile)
    {
        return "verify_code_{$mobile}";
    }
}