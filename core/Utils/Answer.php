<?php

namespace Core\Utils;

class Answer {
    private static array $place = [];
    public static array $warnings = [];
    public static bool $debug = true;

    public static function setup($controller, $method){
        self::$place = [
            "controller" => $controller,
            "method" => $method
        ];
    }

    public static function success($data = []){
        header("Content-Type: application/json; charset=utf-8");
        $result = [
            "type" => "success",
            "warnings" => self::$warnings,
            "data" => $data
        ];

        exit(json_encode($result, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));
    }

    public static function error($error_msg, $debug_turn = true){
        header("Content-Type: application/json; charset=utf-8");
        $result = [
            "type" => "error",
            "warnings" => self::$warnings,
            "data" => [
                "messages" => $error_msg
            ]
        ];

        if(self::$place !== []) $result['place'] = self::$place;
        if(self::$debug && $debug_turn) $result['debug_backtrace'] = debug_backtrace();

        exit(json_encode($result, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));
    }

    public static function warning($data){
        self::$warnings[] = $data;
    }
}