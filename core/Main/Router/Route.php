<?php

namespace Core\Main\Router;
use Core\Base\Controller;

class Route {
    protected Controller $controller;
    protected string $method_name;
    public function __construct($controller_name, $method_name) {
        $this->controller = new $controller_name();
        $this->method_name = $method_name;
    }
    public function launch(){
        $this->controller->{$this->method_name}();
    }
}