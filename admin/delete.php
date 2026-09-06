<?php
require_once __DIR__ . '/inc/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}
if (!csrf_check($_POST['csrf_token'] ?? '')) {
    header('Location: dashboard.php');
    exit;
}

$slug = trim((string) ($_POST['slug'] ?? ''));
$articles = read_articles();
$remaining = array_values(array_filter($articles, function ($a) use ($slug) {
    return ($a['slug'] ?? '') !== $slug;
}));

if (count($remaining) !== count($articles)) {
    write_articles($remaining);
    sync_sitemap($remaining);
}

header('Location: dashboard.php?deleted=1');
exit;
