<?php

namespace App\Version\b001\Message;

use Core\Main\Request\Request;
use Core\Utils\Answer;
use Exception;

class Model extends \Core\Base\Model {
    protected array $temp_account;

    public function __construct($db_config_name = "db") {
        parent::__construct($db_config_name);
    }

    /**
     * Получение участника беседы
     * @param int $account_id
     * @param int $conversation_id
     * @return mixed
     */
    public function get_member(int $account_id, int $conversation_id){
        if(!($query = $this->db->query("SELECT * FROM conversation_member WHERE conversation_id = ? AND account_id = ? AND active = 'y' LIMIT 1", [$conversation_id, $account_id]))){
            throw new Exception("Conversation does not exist or you have been excluded");
        }
        return $query[0];
    }

    /**
     * Создание беседы
     * @param int $creator_id
     * @param string $type
     * @param string $title
     * @param string $description
     * @return array
     * @throws Exception
     */
    public function createConversation(int $creator_id, string $type, string $title, string $description): array {
        if(!$this->db->execute("INSERT INTO conversation (creator_id, type, title, description) VALUE (?, ?, ?, ?)", [$creator_id, $type, $title, $description])){
            throw new Exception("Error wrote new conversation in database");
        }
        $conversation_id = $this->db->lastInsertId();
        if(!$this->db->execute("INSERT INTO conversation_member (conversation_id, account_id, type) VALUE (?, ?, ?)", [$conversation_id, $creator_id, "creator"])){
            throw new Exception("Error wrote new member in database");
        }
        $this->send($creator_id, $conversation_id, "Chat {$title} is created!");
        return Answer::success([
            "id" => $conversation_id
        ]);
    }

    /**
     * Получение информации о беседе
     * @param int $account_id
     * @param int $conversation_id
     * @return array
     * @throws Exception
     */
    public function getConversation(int $account_id, int $conversation_id): array {
        // Проверяем, состоим ли мы в этой беседе
        $get_member_result = $this->get_member($account_id, $conversation_id);
        if(isset($get_member_result["type"]) && $get_member_result["type"] === "error") return $get_member_result;
        // Получаем информацию о беседе
        if($conversation = $this->db->query("SELECT * FROM conversation WHERE id = ?", [$conversation_id])){
            // Получаем участников беседы
            $members = $this->db->query("SELECT * FROM view_conversation_member WHERE conversation_id = ?", [$conversation_id]);
            return Answer::success(["conversation" => $conversation, "members" => $members]);
        }
        throw new Exception("Conversation is missing");
    }

    /**
     * Удаление истории диалога
     * @param int $account_id
     * @param int $conversation_id
     * @return array
     */
    public function removeHistory(int $account_id, int $conversation_id): array {
        // Проверяем, состоим ли мы в этой беседе
        $get_member_result = $this->get_member($account_id, $conversation_id);
        if(isset($get_member_result["type"]) && $get_member_result["type"] === "error") return $get_member_result;
        // Удаляем историю диалога
        if(!$this->db->execute("UPDATE conversation_member SET begin_message_id = (SELECT id FROM conversation_messages ORDER BY ID DESC LIMIT 1) WHERE account_id = ? AND conversation_id = ?", [$account_id, $conversation_id])){
            throw new Exception("Error remove history messages");
        }
        return Answer::success();
    }

    /**
     * Выход из чата
     * @param int $account_id
     * @param int $conversation_id
     * @return array
     */
    public function leaveConversation(int $account_id, int $conversation_id): array {
        // Проверяем, состоим ли мы в этой беседе
        $get_member_result = $this->get_member($account_id, $conversation_id);
        if(isset($get_member_result["type"]) && $get_member_result["type"] === "error") return $get_member_result;
        // Выход из беседы
        if(!$this->db->execute("UPDATE conversation_member SET active = 'n' WHERE account_id = ? AND conversation_id = ?", [$account_id, $conversation_id])){
            throw new Exception("Error leave from conversation");
        }
        return Answer::success();
    }

    /**
     * Отправка сообщения в диалог
     * @param int $account_id
     * @param int $conversation_id
     * @param string $text
     * @return array
     * @throws Exception
     */
    public function send(int $account_id, int $conversation_id, string $text): array {
        // Проверяем, состоим ли мы в этой беседе
        $member = $this->get_member($account_id, $conversation_id);

        if(isset($get_member_result["type"]) && $get_member_result["type"] === "error") return $get_member_result;
        // Определяем параметры для нового сообщения
        $member_id = $member['id'];
        $time = time();
        // Записываем в БД
        if(!$this->db->execute("INSERT INTO conversation_message (member_id, conversation_id, text, time) VALUE (?, ?, ?, ?)", [$member_id, $conversation_id, $text, $time])){
            throw new Exception("Unknown error sending message");
        }
        return Answer::success(["id" => $this->db->lastInsertId(), "text" => $text, "time" => $time]);
    }

    /**
     * Получение списка диалогов
     * @param int $account_id
     * @param int $conversation_id
     * @param int $last_message_id
     * @return array
     * @throws Exception
     */
    public function getList(int $account_id, int $conversation_id, int $last_message_id = 0): array{
        // Проверяем, состоим ли мы в этой беседе
        $get_member_result = $this->get_member($account_id, $conversation_id);
        if(isset($get_member_result["type"]) && $get_member_result["type"] === "error") return $get_member_result;
        // Получаем ID последнего сообщения, если стандартн. не задана
        if($last_message_id === 0) $last_message_id = $this->db->query("SELECT * FROM view_conversation_message WHERE conversation_id = ? ORDER BY id DESC LIMIT 1", [$conversation_id])[0]["id"];
        // Получаем список сообщений
        if($list = $this->db->query("SELECT * FROM view_conversation_message WHERE conversation_id = ? AND id < ? LIMIT 20", [$conversation_id, $last_message_id])){
            return Answer::success(["list" => $list, "last_id" => $last_message_id]);
        }
        throw new Exception("Error getting message list");
    }
}