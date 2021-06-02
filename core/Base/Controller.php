<?php

namespace Core\Base;

use Core\Utils\Answer;

abstract class Controller{
    protected Model $model;
    private array $options;
	public function __construct(){
	    $this->options = (strtolower($_SERVER["REQUEST_METHOD"]) == "get") ? $_GET : $_POST;
    }

    public function getOption($name){
	    if(isset($this->options[$name])) return $this->options[$name];
	    Answer::error(["Parameter '{$name}' is missing"]);
    }

    public function tryGetOption($name){
        if(isset($this->options[$name])) return $this->options[$name];
        return null;
    }
}