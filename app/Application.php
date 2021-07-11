<?php

namespace App;

use Core\Main\Request\Request;
use Core\Main\Router\Router;
use Core\Utils\Answer;

class Application extends \Core\Main\Application\Application {

    /**
     * Метод инициализации приложения
     */
    public static function launch(): void{
        // Задаём корневую директорию приложения
        self::setRootDir(__DIR__);
        // Задаём последнюю доступную версию
        self::setLastVersion("b001");

        // Выбор версии
        if(Request::tryGetOption("v")){
            // Если параметр 'v' задан - записываем пользовательскую версию
            self::setVersion(Request::getOption("v"));
        } else {
            // Если параметр 'v' отсутствует - записываем последнюю версию
            self::setVersion(self::$last_version);
        }

        // Определяем текущий роут
        $route = Router::dispatch();
        // Запускаем его исполнение, записываем результат
        $result = $route->launch();
        // Отправляем результат клиенту
        Answer::send($result);
    }

}