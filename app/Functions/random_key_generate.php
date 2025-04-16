<?php
function NumericOTP($length): string
{
    $chs = '0123456789';
    $result = '';
    for ($i = 1; $i <= $length; $i++) {
        $result .= substr($chs, (rand() % (strlen($chs))), 1);
    }
    return $result;
}

function otp_seperated($otp): string
{
    $result = '';
    for ($i = 0; $i < strlen($otp); $i++) {
        $result .= substr($otp, $i, 1);
        $result .= ' ';
    }
    return $result;
}
function alpha_numeric_otp($length): string
{
    $chs = '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNQRSTUVWXYZ!@#$&*';
    $result = '';
    for ($i = 1; $i <= $length; $i++) {
        $result .= substr($chs, (rand() % (strlen($chs))), 1);
    }

    return $result;
}
function simple_alpha_numeric_otp($length): string
{
    if ($length<5){
        $length = 5;
    }
    $digits = '123456789';
    $chs = 'abcdefghi';
    $result = '';
    for ($i = 1; $i <= 2; $i++) {
        $result .= substr($digits, (rand() % (strlen($digits))), 1);
    }
    for ($i = 1; $i <= $length-4; $i++) {
        $result .= substr($chs, (rand() % (strlen($chs))), 1);
    }
    for ($i = 1; $i <= 2; $i++) {
        $result .= substr($digits, (rand() % (strlen($digits))), 1);
    }
    return $result;
}
