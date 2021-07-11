<?php

namespace App\Version\b001\Account;
use Core\Main\Request\Request;
use Core\Utils\Answer;
use Core\Utils\Field;

class Controller extends \Core\Base\Controller {
    public function __construct() {
        $this->model = new Model();
    }

    /**
     * Служебный метод для проверки активности сессии
     * @param $session_key
     * @return array
     */
    public function verifyAuth($session_key): array{
        return $this->model->verify_auth($session_key);
    }

    /**
     * Служебный метод для проверки активности сессии и активации аккаунта
     * @param $session_key
     * @return array
     */
    public function verifyActive($session_key): array{
        return $this->model->verify_active($session_key);
    }

    /**
     * Регистрация нового аккаунта
     * @param $email
     * @param $login
     * @param $password
     * @return array
     */
    public function create($email, $login, $password): array{
        return $this->model->create($email, $login, $password);
    }

    /**
     * Отправка повторного письма с кодом активации
     * @param $session_key
     * @return array
     */
    public function sendConfirm($session_key): array{
        $this->verifyAuth($session_key);
        return $this->model->sendConfirm();
    }

    /**
     * Подтверждение аккаунта кодом активации
     * @param $session_key
     * @param $code
     * @return array
     */
    public function confirm($session_key, $code): array{
        $this->verifyAuth($session_key);
        return $this->model->confirm($code);
    }

    /**
     * Авторизация по логину или Email
     * @param $login
     * @param $password
     * @return array
     */
    public function auth($login, $password): array{
        return $this->model->auth($login, $password);
    }

    /**
     * Получение данных об аккаунте по сессии
     * @param $session_key
     * @return array
     */
    public function get($session_key): array{
        return $this->model->verify_auth($session_key);
    }

    /**
     * Редактирование аккаунта
     * @param $session_key
     * @param $display_name
     * @param $display_surname
     * @param $display_status_text
     * @param $bio
     * @return array
     */
    public function edit($session_key, $display_name = null, $display_surname = null, $display_status_text = null, $bio = null): array {
        $this->verifyActive($session_key);

        $fields = [];
        if($display_name != null) $fields['display_name'] = $display_name;
        if($display_surname != null) $fields['display_surname'] = $display_surname;
        if($display_status_text != null) $fields['display_status_text'] = $display_status_text;
        if($bio != null) $fields['bio'] = $bio;

        if($fields === []){
            return Answer::error(["Noting to update"]);
        }

        return $this->model->edit($fields);
    }

    /**
     * Выход из аккаунта (закрытие сессии)
     * @param $session_key
     * @return array
     */
    public function logout($session_key): array{
        $this->verifyAuth($session_key);
        return $this->model->logout();
    }

    /**
     * Получение списка бесед
     * @param $session_key
     * @return array
     */
    public function getConversations($session_key): array{
        $this->verifyActive($session_key);
        return $this->model->getConversations();
    }
}