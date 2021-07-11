<?php

namespace Core\Utils;

class Answer {
    public static array $warnings = [];
    public static bool $debug = true;

    public static function success($data = []): array {
        $result = [
            "type" => "success",
            "warnings" => self::$warnings,
            "data" => $data
        ];

        return $result;
    }

    public static function error($error_msg, $additional = [], $debug_turn = true): array {
        $result = [
            "type" => "error",
            "warnings" => self::$warnings,
            "data" => [
                "messages" => $error_msg,
                "additional" => $additional
            ]
        ];

        if(self::$debug && $debug_turn) $result['debug_backtrace'] = debug_backtrace();

        return $result;
    }

    public static function criticalError($error_msg, $additional = [], $debug_turn = true){
        self::send(self::error($error_msg, $additional, $debug_turn));
    }

    public static function warning($data){
        self::$warnings[] = $data;
    }

    public static function send($result){
        header("Content-Type: application/json; charset=utf-8");
        exit(json_encode($result, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE));
    }
}