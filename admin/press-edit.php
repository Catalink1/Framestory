<?php
require_once __DIR__ . '/inc/bootstrap.php';
require_login();

$items = read_press();
$id = trim((string) ($_GET['id'] ?? ''));
$isEdit = $id !== '';
$item = [];

if ($isEdit) {
    $found = null;
    foreach ($items as $it) {
        if (($it['id'] ?? '') === $id) { $found = $it; break; }
    }
    if (!$found) {
        header('Location: press-dashboard.php');
        exit;
    }
    $item = $found;
}

$errors = [];
?>
<!doctype html>
<html lang="ro">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $isEdit ? 'Editează mențiune' : 'Mențiune nouă' ?> — Admin FrameStory</title>
  <link rel="stylesheet" href="assets/admin.css" />
</head>
<body>
  <?php include __DIR__ . '/inc/topbar.php'; ?>

  <main class="admin-main admin-main-narrow">
    <h1><?= $isEdit ? 'Editează mențiune' : 'Mențiune nouă de presă' ?></h1>
    <?php include __DIR__ . '/inc/press-form.php'; ?>
  </main>
</body>
</html>
