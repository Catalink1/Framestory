<?php
require_once __DIR__ . '/inc/bootstrap.php';
require_login();

$articles = read_articles();
$slug = trim((string) ($_GET['slug'] ?? ''));
$isEdit = $slug !== '';
$article = ['data' => date('Y-m-d')];

if ($isEdit) {
    $found = null;
    foreach ($articles as $a) {
        if (($a['slug'] ?? '') === $slug) { $found = $a; break; }
    }
    if (!$found) {
        header('Location: dashboard.php');
        exit;
    }
    $article = $found;
}

$errors = [];
$categories = existing_categories($articles);
?>
<!doctype html>
<html lang="ro">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $isEdit ? 'Editează articol' : 'Articol nou' ?> — Admin FrameStory</title>
  <link rel="stylesheet" href="assets/admin.css" />
  <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body>
  <?php include __DIR__ . '/inc/topbar.php'; ?>

  <main class="admin-main admin-main-narrow">
    <h1><?= $isEdit ? 'Editează articol' : 'Articol nou' ?></h1>
    <?php include __DIR__ . '/inc/article-form.php'; ?>
  </main>

  <script src="assets/admin.js"></script>
</body>
</html>
