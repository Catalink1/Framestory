<?php
/**
 * admin/inc/auth.php — verificare sesiune. Inclus din bootstrap.php.
 */
if (!defined('ADMIN_ACCESS')) {
    http_response_code(403);
    exit('Acces direct interzis.');
}

function is_logged_in() {
    return !empty($_SESSION['admin_logged_in']);
}

/** Apelat la începutul fiecărei pagini protejate. */
function require_login() {
    if (!is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

function admin_login($username, $password) {
    $config = $GLOBALS['adminConfig'];
    if (!$config) return false;
    if (!hash_equals($config['username'], (string) $username)) return false;
    if (!password_verify((string) $password, $config['password_hash'])) return false;

    session_regenerate_id(true);
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_username'] = $config['username'];
    return true;
}

function admin_logout() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
