<?php

namespace App\Version\b001\Controller;
use \App\Version\b001\Model\Account;
use Core\Utils\Field;
use Core\Utils\RequestOption;

class Message extends \Core\Base\Controller {
    protected array $temp_account;

    public function __construct() {
        $this->temp_account = (new Account())->verify_auth();
        $this->model = new \App\Version\b001\Model\Message($this->temp_account);
    }

    public function createConversation(){
        $type = (new Field("Type", RequestOption::get('type'), [
            "type" => "enum",
            "list" => ["personal", "private", "public", "channel"],
            "required" => true
        ]))->check();

        $title = (new Field("Title", RequestOption::get('title'), [
            "type" => "text",
            "min" => 1,
            "max" => 60,
            "required" => true
        ]))->check();

        $description = (new Field("Description", RequestOption::tryGet('description'), [
            "type" => "text",
            "min" => 1,
            "max" => 300,
            "required" => false
        ]))->check();

        $this->model->createConversation($type, $title, $description);
    }

    public function getConversation(){
        $id = (new Field("ID", RequestOption::get('id'), [
            "type" => "int",
            "min" => 1,
            "max" => 11,
            "required" => true
        ]))->check();

        $this->model->getConversation($id);
    }

    public function removeHistory(){
        $id = (new Field("ID", RequestOption::get('id'), [
            "type" => "int",
            "min" => 1,
            "max" => 11,
            "required" => true
        ]))->check();

        $this->model->removeHistory($id);
    }

    public function leaveConversation(){
        $id = (new Field("ID", RequestOption::get('id'), [
            "type" => "int",
            "min" => 1,
            "max" => 11,
            "required" => true
        ]))->check();

        $this->model->leaveConversation($id);
    }

    public function send(){
        $id = (new Field("ID", RequestOption::get('id'), [
            "type" => "int",
            "min" => 1,
            "max" => 11,
            "required" => true
        ]))->check();

        $text = (new Field("Text", RequestOption::get('text'), [
            "type" => "text",
            "min" => 1,
            "max" => 2000,
            "required" => true
        ]))->check();

        $this->model->send($id, $text);
    }
}