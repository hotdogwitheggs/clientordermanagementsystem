<?php
// Start session with custom lifetime if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 7200,
        'path' => '/',
        'secure' => false, // set to true if using HTTPS
        'httponly' => true
    ]);
    session_start();
}

/**
 * Check if a user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if current user is an admin
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if current user is a designer
 */
function is_designer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'designer';
}

/**
 * Check if current user is a regular user
 */
function is_user() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

/**
 * Require login to access a page
 */
function require_login() {
    if (!is_logged_in()) {
        set_session_message('danger', 'You must be logged in to access this page.');
        redirect('/graphic_design_service/login.php');
    }
}

/**
 * Require admin access
 */
function require_admin() {
    require_login();
    if (!is_admin()) {
        set_session_message('danger', 'Access denied. Admins only.');
        redirect('/graphic_design_service/index.php');
    }
}

/**
 * Require user access
 */
function require_user() {
    require_login();
    if (!is_user()) {
        set_session_message('danger', 'Access denied. Regular users only.');
        redirect('/graphic_design_service/index.php');
    }
}

/**
 * Set a session message to show as a flash alert
 */
function set_session_message($type, $text) {
    $_SESSION['message'] = [
        'type' => $type,
        'text' => $text
    ];
}

/**
 * Display the session flash message, if any
 */
function display_session_message() {
    if (isset($_SESSION['message']['type'], $_SESSION['message']['text'])) {
        $type = htmlspecialchars($_SESSION['message']['type']);
        $text = htmlspecialchars($_SESSION['message']['text']);
        echo "<div class=\"alert alert-$type alert-dismissible fade show\" role=\"alert\">
                $text
                <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
              </div>";
        unset($_SESSION['message']);
    }
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: $url");
    exit;
}
