<?php

namespace Core\Base;
use Core\Main\Database\Connection;

abstract class Model {
    /**
     * Подключение к БД
     * @var Connection
     */
    protected Connection $db;

    /**
     * Model конструктор.
     * @param string $db_config_name
     */
    public function __construct(string $db_config_name = "db") {
        $this->db = Connection::getInstance($db_config_name);
    }
}