<?php

namespace Test;
use App\Version\b001\Account\Controller;
use Core\Main\Database\Connection;

class AccountControllerTest extends TestCaseAuth
{
    public static string $session_key = "";
    protected static string $email;
    protected static string $login;
    protected static string $password;

    /**
     * Действия перед началом тестирования
     */
    public static function setUpBeforeClass(): void {
        // Генерируем данные для тестирования
        self::$email = FakeData::email();
        self::$login = FakeData::text();
        self::$password = FakeData::text(6, 25);
    }

    /**
     * Действия перед тестированием каждого метода
     */
    public function setUp(): void
    {
        // Добавляем зависимые таблицы
        $this->addDependentTable("account");
        $this->addDependentTable("account_session");
        // Задаём тестируемый контроллер
        $this->setController(new Controller());
        // Выполняем шаблонные действия
        parent::setUp();
    }

    /**
     * Тестируем создание
     */
    public function testCreate(): array{
        // Выполняем метод, записываем результат
        $result = $this->getMethodResult("create", [self::$email, self::$login, self::$password]);
        // Проверяем содержимое пришедшей даты
        $this->verifyDataResult($result, "session_key");
        // Выводим результат для следующего теста
        return [self::$login, self::$password];
    }

    /**
     * Тестируем авторизацию
     * @depends testCreate
     */
    public function testAuth($data): string {
        list($login, $password) = $data;
        // Выполняем метод, записываем результат
        $result = $this->getMethodResult("auth", [$login, $password]);
        // Проверяем содержимое пришедшей даты
        $this->verifyDataResult($result, "session_key");
        self::$session_key = $result["session_key"];
        // Выводим результат
        return self::$session_key;
    }

    /**
     * Проверяем, является ли сессия активной
     * @param $session_key
     * @depends testAuth
     */
    public function testVerifyAuth($session_key) {
        // Выполняем метод, записываем результат
        $result = $this->getMethodResult("verifyAuth", [$session_key]);
        // Проверяем содержимое пришедшей даты
        $this->verifyDataResult($result, "account");
    }

    /**
     * Проверяем, является ли сессия активной, а аккаунт активироанным
     * @param $session_key
     * @depends testAuth
     */
    public function testVerifyActive($session_key) {
        // Выполняем метод
        $this->getMethodResult("verifyActive", [$session_key], "error");
    }

    /**
     * Отправляем код подтверждения
     * @param $session_key
     * @depends testAuth
     */
    public function testSendConfirm($session_key)
    {
        // Выполняем метод
        $this->getMethodResult("sendConfirm", [$session_key]);
        // Получаем сгенерированный код подтверждения из БД
        $result_code = Connection::getInstance()->query(
    "SELECT 
        activation.code 
        FROM 
        account_activation_code activation, 
        account_session session 
        WHERE 
        activation.account_id = session.account_id AND 
        session.session_key = ? 
        ORDER BY activation.id DESC 
        LIMIT 1", [$session_key]);
        // Проверяем, получился ли код
        $this->assertCount(1, $result_code, "Error message: ".json_encode($result_code));
        $this->assertArrayHasKey("code", $result_code[0]);
        // Если получился, выводим для следующего теста
        return $result_code[0]["code"];
    }

    /**
     * Активируем аккаунт
     * @param $session_key
     * @param $code
     * @depends testAuth
     * @depends testSendConfirm
     */
    public function testConfirm($session_key, $code)
    {
        // Выполняем метод
        $this->getMethodResult("confirm", [$session_key, $code]);
    }

    /**
     * Получаем аккаунт
     * @param $session_key
     * @depends testAuth
     */
    public function testGet($session_key)
    {
        // Выполняем метод и записываем результат
        $result = $this->getMethodResult("get", [$session_key]);
        // Проверяем содержимое пришедшей даты
        $this->verifyDataResult($result, "account");
        $this->verifyDataResult($result["account"], "email", self::$email);
        $this->verifyDataResult($result["account"], "login", self::$login);
        $this->verifyDataResult($result["account"], "display_name", self::$login);
        $this->verifyDataResult($result["account"], "display_surname");
        $this->verifyDataResult($result["account"], "display_status_text");
        $this->verifyDataResult($result["account"], "bio");
    }

    /**
     * Редактируем аккаунт
     * @param $session_key
     * @depends testAuth
     */
    public function testEdit($session_key)
    {
        // Генерируем рандомные данные
        $display_name = FakeData::text(2, 60);
        $display_surname = FakeData::text(2, 60);
        $display_status_text = FakeData::text(2, 120);
        $bio = FakeData::text(2, 300);
        // Выполняем метод, записываем результат
        $this->getMethodResult("edit", [$session_key, $display_name, $display_surname, $display_status_text, $bio]);
        // Выполняем получение аккаунта для проверки
        $result = $this->getMethodResult("get", [$session_key]);
        // Сравниваем
        $this->verifyDataResult($result, "account");
        $this->verifyDataResult($result["account"], "display_name", $display_name);
        $this->verifyDataResult($result["account"], "display_surname", $display_surname);
        $this->verifyDataResult($result["account"], "display_status_text", $display_status_text);
        $this->verifyDataResult($result["account"], "bio", $bio);
    }

    /**
     * Получаем диалоги
     * @param $session_key
     * @depends testAuth
     */
    public function testGetConversations($session_key)
    {
        // Выполняем и записываем
        $result_assert = $this->controller->getConversations($session_key);
        // Проверяем выполнение
        $this->assertArrayHasKey("type", $result_assert);
        $this->assertEquals("success", $result_assert["type"]);
    }

    /**
     * Выходим из аккаунта
     * @param $session_key
     * @depends testAuth
     */
    public function testLogout($session_key)
    {
        // Выполняем и записываем
        $result_assert = $this->controller->logout($session_key);
        // Проверяем выполнение
        $this->assertArrayHasKey("type", $result_assert);
        $this->assertEquals("success", $result_assert["type"]);
    }
}