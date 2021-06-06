<?php

namespace App\Version\b001\Controller;
use Core\Utils\Field;
use Core\Utils\RequestOption;

class Account extends \Core\Base\Controller {
    public function __construct() {
        $this->model = new \App\Version\b001\Model\Account();
    }

    public function create(){
        $email = (new Field("Email", RequestOption::get('email'), [
            "type" => "email",
            "min" => 4,
            "max" => 120,
            "required" => true
        ]))->check();

        $login = (new Field("Login", RequestOption::get('login'), [
            "type" => "text",
            "min" => 4,
            "max" => 45,
            "required" => true
        ]))->check();

        $password = (new Field("Password", RequestOption::get('password'), [
            "type" => "text",
            "min" => 6,
            "max" => 45,
            "required" => true
        ]))->check();

        $this->model->create($email, $login, $password);
    }

    public function sendConfirm(){
        $this->model->sendConfirm();
    }

    public function confirm(){
        $code = (new Field("Code", RequestOption::get('code'), [
            "type" => "int",
            "min" => 6,
            "max" => 6,
            "required" => true
        ]))->check();

        $this->model->confirm($code);
    }

    public function auth(){
        $login = (new Field("Login", RequestOption::get('login'), [
            "type" => "text",
            "min" => 4,
            "max" => 45,
            "required" => true
        ]))->check();

        $password = (new Field("Password", RequestOption::get('password'), [
            "type" => "text",
            "min" => 6,
            "max" => 45,
            "required" => true
        ]))->check();

        $this->model->auth($login, $password);
    }

    public function get(){
        $this->model->get();
    }

    public function edit(){
        $display_name = (new Field("Name", RequestOption::tryGet('display_name'), [
            "type" => "text",
            "min" => 2,
            "max" => 60,
            "required" => false
        ]))->check();

        $display_surname = (new Field("Surname", RequestOption::tryGet('display_surname'), [
            "type" => "text",
            "min" => 2,
            "max" => 60,
            "required" => false
        ]))->check();

        $display_status_text = (new Field("Surname", RequestOption::tryGet('display_status_text'), [
            "type" => "text",
            "min" => 2,
            "max" => 120,
            "required" => false
        ]))->check();

        $bio = (new Field("Surname", RequestOption::tryGet('bio'), [
            "type" => "text",
            "min" => 2,
            "max" => 300,
            "required" => false
        ]))->check();

        $fields = [];
        if($display_name != null) $fields['display_name'] = $display_name;
        if($display_surname != null) $fields['display_surname'] = $display_surname;
        if($display_status_text != null) $fields['display_status_text'] = $display_status_text;
        if($bio != null) $fields['bio'] = $bio;

        $this->model->edit($fields);
    }

    public function logout(){
        $this->model->logout();
    }

    public function getConversations(){
        $this->model->getConversations();
    }
}