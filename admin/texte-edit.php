<?php
require_once __DIR__ . '/inc/bootstrap.php';
require_login();

$items = read_texte();
$id = trim((string) ($_GET['id'] ?? ''));
$isEdit = $id !== '';
$item = [];

if ($isEdit) {
    $found = null;
    foreach ($items as $it) {
        if (($it['id'] ?? '') === $id) { $found = $it; break; }
    }
    if (!$found) {
        header('Location: texte-dashboard.php');
        exit;
    }
    $item = $found;
}

$temeExistente = [];
foreach ($items as $it) {
    if (!empty($it['tema'])) $temeExistente[$it['tema']] = true;
}
$temeExistente = array_keys($temeExistente);

$errors = [];
?>
<!doctype html>
<html lang="ro">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $isEdit ? 'Editează text' : 'Text nou' ?> — Admin</title>
  <link rel="stylesheet" href="assets/admin.css" />
</head>
<body>
  <?php include __DIR__ . '/inc/topbar.php'; ?>

  <main class="admin-main admin-main-narrow">
    <h1><?= $isEdit ? 'Editează text publicat' : 'Text publicat nou' ?></h1>
    <?php include __DIR__ . '/inc/texte-form.php'; ?>
  </main>
</body>
</html>
