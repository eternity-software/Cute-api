<?php

namespace Core\Main\Database;
use Core\Utils\Answer;
use Core\Utils\Config;
use PDO;

class Connection {
    private static $instance;
    private PDO $pdo;
    private string $dbname;

    /**
     * Получаем эземпляр класса
     * @param string $config_name
     * @return Connection
     */
    public static function getInstance(string $config_name = "db"): Connection {
        if(!(self::$instance instanceof self)){
            self::$instance = new self($config_name);
        }
        return self::$instance;
    }

    private function __construct($config_name) {
        // Получаем конфиг подключения к БД
        $config = Config::load($config_name);
        // Записываем имя БД
        $this->dbname = $config['dbname'];
        try{
            $opt = [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ];
            $this->pdo = new PDO($config['driver'].':host='.$config['host'].';dbname='.$this->dbname.';charset='.$config['charset'], $config['user'], $config['password'], $opt);
        }catch (\PDOException $e){
            Answer::error("Database '{$this->dbname}' {$e->getMessage()}");
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

    public function getTableStatus($table_name): array{
        return $this->query("SHOW TABLE STATUS FROM `{$this->dbname}` WHERE `name` LIKE ?", [$table_name]);
    }

    public function setAutoIncrementTable($table_name, $auto_increment = 0): bool{
        return $this->execute("ALTER TABLE {$table_name} AUTO_INCREMENT = ?", [$auto_increment]);
    }

    public function lastInsertId(): string {
        return $this->pdo->lastInsertId();
    }

    public function errorInfo() {
        return $this->pdo->errorInfo();
    }
}