<?php
require_once __DIR__ . '/inc/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: press-dashboard.php');
    exit;
}
if (!csrf_check($_POST['csrf_token'] ?? '')) {
    header('Location: press-dashboard.php');
    exit;
}

$id = trim((string) ($_POST['id'] ?? ''));
$items = read_press();
$remaining = array_values(array_filter($items, function ($it) use ($id) {
    return ($it['id'] ?? '') !== $id;
}));

if (count($remaining) !== count($items)) {
    write_press($remaining);
}

header('Location: press-dashboard.php?deleted=1');
exit;
