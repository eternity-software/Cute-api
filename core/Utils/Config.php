<?php

namespace Core\Utils;

class Config {
    public static function load($name){
        $path = __ROOTDIR__."/app/config/".$name.".json";
        if(file_exists($path)){
            try{
                return json_decode(file_get_contents($path), true);
            }catch (\Exception $e){
                Answer::error($e->getMessage());
            }
        }
        Answer::error("Configuration file '{$path}' is missing");
    }
}