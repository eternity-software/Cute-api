<?php

namespace Core\Main\Router;

use Core\Base\Controller;
use Core\Utils\Answer;

class Router {

    /**
     * Список обрабатываемых страниц
     * @var array
     */
    protected static array $routes = [];

    /**
     * Метод добавления доступных страниц
     * @param string $version
     * @param string $request
     * @param string $method
     * @param Controller $controller
     * @param string $action
     * @param array $options
     */
    public static function addRoute(string $request, string $method, Controller $controller, string $action, string $access_level, array $options = []){
        self::$routes[$request] = [
            "method" => strtolower($method),
            "controller" => $controller,
            "action" => $action,
            "access_level" => strtolower($access_level),
            "options" => $options
        ];
    }

    /**
     * Метод для получения текущего роута
     * @return Route
     */
    public static function dispatch(): Route{
        // Получаем запрашиваемые контроллер и метод
        $temp_request = trim(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH), "/");
        // Определяем текущий метод запроса
        $temp_method = strtolower($_SERVER["REQUEST_METHOD"]);
        foreach (self::$routes as $request => $route){
            // Если мы нашли нужную связку -> возвращаем новый контроллер
            if($request === $temp_request && $route['method'] === $temp_method){
                // Получаем пришедшие параметры запроса
                $temp_options = ($temp_method === "get") ? $_GET : $_POST;
                return new Route($route["controller"], $route["action"], $route["access_level"], $temp_options, $route["options"]);
            }
        }
        return new Route(new \App\Version\b001\Error\Controller(), "page404");
    }
}