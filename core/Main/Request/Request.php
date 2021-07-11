<?php

namespace Core\Main\Request;
use App\Application;
use Core\Base\Controller;
use Core\Utils\Answer;
use Core\Utils\RequestOption;

class Request {
    private static $instance;

    /**
     * Получение экземпляра объекта
     * @return Request
     */
    public static function getInstance(): Request {
        if(!(self::$instance instanceof self)){
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Закрываем конструктор от лишних глаз
     */
    private function __construct() {}

    /**
     * Метод, обеспечивающий общение моделей между собой
     * @param string $model
     * @param string $method
     * @param array $options
     * @return false|mixed
     */
    public static function sendInner(Controller $controller, string $method, array $options = []){
        try {
            // Возвращаем результат выполнение метода
            return call_user_func_array([$controller, $method], $options);
        }catch (\Exception $e){
            return Answer::error([$e->getMessage()]);
        }
    }

    /**
     * Метод получения списка отправленных параметров
     * @return array
     */
    private static function getOptionsAll(): array {
        return (strtolower($_SERVER["REQUEST_METHOD"]) == "get") ? $_GET : $_POST;
    }

    public static function getOption($name) {
        $options = self::getOptionsAll();
        if (isset($options[$name])) return $options[$name];
        Answer::error(["Parameter '{$name}' is missing"]);
    }

    public static function tryGetOption($name) {
        $options = self::getOptionsAll();
        if (isset($options[$name])) return $options[$name];
        return false;
    }
}