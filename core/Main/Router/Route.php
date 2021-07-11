<?php

namespace Core\Main\Router;

use Core\Base\Controller;
use Core\Main\Request\Request;
use Core\Utils\Answer;
use Core\Utils\Field;

class Route {
    /**
     * Исполняющий контроллер
     * @var Controller
     */
    protected Controller $controller;
    /**
     * Исполняющий метод запроса
     * @var string
     */
    protected string $action;
    /**
     * Требуемый уровень доступа
     * @var string
     */
    protected string $access_level;
    /**
     * Параметры, которые должен получить текущий метод
     * @var array
     */
    protected array $options;
    /**
     * Параметры, которые пришли вместе с запросом
     * @var array
     */
    protected array $request_options;
    /**
     * Параметры, которые передаются в испольниельный метод
     * @var array
     */
    protected array $put_options;

    /**
     * Конструктор Роута
     * @param Controller $controller
     * @param string $action
     * @param array $request_options
     * @param array $options
     */
    public function __construct(Controller $controller, string $action, string $access_level = "ALL", array $request_options = [], array $options = []) {
        $this->controller = $controller;
        $this->action = $action;
        $this->access_level = strtoupper($access_level);
        $this->request_options = $request_options;
        $this->options = $options;
    }

    /**
     * Метод сравнения необходимых параметров с пришедшими
     */
    private function verifyOptions(){
        // Перебираем необходимые для метода параметры
        foreach ($this->options as $name => $options){
            // Определяем значение пришедшего параметра (или то, что пришло или null)
            $value = (isset($this->request_options[$name])) ? $this->request_options[$name] : null;
            // Создаём поле и получаем конечное значение параметра
            $this->put_options[$name] = (new Field($name, $value, $options))->check();
        }
    }

    /**
     * Метод проверки уровня доступа
     */
    private function verifyAccess(){
        // Если требуемый уровень доступа 'ALL' - пропускаем проверку
        if($this->access_level === "ALL") return;
        // Если пришёл параметр 'session'
        if( ($session_key = Request::tryGetOption("session")) && $this->access_level != "ANONYMOUS"){
            // Если тип авторизации подходит под ролевые
            if(!array_search($this->access_level, ["ROOT", "ADMIN", "SUPPORT", "USER"])){
                Answer::criticalError(["Invalid access level"]);
            }
            $this->put_options["session_key"] = $session_key;
        }
    }

    /**
     * Запуск исполняемого метода
     * @return array
     */
    public function launch(): array{
        // Проверяем существует ли запрашиваемый метод
        if(!method_exists($this->controller, $this->action)){
            Answer::criticalError(["Request method '{$temp_action}' is missing. Check documentation for cute api ver. {$version}!"]);
        }
        // Выполняем проверку уровня доступа
        $this->verifyAccess();
        // Выполняем проверку параметров
        $this->verifyOptions();
        // Запускаем и возвращаем результат выполнения
        return Request::sendInner($this->controller, $this->action, $this->put_options);
    }

}