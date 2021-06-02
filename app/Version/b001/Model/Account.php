<?php

namespace App\Version\b001\Model;

use Core\Utils\Answer;
use Core\Utils\Mail;
use Core\Utils\Random;

class Account extends \Core\Base\Model {
    protected array $temp_account = [];
    protected string $temp_session;

    public function __construct($db_config_name = "db") {
        parent::__construct($db_config_name);
        $this->temp_account = $this->get(true);
    }

    private function create_session($account_id){
        $session_key = hash("sha256", time() * $account_id);
        $session_time = time() + 24 * 30 * 12;
        $ip = $_SERVER["REMOTE_ADDR"];

        if(!$this->db->execute("INSERT INTO account_session (account_id, session_key, session_time, ip) VALUE (?, ?, ?, ?)", [$account_id, $session_key, $session_time, $ip])){
            Answer::error(["Unknown error while writing a session to the database!"]);
        }

        return $session_key;
    }

    private function create_code($account_id, $email){
        $codeConfirm = Random::code();
        $time = time() + 3600;
        if(!$this->db->execute("INSERT INTO account_activation_code (account_id, code, time) VALUE (?, ?, ?)", [$account_id, $codeConfirm, $time])){
            Answer::error(["Activation code was not wrote in database"]);
        }

        if(!Mail::send($email, 'Activate account', 'Your code: ' . $codeConfirm)) {
            Answer::error(["Letter was not sent!"]);
        }
    }

    public function auth($login, $password){
        if(!($query = $this->db->query("SELECT id, password FROM account WHERE login = ? OR email = ? LIMIT 1", [$login, $login])[0])){
            Answer::error(["User with login or email '{$login}' not found! Try type another."]);
        }

        if(!password_verify($password, $query['password'])){
            Answer::error(["Wrong password entered!"]);
        }

        Answer::success([
            "session_key" => $this->create_session($query['id'])
        ]);
    }

    public function create($email, $login, $password){
        $password = password_hash($password, PASSWORD_DEFAULT);

        if($this->db->query("SELECT id, password FROM account WHERE login = ? OR email = ? LIMIT 1", [$login, $email])){
            Answer::error(["User with login or email was registered! Try type another."]);
        }

        if(!$this->db->execute("INSERT INTO account (email, login, password, display_name) VALUE (?, ?, ?, ?)", [$email, $login, $password, $login])){
            Answer::error(["Unknown error while writing a new user to the database!"]);
        }

        $account_id = $this->db->lastInsertId();
        $this->create_code($account_id, $email);

        Answer::success([
            "session_key" => $this->create_session($account_id)
        ]);
    }

    public function sendConfirm(){
        if($this->temp_account === []){
            Answer::error(["Auth failed!"]);
        }

        $this->create_code($this->temp_account['id'], $this->temp_account['email']);

        Answer::success([]);
    }

    public function confirm($code){
        if($this->temp_account === []){
            Answer::error(["Auth failed!"]);
        }
        $accound_id = $this->temp_account['id'];
        if(!$this->db->query("SELECT id FROM account_activation_code WHERE code = ? AND account_id = ? LIMIT 1", [$code, $accound_id])){
            Answer::error(["Incorrect code"]);
        }
        if(!$this->db->execute("DELETE FROM account_activation_code WHERE code = ? AND account_id = ?", [$code, $accound_id])){
            Answer::error(["Unknown error deactivate code"]);
        }
        if(!$this->db->execute("UPDATE account SET active = 'y' WHERE id = ?", [$accound_id])){
            Answer::error(["Unknown error wrote database"]);
        }

        Answer::success([]);
    }

    public function get($inner = false) : array{
        // Проверяем, есть ли сессия
        if(empty($_GET["session"])){
            if($inner){
                return [];
            } else {
                Answer::error(["Key session is missing"]);
            }
        }
        $this->temp_session = $_GET["session"];

        if(!($query = $this->db->query("SELECT account.*, account_session.session_time FROM account, account_session WHERE account_session.session_key = ? AND account.id = account_session.account_id LIMIT 1", [$this->temp_session])[0])){
            Answer::error(["This session is not registered!"]);
        }
        if($query['session_time'] < time()){
            Answer::error(["This session is fuu..!"]);
        }
        if(!$inner){
            Answer::success(["account" => $query]);
        } else {
            return $query;
        }
    }

    public function edit($fields){
        if($fields === []) Answer::error(["Fields is clear!"]);

        if($this->temp_account === []){
            Answer::error(["Auth failed!"]);
        }
        $sql_to_execute = "UPDATE account SET ";
        $array_to_execute = [];
        $i = 0;
        foreach ($fields as $key => $value){
            $sql_to_execute.= "{$key} = ?";
            if(count($fields)-1 > $i) $sql_to_execute.= ", ";
            $array_to_execute[] = $value;
            $i++;
        }
        if(!$this->db->execute($sql_to_execute, $array_to_execute)){
            Answer::error(["Unknown error changes tables!"]);
        }
        Answer::success([]);
    }

    public function logout(){
        if($this->temp_account === []){
            Answer::error(["Authorization unsuccessful"]);
        }

        if(!$this->db->execute("UPDATE account_session SET active = 'n' WHERE session_key = ?", [$this->temp_session])){
            Answer::error(["Unknown database error"]);
        }

        Answer::success([]);
    }
}