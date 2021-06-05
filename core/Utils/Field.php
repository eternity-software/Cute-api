<?php

namespace Core\Utils;

class Field {
    protected string $name;
    protected $value;
    protected array $options;
    public function __construct($name, $value, $options = []){
        $this->name = $name;
        $this->value = $value;
        $this->options = $options;
        return $this;
    }
    public function check() {
        $errors = [];

        // Options
        $type = (isset($this->options['type'])) ? strtolower($this->options['type']) : false;
        $min = (isset($this->options['min'])) ? $this->options['min'] : 0;
        $max = (isset($this->options['max'])) ? $this->options['max'] : 0;
        $required = (isset($this->options['required'])) ? $this->options['required'] : false;
        $list = (isset($this->options['list'])) ? $this->options['list'] : false;

        // Min - max
        if($type !== "int") {
            if ($this->value != null && $min != 0 && strlen($this->value) < $this->options['min']) $errors[] = "Minimum count symbols for '{$this->name}' - {$this->options['min']}";
            if ($this->value != null && $max != 0 && strlen($this->value) > $this->options['max']) $errors[] = "Maximum count symbols for '{$this->name}' - {$this->options['max']}";
        } else {
            if($this->value != null && !filter_var($this->value, FILTER_VALIDATE_INT) && ($this->value > $max || $this->value < $min)) $errors[] = "Field '{$this->name}' is not a number or out of the range ({$min} - {$max})";
        }

        // Email validate
        if($this->value != null && $type === "email" && !filter_var($this->value, FILTER_VALIDATE_EMAIL))  $errors[] = "Field '{$this->name}' is not a email";

        // Enum validate
        if($type === "enum" && $list && !array_search($this->value, $list)) $errors[] = "Field '{$this->name}' is invalid";

        // Required
        if($required && ($this->value == null || $this->value == false)) $errors[] = "Field '{$this->name}' is required!";

        // Result
        if($errors != []) Answer::error($errors);
        return $this->value;
    }
}