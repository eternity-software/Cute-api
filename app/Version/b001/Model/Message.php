<?php

namespace App\Version\b001\Model;

use Core\Utils\Answer;

class Message extends \Core\Base\Model {
    protected array $temp_account;

    public function __construct($temp_account, $db_config_name = "db") {
        parent::__construct($db_config_name);
        $this->temp_account = $temp_account;
    }

    public function createConversation($type, $title, $description){
        $creator_id = $this->temp_account['id'];
        if(!$this->db->execute("INSERT INTO conversation (creator_id, type, title, description) VALUE (?, ?, ?, ?)", [$creator_id, $type, $title, $description])){
            Answer::error(["Error wrote new conversation in database"]);
        }
        $conversation_id = $this->db->lastInsertId();
        if(!$this->db->execute("INSERT INTO conversation_member (conversation_id, account_id, type) VALUE (?, ?, ?)", [$conversation_id, $creator_id, "creator"])){
            Answer::error(["Error wrote new member in database"]);
        }
        $this->send($conversation_id, "Chat {$title} is created!");
        Answer::success();
    }

    public function getConversation($id){
        if($conversation = $this->db->query("SELECT * FROM conversation WHERE id = ?", [$id])){
            $members = $this->db->query("SELECT * FROM view_conversation_member WHERE conversation_id = ?", [$id]);
            Answer::success(["conversation" => $conversation, "members" => $members]);
        }
        Answer::error(["Conversation is missing"]);
    }

    public function removeHistory($conversation_id){
        $account_id = $this->temp_account['id'];
        if(!$this->db->execute("UPDATE conversation_member SET begin_message_id = (SELECT id FROM conversation_messages ORDER BY ID DESC LIMIT 1) WHERE account_id = ? AND conversation_id = ?", [$account_id, $conversation_id])){
            Answer::error(["Error remove history messages"]);
        }
        Answer::success();
    }

    public function leaveConversation($conversation_id){
        $account_id = $this->temp_account['id'];
        if(!$this->db->execute("UPDATE conversation_member SET active = 'n' WHERE account_id = ? AND conversation_id = ?", [$account_id, $conversation_id])){
            Answer::error(["Error leave from conversation"]);
        }
        Answer::success();
    }

    public function send($conversation_id, $text){
        $account_id = $this->temp_account['id'];
        if(!($query = $this->db->query("SELECT id FROM view_conversation_member WHERE account_id = ?", [$account_id]))){
            Answer::error(["Member is missing"]);
        }
        $member_id = $query['id'];
        $time = time();
        if(!$this->db->execute("INSERT INTO conversation_message (member_id, conversation_id, text, time) VALUE (?, ?, ?, ?)", [$member_id, $conversation_id, $text, $time])){
            Answer::error(["Unknown error sending message"]);
        }
        Answer::success(["id" => $this->db->lastInsertId(), "text" => $text, "time" => $time]);
    }
}