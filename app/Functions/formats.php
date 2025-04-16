<?php
function j_d_stamp_en()
{
    return jdate('Y/m/d H:i:s', '', '', '', 'en');
}

function fa_num($number): string
{
    $en = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
    $fa = array("۰", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹");
    return str_replace($en, $fa, $number);
}

function mob_form(string $mobile): string
{
    $part1 = substr($mobile, 0, 4);
    $part2 = substr($mobile, 4, 3);
    $part3 = substr($mobile, 7, 4);

    return $part1 . ' ' . $part2 . ' ' . $part3;
}


function currency($num)
{
    if (strlen($num) > 0) {
        return number_format($num, 0, ".", ", ");
    } else {
        return $num;
    }
}
