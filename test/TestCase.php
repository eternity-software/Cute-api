<?php

namespace Test;

use Core\Base\Controller;
use Core\Main\Database\Connection;
use Core\Main\Request\Request;

class TestCase extends \PHPUnit\Framework\TestCase {
    /**
     * Зависимые таблицы в БД
     * @var array
     */
    protected static array $dependent_tables = [];

    /**
     * Тестируемый контроллер
     * @var Controller
     */
    protected Controller $controller;


    /**
     * Последний результат
     * @var array
     */
    protected array $last_result = [];

    /**
     * Метод добавления зависимой таблицы
     * @param string $name
     * @return bool
     */
    protected function addDependentTable(string $name): bool{
        // Если таблица уже есть - завершаем
        if(isset(self::$dependent_tables[$name])) return false;
        // Если таблица ещё не добавлена, получаем некоторые её параметры
        $table_status = Connection::getInstance()->getTableStatus($name);
        // Записываем всё это в массив
        self::$dependent_tables[$name] = $table_status;
        return true;
    }

    /**
     * Задаём тестируемый контроллер
     * @param Controller $controller
     */
    protected function setController(Controller $controller){
        $this->controller = $controller;
    }


    /**
     * Выполнить метод и получить результат (с валидацей success)
     * @param string $name
     * @param array $options
     * @param string $type
     * @return array
     */
    public function getMethodResult(string $name, array $options, string $type = "success"): array{
        // Выполняем запрос
        $this->last_result = Request::sendInner($this->controller, $name, $options);
        // Валидация на success
        $this->assertArrayHasKey("type", $this->last_result);
        $this->assertEquals($type, $this->last_result["type"], "Error answer: " . json_encode($this->last_result["data"]));
        $this->assertArrayHasKey("data", $this->last_result);

        return $this->last_result["data"];
    }

    /**
     * Проверяем, есть внутреннее значение даты
     * @param $name
     * @param $value
     * @param $data
     */
    public function verifyDataResult($data, $name, $value = null){
        // Проверяем, выполнен ли запрос
        $this->assertNotEquals([], $this->last_result, "Last result is null");
        // Проверяем, есть ли нужное поле
        $this->assertArrayHasKey($name, $data);
        // Проверяем значение
        if($value !== null) $this->assertEquals($value, $data["$name"]);
    }

    /**
     * Действия перед тестированием каждого метода (подготовка)
     */
    public function setUp(): void {}

    /**
     * После окончания тестирования (каждого класса)
     */
    public static function tearDownAfterClass(): void {
        // Перебираем массив таблиц
        foreach (self::$dependent_tables as $dependent_table => $opts){
            // Приминяем исходные данные счётчика ID (AI)
            $auto_increment = $opts[0]["Auto_increment"];
            Connection::getInstance()->setAutoIncrementTable($dependent_table, $auto_increment);
        }
    }
}