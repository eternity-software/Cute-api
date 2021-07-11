<?php

namespace App\Version\b001\Message;
use Core\Main\Request\Request;
use Core\Utils\Field;

class Controller extends \Core\Base\ControllerAuth {

    public function __construct() {
        $this->model = new Model();
    }

    /**
     * Создание беседы
     * @param $session_key
     * @param $type
     * @param $title
     * @param $description
     * @return array
     */
    public function createConversation($session_key, $type, $title, $description): array{
        $this->verifyActive($session_key);
        return $this->model->createConversation($this->temp_account['id'], $type, $title, $description);
    }

    /**
     * Получение информации о беседе
     * @param $session_key
     * @param $id
     * @return array
     */
    public function getConversation($session_key, $id): array{
        $this->verifyActive($session_key);
        return $this->model->getConversation($this->temp_account['id'], $id);
    }

    /**
     * Удаление истории диалога
     * @param $session_key
     * @param $id
     * @return array
     */
    public function removeHistory($session_key, $id): array{
        $this->verifyActive($session_key);
        return $this->model->removeHistory($this->temp_account['id'], $id);
    }

    /**
     * Выход из беседы
     * @param $session_key
     * @param $id
     * @return array
     */
    public function leaveConversation($session_key, $id): array{
        $this->verifyActive($session_key);
        return $this->model->leaveConversation($this->temp_account['id'], $id);
    }

    /**
     * Отправка сообщений в беседу
     * @param $session_key
     * @param $id
     * @param $text
     * @return array
     */
    public function send($session_key, $id, $text): array{
        $this->verifyActive($session_key);
        return $this->model->send($this->temp_account['id'], $id, $text);
    }

    /**
     * Получение списка сообщений диалога
     * @param $session_key
     * @param $conversation_id
     * @param int $last_message_id
     * @return array
     */
    public function getList($session_key, $conversation_id, int $last_message_id = 0): array{
        $this->verifyActive($session_key);
        return $this->model->getList($this->temp_account['id'], $conversation_id, $last_message_id);
    }
}