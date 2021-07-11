<?php

namespace App\Version\b001\Account;

use Core\Utils\Answer;
use Core\Utils\Mail;
use Core\Utils\Random;
use Exception;

class Model extends \Core\Base\Model {
    protected array $temp_account = [];
    protected string $temp_session;

    /**
     * @throws Exception
     */
    private function create_session($account_id){
        $session_key = hash("sha256", time() * $account_id);
        $session_time = time() + 3600 * 24 * 30 * 12;
        $ip = $_SERVER["REMOTE_ADDR"] ?? "unavailable";

        if(!$this->db->execute("INSERT INTO account_session (account_id, session_key, session_time, ip) VALUE (?, ?, ?, ?)", [$account_id, $session_key, $session_time, $ip])){
            throw new Exception("Unknown error while writing a session to the database!");
        }

        return Answer::success([
            "session_key" => $session_key
        ]);
    }

    /**
     * @throws Exception
     */
    private function create_code($account_id, $email) {
        $codeConfirm = Random::code();
        $time = time() + 3600;
        if(!$this->db->execute("INSERT INTO account_activation_code (account_id, code, time) VALUE (?, ?, ?)", [$account_id, $codeConfirm, $time])){
            throw new Exception("Activation code was not wrote in database");
        }

        if(!Mail::send($email, 'Activate account', 'Your code: ' . $codeConfirm)) {
            throw new Exception("Letter was not sent!");
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function verify_auth($session_key) : array{
        // Записываем ключ текущий сессии
        $this->temp_session = $session_key;
        // Получаем аккаунт по ключу сессии из БД (или возвращаем ошибку)
        if(!($query = $this->db->query("SELECT * FROM view_account_by_session WHERE session_key = ? LIMIT 1", [$this->temp_session]))) throw new Exception("Authorization unsuccessful");
        // Записываем текущий аккаунт в переменную
        $this->temp_account = (count($query) > 0) ? $query[0] : [];
        // Проверяем, не истекло ли время сессии
        if($this->temp_account['session_time'] < time()) throw new Exception("This session is expired!");
        // Возвращаем успешный ответ
        return Answer::success(["account" => $this->temp_account]);
    }

    /**
     * @throws Exception
     */
    public function verify_active($session_key = null): array{
        // Если сессия отсутствует - критическая ошибка
        if(!isset($this->temp_session) && $session_key === null) throw new Exception("Session is missing");
        // Если текущий аккаунт не получен - получаем
        if($this->temp_account === []){
            $this->verify_auth(($session_key !== null) ? $session_key : $this->temp_session);
        }
        // Если текущий аккаунт не активирован - ошибка
        if($this->temp_account['active'] != "y") throw new Exception("Controller is not active!");
        // Возвращаем успешный ответ
        return Answer::success(["account" => $this->temp_account]);
    }

    /**
     * @throws Exception
     */
    public function auth($login, $password): array {
        if(!($query = $this->db->query("SELECT id, password FROM account WHERE login = ? OR email = ? LIMIT 1", [$login, $login])[0])){
            throw new Exception("User with login or email '{$login}' not found! Try type another.");
        }

        if(!password_verify($password, $query['password'])){
            throw new Exception("Wrong password entered!");
        }

        return $this->create_session($query['id']);
    }

    /**
     * @throws Exception
     */
    public function create($email, $login, $password): array {
        $password = password_hash($password, PASSWORD_DEFAULT);

        if($this->db->query("SELECT id, password FROM account WHERE login = ? OR email = ? LIMIT 1", [$login, $email])){
            throw new Exception("User with login or email was registered! Try type another.");
        }

        if(!$this->db->execute("INSERT INTO account (email, login, password, display_name) VALUE (?, ?, ?, ?)", [$email, $login, $password, $login])){
            throw new Exception("Unknown error while writing a new user to the database!");
        }

        $account_id = $this->db->lastInsertId();
        $this->create_code($account_id, $email);

        return $this->create_session($account_id);
    }

    public function sendConfirm(): array {
        $result_code = $this->create_code($this->temp_account['id'], $this->temp_account['email']);
        return (is_array($result_code)) ? $result_code : Answer::success();
    }

    /**
     * @throws Exception
     */
    public function confirm($code): array {
        $account_id = $this->temp_account['id'];
        if(!($query_code = $this->db->query("SELECT id, time FROM account_activation_code WHERE code = ? AND account_id = ? LIMIT 1", [$code, $account_id]))){
            throw new Exception("Incorrect code");
        }
        if(intval($query_code[0]["time"]) < time()){
            throw new Exception("Time is over");
        }
        if(!$this->db->execute("DELETE FROM account_activation_code WHERE code = ? AND account_id = ? LIMIT 1", [$code, $account_id])){
            throw new Exception("Unknown error deactivate code");
        }
        if(!$this->db->execute("UPDATE account SET active = 'y' WHERE id = ? LIMIT 1", [$account_id])){
            throw new Exception("Unknown error wrote database");
        }

        return Answer::success();
    }

    /**
     * @throws Exception
     */
    public function edit($fields): array {
        if($fields === []) Answer::error(["Fields is clear!"]);
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
            throw new Exception("Unknown error changes tables!");
        }
        return Answer::success();
    }

    /**
     * @throws Exception
     */
    public function logout(): array {
        if(!$this->db->execute("UPDATE account_session SET active = 'n' WHERE session_key = ?", [$this->temp_session])) throw new Exception("Unknown database error");
        return Answer::success();
    }

    public function getConversations(): array {
        $conversations = $this->db->query("SELECT view_conversations.* FROM view_conversations INNER JOIN conversation_member member ON member.account_id = ? WHERE view_conversations.id = member.conversation_id", [$this->temp_account['id']]);
        return Answer::success([
            "conversations" => $conversations
        ]);
    }
}