<?php

namespace Core\Utils;

class RequestOption {
    private static function getOptionsAll() : array{
        return (strtolower($_SERVER["REQUEST_METHOD"]) == "get") ? $_GET : $_POST;
    }

    public static function get($name){
        $options = self::getOptionsAll();
        if(isset($options[$name])) return $options[$name];
        Answer::error(["Parameter '{$name}' is missing"]);
    }

    public static function tryGet($name){
        $options = self::getOptionsAll();
        if(isset($options[$name])) return $options[$name];
        return false;
    }
}