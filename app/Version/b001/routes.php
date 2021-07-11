<?php

use \Core\Main\Router\Router;

// -------------------- Account

// Создание аккаунта
Router::addRoute(
    "account.create",  "GET", new App\Version\b001\Account\Controller(), "create", "anonymous", [
    "email" => [
        "type" => "string",
        "use" => "email",
        "min" => 4,
        "max" => 120,
        "required"
    ],
    "login" => [
        "type" => "string",
        "min" => 4,
        "max" => 45,
        "required"
    ],
    "password" => [
        "type" => "string",
        "min" => 6,
        "max" => 45,
        "required"
    ]
]);

// Отправка письма с кодом подтверждения
Router::addRoute("account.sendConfirm",  "GET", new App\Version\b001\Account\Controller(), "sendConfirm", "user");

// Активация аккаунта кодом подтверждения
Router::addRoute(
    "account.confirm",  "GET", new App\Version\b001\Account\Controller(), "confirm", "user", [
    "code" => [
        "type" => "int",
        "min" => 6,
        "max" => 6,
        "required"
    ]
]);

// Авторизация в системе
Router::addRoute(
    "account.auth",  "GET", new App\Version\b001\Account\Controller(), "auth", "anonymous", [
    "login" => [
        "type" => "string",
        "min" => 4,
        "max" => 45,
        "required"
    ],
    "password" => [
        "type" => "string",
        "min" => 6,
        "max" => 45,
        "required"
    ]
]);

// Получаение текущего аккаунта
Router::addRoute("account.get",  "GET", new App\Version\b001\Account\Controller(), "get", "user");

// Редактирование аккаунта
Router::addRoute(
    "account.edit",  "GET", new App\Version\b001\Account\Controller(), "edit", "user", [
    "display_name" => [
        "type" => "string",
        "min" => 2,
        "max" => 60
    ],
    "display_surname" => [
        "type" => "string",
        "min" => 2,
        "max" => 60
    ],
    "display_status_text" => [
        "type" => "string",
        "min" => 2,
        "max" => 120,
    ],
    "bio" => [
        "type" => "string",
        "min" => 2,
        "max" => 300,
    ]
]);

// Выход из аккаунта (закрытие сессии)
Router::addRoute("account.logout",  "GET", new App\Version\b001\Account\Controller(), "logout", "user");

// Получение чатов для аккаунта
Router::addRoute("account.getConversations",  "GET", new App\Version\b001\Account\Controller(), "getConversations", "user");

// -------------------- Message

// Создание беседы
Router::addRoute(
    "message.createConversation",  "GET", new App\Version\b001\Account\Controller(), "createConversation", "user", [
    "type" => [
        "type" => "enum",
        "list" => ["personal", "private", "public", "channel"],
        "required"
    ],
    "title" => [
        "type" => "string",
        "min" => 1,
        "max" => 60,
        "required"
    ],
    "description" => [
        "type" => "string",
        "min" => 1,
        "max" => 300,
        "required"
    ]
]);

// Получение информации о беседе
Router::addRoute(
    "message.getConversation",  "GET", new App\Version\b001\Account\Controller(), "getConversation", "user", [
    "id" => [
        "type" => "int",
        "min" => 1,
        "max" => 11,
        "required"
    ]
]);

// Получение информации о беседе
Router::addRoute(
    "message.getConversation",  "GET", new App\Version\b001\Account\Controller(), "getConversation", "user", [
    "id" => [
        "type" => "int",
        "min" => 1,
        "max" => 11,
        "required"
    ]
]);

// Удаление истории диалога
Router::addRoute(
    "message.removeHistory",  "GET", new App\Version\b001\Account\Controller(), "removeHistory", "user", [
    "id" => [
        "type" => "int",
        "min" => 1,
        "max" => 11,
        "required"
    ]
]);

// Выход из беседы
Router::addRoute(
    "message.leaveConversation",  "GET", new App\Version\b001\Account\Controller(), "leaveConversation", "user", [
    "id" => [
        "type" => "int",
        "min" => 1,
        "max" => 11,
        "required"
    ]
]);

// Отправка сообщения
Router::addRoute(
    "message.send",  "GET", new App\Version\b001\Account\Controller(), "send", "user", [
    "id" => [
        "type" => "int",
        "min" => 1,
        "max" => 11,
        "required"
    ],
    "text" => [
        "type" => "string",
        "min" => 1,
        "max" => 2000,
        "required"
    ]
]);

// Получение списка сообщений
Router::addRoute(
    "message.getList",  "GET", new App\Version\b001\Account\Controller(), "getList", "user", [
    "conversation_id" => [
        "type" => "int",
        "min" => 1,
        "max" => 11,
        "required"
    ],
    "last_message_id" => [
        "type" => "int",
        "min" => 1,
        "max" => 11
    ]
]);