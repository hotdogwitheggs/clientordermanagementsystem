<?php
require_once 'db.php';
require_once 'functions.php';
require_once 'session.php';

function login($email_or_username, $password) {
    global $database;

    $sql = "SELECT * FROM users WHERE email = :identifier OR username = :identifier LIMIT 1";
    $user = $database->selectOne($sql, ['identifier' => $email_or_username]);

    if (!$user) {
        return false;
    }

    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        return true;
    }

    return false;
}

function logout() {
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
}
