<?php

namespace Test;
use App\Version\b001\Message\Controller;
use Core\Utils\Random;

class MessageControllerTest extends TestCase
{
    protected static string $session_key;
    protected static string $type;
    protected static string $title;
    protected static string $description;

    /**
     * Действия перед началом тестирования
     */
    public static function setUpBeforeClass(): void {
        // Получаем сессию
        self::$session_key = AccountControllerTest::$session_key;
        // Генерируем данные для тестирования
        self::$type = Random::arrayElement(["personal", "private", "public", "channel"]);
        self::$title = FakeData::text(1, 60);
        self::$description = FakeData::text(1, 300);
    }

    /**
     * Действия перед тестированием каждого метода
     */
    public function setUp(): void
    {
        // Добавляем зависимые таблицы
        $this->addDependentTable("conversation");
        $this->addDependentTable("conversation_member");
        $this->addDependentTable("conversation_message");
        // Задаём тестируемый контроллер
        $this->setController(new Controller());
        // Выполняем шаблонные действия
        parent::setUp();
    }


    /**
     * Создание беседы
     * @depends AccountControllerTest::testAuth
     * @return mixed
     */
    public function testCreateConversation($session_key) {
        $result = $this->getMethodResult("createConversation", [$session_key, self::$type, self::$title, self::$description]);
        $this->verifyDataResult($result, "id");
        return $result["id"];
    }

    /**
     * Создание беседы
     * @param $id
     * @depends testCreateConversation
     */
    public function testGetConversation($id) {
        $this->getMethodResult("getConversation", [self::$session_key, $id]);
    }

    public function testSend($session_key) {

    }

    public function testGetList($session_key) {

    }

    public function testRemoveHistory($session_key) {

    }

    public function testLeaveConversation($session_key) {

    }
}