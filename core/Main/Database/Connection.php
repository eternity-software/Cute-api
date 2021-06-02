<?php

namespace Core\Main\Database;
use Core\Utils\Answer;
use Core\Utils\Config;
use PDO;

class Connection {
    private PDO $pdo;

    public function __construct($config_name = "db") {
        try{
            $opt = [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ];
            $config = Config::load($config_name);
            $this->pdo = new PDO($config['driver'].':host='.$config['host'].';dbname='.$config['dbname'].';charset='.$config['charset'], $config['user'], $config['password'], $opt);
        }catch (\PDOException $e){
            Answer::error("Database '{$config['dbname']}' {$e->getMessage()}");
        }
    }

    public function query($sql, $params = []): array {
        $sth = $this->pdo->prepare($sql);
        $sth->execute($params);
        return $sth->fetchAll();
    }

    public function execute($sql, $params = []): bool {
        $sth = $this->pdo->prepare($sql);
        if($sth->execute($params))
        {
            return true;
        }
        return false;
    }

    public function lastInsertId(): string {
        return $this->pdo->lastInsertId();
    }

    public function errorInfo() {
        return $this->pdo->errorInfo();
    }
}