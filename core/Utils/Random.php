<?php

namespace Core\Utils;

class Random {
    /**
     * Алфавиты генирации рандомных строк
     */
    public const STRING_EVERYONE = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ*/$#:+-";
    public const STRING_WITHOUT_SPECIFIC = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ.";
    public const STRING_TEXT = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

    /**
     * Генерация рандомного числового кода подтверждения
     * @param int $length
     * @return string
     */
    public static function code($length = 6) : string{
        $min = str_repeat(0, $length-1) . 1;
        $max = str_repeat(9, $length);
        return mt_rand($min, $max);
    }

    /**
     * Генерация рандомной строки
     * @param int $length
     * @return string
     */
    public static function string(int $length = 10, string $characters = self::STRING_EVERYONE) : string{
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++)
        {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Выбор рандомного элемента массива
     * @param array $array
     * @return mixed
     */
    public static function arrayElement(array $array){
        // Генерируем рандомный индекс
        $index = rand(0, count($array)-1);
        // Если вдруг не найден объект с таким индексом, пробуем снова
        if(!isset($array[$index])){
            self::arrayElement($array);
        }
        return $array[$index];
    }
}