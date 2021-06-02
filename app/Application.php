<?php

namespace App;

use Core\Main\Router\Router;

class Application {
    private static string $last_version = "b001";

    public static function launch(){
        $temp_route = Router::getRoute(self::$last_version);
        $temp_route->launch();
    }
}