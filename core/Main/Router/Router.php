<?php

namespace Core\Main\Router;

use Core\Utils\Answer;

class Router {

    /**
     * Метод для получения запрашевоемого имени контроллера и метода
     * @return array
     */
    private static function getTempData() : array{
        $boom_url = explode(".", $_SERVER["REQUEST_URI"], 2);
        $controller_name = $boom_url[0];
        $temp_method = explode("?", $boom_url[1], 2)[0];

        return [
            $controller_name,
            $temp_method
        ];
    }

    /**
     * Метод для получения текущего роута
     * @param $version
     * @return Route
     */
    public static function getRoute($version) : Route {
        list($controller_name, $temp_method) = self::getTempData();
        // (От - до) firstLevel - FirstLevel
        $temp_controller = mb_convert_case(trim($controller_name, "/"), MB_CASE_TITLE, "UTF-8");
        // Проверяем существует ли запрашиваемый класс
        $class_name = "\\App\\Version\\{$version}\\Controller\\{$temp_controller}";
        if(!class_exists($class_name)){
            Answer::error("Request controller '{$controller_name}' is missing. Check documentation for cute api ver. {$version}!");
        }
        $class_object = new $class_name();
        // Проверяем существует ли запрашиваемый метод
        if(!method_exists($class_object, $temp_method)){
            Answer::error("Request method '{$temp_method}' is missing. Check documentation for cute api ver. {$version}!");
        }
        return new Route($class_name, $temp_method);
    }
}