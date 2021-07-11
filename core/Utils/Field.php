<?php

namespace Core\Utils;

class Field {
    protected string $name;
    protected $value;
    protected $default_value = null;
    protected array $options;
    protected string $type;
    protected array $errors = [];

    public function __construct($name, $value, $options = []){
        $this->name = $name;
        $this->value = trim($value);
        $this->options = $options;
        return $this;
    }

    private function appendError($error){
        $this->errors[] = $error;
    }

    public function check() {
        // Перебираем параметры
        foreach ($this->options as $key => $value){
            // Делаем действие, исходя из имени параметра
            switch ($key){
                // Выносим тип в отдельную переменную (для других проверок)
                case "type":
                    $this->type = strtolower($value);
                    break;
                case "default":
                    $this->default_value = $value;
                    break;
                // Проверка на специфичные свойства (используется как)
                case "use":
                    // Выполняем проверку на валидный email (если это он)
                    if(!filter_var($this->value, FILTER_VALIDATE_EMAIL) && $value === "email") $this->appendError("{$this->name} is not email");
                    break;
                // Параметр обязательный (проверяем, не является ли он пустым или нулевым)
                case "required":
                    if( ($this->value === null || $this->value === false || $this->value === 0) && $this->default_value === null){
                        // Если значение пусто и нет стандартного значения
                        $this->appendError("{$this->name} is empty");
                    } else if($this->default_value !== null) {
                        // Если стандартное значение есть
                        $this->value = $this->default_value;
                    }
                    break;
                // Проверяем минимальное значение (исходя из типа поля)
                case "min":
                    if(
                        // Для строк
                        ($this->type === "string" && strlen($this->value) < intval($value)) ||
                        // Для чисел
                        ($this->type === "int" && intval($this->value) < intval($value))
                    ){
                        $this->appendError("{$this->name} is less of {$value}");
                    }
                    break;
                // Проверяем максимальное значение (исходя из типа поля)
                case "max":
                    if(
                        // Для строк
                        ($this->type === "string" && strlen($this->value) > intval($value)) ||
                        // Для чисел
                        ($this->type === "int" && intval($this->value) > intval($value))
                    ){
                        $this->appendError("{$this->name} is more of {$value}");
                    }
                    break;
                // Проверка на соответствие состоянию (если поле enum)
                case "list":
                    // Если тип не enum
                    if($this->type !== "enum") {
                        $this->appendError("{$this->name} is not enum");
                        break;
                    }
                    // Если список возможных состояний не массив или пуст
                    if(!is_array($value) || $value === []) $this->appendError("{$this->name} have empty state list");
                    // Если нынешнее состояние отсутствует в списке
                    if(!array_search($this->value, $value)){
                        $this->appendError("{$this->name} is incorrect");
                    }
                    break;
            }
        }

        // Если ошибки есть - выводим критическую ошибку с ними
        if($this->errors !== []) Answer::criticalError($this->errors);

        return $this->value;
    }
}