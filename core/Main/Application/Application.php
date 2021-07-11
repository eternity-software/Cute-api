<?php

namespace Core\Main\Application;

use Core\Utils\Answer;

abstract class Application
{
    /**
     * Корневая директория приложения
     * @var string
     */
    protected static string $rootDir;
    /**
     * Последняя доступная версия приложения
     * @var string
     */
    protected static string $last_version;
    /**
     * Версия текущей сессии приложения
     * @var string
     */
    protected static string $version;

    /**
     * Получение запрашиваемой версии API
     * @return string
     */
    public static function getVersion(): string {
        return self::$version;
    }

    /**
     * Задание текущей версии API
     * @param $version
     */
    protected static function setVersion($version): void{
        // Проверяем, существует ли запрашиваемая версия
        if(!file_exists(self::$rootDir."/Version/{$version}/")){
            Answer::criticalError(["Version {$version} is missing!"]);
        }
        // Подключаем страницы загруженной версии
        require_once self::$rootDir."/Version/{$version}/routes.php";
        self::$version = $version;
    }

    /**
     * Задание последней доступной версии API
     * @param $lastVersion
     */
    protected static function setLastVersion($lastVersion): void{
        self::$last_version = $lastVersion;
    }

    /**
     * Задание корневой директории приложения
     * @param $path
     */
    protected static function setRootDir($path): void{
        self::$rootDir = $path;
    }

    /**
     * Получение корневой директории приложения
     */
    protected static function getRootDir(): string{
        return self::$rootDir;
    }

    /**
     * Метод инициализации приложения
     */
    protected abstract static function launch(): void;
}