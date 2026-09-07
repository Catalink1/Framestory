<?php
require_once __DIR__ . '/inc/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: texte-dashboard.php');
    exit;
}
if (!csrf_check($_POST['csrf_token'] ?? '')) {
    header('Location: texte-dashboard.php');
    exit;
}

$id = trim((string) ($_POST['id'] ?? ''));
$items = read_texte();
$remaining = array_values(array_filter($items, function ($it) use ($id) {
    return ($it['id'] ?? '') !== $id;
}));

if (count($remaining) !== count($items)) {
    write_texte($remaining);
}

header('Location: texte-dashboard.php?deleted=1');
exit;
