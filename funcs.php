<?php
function debug(array $arr): void
{
    echo '<pre>' . print_r($arr, true) . '</pre>';
}

function registration(): bool
{
    global $pdo;
    $login = isset($_POST['login']) ? trim($_POST['login']) : '';
    $pass = isset($_POST['pass']) ? trim($_POST['pass']) : '';

    if (empty($login) || empty($pass)) {
        $_SESSION['errors'] = 'Поля логин/пароль обязательны';
        return false;
    }

    $res = $pdo->prepare("SELECT COUNT(*) FROM users WHERE login = ?");
    $res->execute([$login]);

    if ($res->fetchColumn()) {
        $_SESSION['errors'] = "Данный логин уже используется!!!";
        return false;
    }

    $pass = password_hash($pass, PASSWORD_DEFAULT);
    $res = $pdo->prepare("INSERT INTO users (login, password) VALUES (?, ?)");

    if ($res->execute([$login, $pass])) {
        $_SESSION['success'] = 'Успешная регистрация';
        return true;
    } else {
        $_SESSION['errors'] = 'Ошибка регистрации';
        return false;
    }
}

function login(): bool
{
    global $pdo;
    $login = isset($_POST['login']) ? trim($_POST['login']) : '';
    $pass = isset($_POST['pass']) ? trim($_POST['pass']) : '';

    if (empty($login) || empty($pass)) {
        $_SESSION['errors'] = 'Поля логин/пароль обязательны';
        return false;
    }

    $res = $pdo->prepare("SELECT * FROM users WHERE login = ?");
    $res->execute([$login]);

    if (!$user = $res->fetch()) {
        $_SESSION['errors'] = 'Логин/пароль введены неверно';
        return false;
    }

    if (!password_verify($pass, $user['password'])) {
        $_SESSION['errors'] = 'Логин/пароль введены неверно';
        return false;
    } else {
        $_SESSION['success'] = 'Вы успешно авторизовались';
        $_SESSION['user']['name'] = $user['login'];
        return true;
    }
}

function save_message(): bool
{
    global $pdo;
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    if (!isset($_SESSION['user']['name'])) {
        $_SESSION['errors'] = 'Необходимо авторизоваться!!!';
    }

    if (empty($message)) {
        $_SESSION['errors'] = 'Введите текс сообщения!';
        return false;
    }

    $res = $pdo->prepare("INSERT INTO messages (name, message) VALUES (?, ?)");
    if ($res->execute([$_SESSION['user']['name'], $message])) {
        $_SESSION['success'] = 'Сообщение добавлено';
        return true;
    } else {
        $_SESSION['errors'] = 'Ошибка';
        return false;
    }
}

function get_messages(): array
{
    global $pdo;
    return $pdo->query("SELECT * FROM messages")->fetchAll();
}

