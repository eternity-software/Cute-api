<?php

namespace Core\Utils;

class Answer {
    public static $place = [];
    public static $debug = true;

    public static function setup($controller, $method){
        self::$place = [
            "controller" => $controller,
            "method" => $method
        ];
    }

    public static function success($data = []){
        $result = [
            "type" => "success",
            "data" => $data
        ];

        exit(json_encode($result, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));
    }

    public static function error($data){
        self::$place['debug_backtrace'] = debug_backtrace();

        $result = [
            "type" => "error",
            "place" => self::$place,
            "data" => $data
        ];

        exit(json_encode($result, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));
    }
}