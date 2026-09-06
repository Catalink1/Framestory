<?php
/**
 * admin/inc/bootstrap.php — se include primul, pe fiecare pagină din admin.
 */
define('ADMIN_ACCESS', true);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/functions.php';

define('ADMIN_CONFIG_PATH', dirname(__DIR__) . '/config.php');
$GLOBALS['adminConfig'] = file_exists(ADMIN_CONFIG_PATH) ? include ADMIN_CONFIG_PATH : null;

require_once __DIR__ . '/auth.php';
