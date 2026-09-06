<?php
require_once __DIR__ . '/inc/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check($_POST['csrf_token'] ?? '')) {
    header('Location: settings.php');
    exit;
}

$old = read_settings();

$new = [
    'email' => trim((string) ($_POST['email'] ?? '')),
    'telefon' => trim((string) ($_POST['telefon'] ?? '')),
    'locatie' => trim((string) ($_POST['locatie'] ?? '')),
    'ga_id' => trim((string) ($_POST['ga_id'] ?? '')),
    'social' => [
        'facebook' => trim((string) ($_POST['facebook'] ?? '')),
        'linkedin' => trim((string) ($_POST['linkedin'] ?? '')),
        'instagram' => trim((string) ($_POST['instagram'] ?? '')),
        'tiktok' => trim((string) ($_POST['tiktok'] ?? '')),
    ],
];

$report = sync_site_settings($old, $new);
write_settings($new);

$_SESSION['settings_report'] = $report;
header('Location: settings.php');
exit;
