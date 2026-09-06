<?php
require_once __DIR__ . '/inc/bootstrap.php';
admin_logout();
header('Location: index.php');
exit;
