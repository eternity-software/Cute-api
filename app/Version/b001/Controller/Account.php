<?php

namespace App\Version\b001\Controller;
use Core\Utils\Field;

class Account extends \Core\Base\Controller {
    public function __construct() {
        parent::__construct();
        $this->model = new \App\Version\b001\Model\Account();
    }

    public function create(){
        $email = (new Field("Email", $this->getOption('email'), [
            "type" => "email",
            "min" => 4,
            "max" => 120,
            "required" => true
        ]))->check();

        $login = (new Field("Login", $this->getOption('login'), [
            "type" => "text",
            "min" => 4,
            "max" => 45,
            "required" => true
        ]))->check();

        $password = (new Field("Password", $this->getOption('password'), [
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
        $code = (new Field("Code", $this->getOption('code'), [
            "type" => "int",
            "min" => 6,
            "max" => 6,
            "required" => true
        ]))->check();

        $this->model->confirm($code);
    }

    public function auth(){
        $login = (new Field("Login", $this->getOption('login'), [
            "type" => "text",
            "min" => 4,
            "max" => 45,
            "required" => true
        ]))->check();

        $password = (new Field("Password", $this->getOption('password'), [
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
        $display_name = (new Field("Name", $this->tryGetOption('display_name'), [
            "type" => "text",
            "min" => 2,
            "max" => 60,
            "required" => false
        ]))->check();

        $display_surname = (new Field("Surname", $this->tryGetOption('display_surname'), [
            "type" => "text",
            "min" => 2,
            "max" => 60,
            "required" => false
        ]))->check();

        $display_status_text = (new Field("Surname", $this->tryGetOption('display_status_text'), [
            "type" => "text",
            "min" => 2,
            "max" => 120,
            "required" => false
        ]))->check();

        $bio = (new Field("Surname", $this->tryGetOption('bio'), [
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
}