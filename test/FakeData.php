<?php

namespace Test;

use Core\Utils\Random;

class FakeData {
    /**
     * Домены возможных Email-ов
     * @var array|string[]
     */
    protected static array $emailDomain = [
        "gmail.com", "mail.ru", "yandex.ru"
    ];

    /**
     * Генерация рандомного Email
     * @param int $length
     * @return string
     */
    public static function email(int $length = 20): string{
        // Проверяем заданную длину (больше 10)
        if($length < 10) exit("Email length less 10..");
        // Получаем рандомный домен
        $randomDomain = Random::arrayElement(self::$emailDomain);
        // Определяем длину рандомной строки
        $length_address = $length - (strlen($randomDomain) + 1);
        // Генерируем строку
        $address = trim(Random::string($length_address, Random::STRING_WITHOUT_SPECIFIC), ".");
        // Соединяем строку воедино
        return "{$address}@{$randomDomain}";
    }

    /**
     * Генерация рандомного текста
     * @param int $minlength
     * @param int $maxlength
     * @return string
     */
    public static function text(int $minlength = 4, int $maxlength = 45): string{
        // Определяем длину рандомной строки
        $length = rand($minlength, $maxlength);
        // Генерируем и выводим строку
        return Random::string($length, Random::STRING_TEXT);
    }
}