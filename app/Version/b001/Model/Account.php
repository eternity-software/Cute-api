<?php

namespace App\Version\b001\Model;

use Core\Utils\Answer;
use Core\Utils\Mail;
use Core\Utils\Random;
use Core\Utils\RequestOption;

class Account extends \Core\Base\Model {
    protected array $temp_account = [];
    protected string $temp_session;

    public function get_data() {
        return $this->temp_account;
    }

    private function create_session($account_id){
        $session_key = hash("sha256", time() * $account_id);
        $session_time = time() + 3600 * 24 * 30 * 12;
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

    public function verify_auth() : array{
        $this->temp_session = RequestOption::get("session");

        if(!($query = $this->db->query("SELECT * FROM view_account_by_session WHERE session_key = ?", [$this->temp_session]))) Answer::error(["Authorization unsuccessful"]);
        $this->temp_account = (count($query) > 0) ? $query[0] : [];

        if($this->temp_account['active'] != "y") Answer::error(["Account is not active!"], ["active" => $this->temp_account['active']]);
        if($this->temp_account['session_time'] < time()) Answer::error(["This session is expired!"]);

        return $this->temp_account;
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
        $this->verify_auth();
        $this->create_code($this->temp_account['id'], $this->temp_account['email']);
        Answer::success();
    }

    public function confirm($code){
        $this->verify_auth();
        $account_id = $this->temp_account['id'];
        if(!$this->db->query("SELECT id FROM account_activation_code WHERE code = ? AND account_id = ?", [$code, $account_id])){
            Answer::error(["Incorrect code"]);
        }
        if(!$this->db->execute("DELETE FROM account_activation_code WHERE code = ? AND account_id = ?", [$code, $account_id])){
            Answer::error(["Unknown error deactivate code"]);
        }
        if(!$this->db->execute("UPDATE account SET active = 'y' WHERE id = ?", [$account_id])){
            Answer::error(["Unknown error wrote database"]);
        }

        Answer::success();
    }

    public function get(){
        $this->verify_auth();
        Answer::success(["account" => $this->temp_account]);
    }

    public function edit($fields){
        if($fields === []) Answer::error(["Fields is clear!"]);

        $this->verify_auth();

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
        Answer::success();
    }

    public function logout(){
        $this->verify_auth();
        if(!$this->db->execute("UPDATE account_session SET active = 'n' WHERE session_key = ?", [$this->temp_session])) Answer::error(["Unknown database error"]);
        Answer::success();
    }

    public function getConversations(){
        $this->verify_auth();
        $conversations = $this->db->query("SELECT view_conversations.* FROM view_conversations INNER JOIN conversation_member member ON member.account_id = ? WHERE view_conversations.id = member.conversation_id");
        Answer::success([
            "conversations" => $conversations
        ]);
    }
}