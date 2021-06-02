<?php

namespace Core\Utils;

class Random {
    public static function code($length = 6) : string{
        $min = str_repeat(0, $length-1) . 1;
        $max = str_repeat(9, $length);
        return mt_rand($min, $max);
    }

    public static function string($length = 10) : string{
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ*/$#:+-';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++)
        {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}