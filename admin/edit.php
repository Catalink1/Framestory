<?php
require_once __DIR__ . '/inc/bootstrap.php';
require_login();

$articles = read_articles();
$slug = trim((string) ($_GET['slug'] ?? ''));
$isEdit = $slug !== '';
$article = ['data' => date('Y-m-d')];
$fromImport = false;

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
} elseif (($_GET['from_import'] ?? '') === '1' && !empty($_SESSION['import_draft'])) {
    $article = $_SESSION['import_draft'] + $article;
    // e mereu un articol NOU — dacă .md-ul a sugerat un slug, îl arătăm în câmp,
    // dar "slug_original" rămâne gol explicit ca să nu se potrivească accidental
    // (și să suprascrie) un articol existent cu același slug.
    $article['slug_original'] = '';
    $fromImport = true;
    unset($_SESSION['import_draft']);
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
    <?php if ($fromImport): ?>
      <div class="flash flash-ok">Articol precompletat din fișierul Markdown importat — verifică totul înainte să publici.</div>
    <?php endif; ?>
    <?php include __DIR__ . '/inc/article-form.php'; ?>
  </main>

  <script src="assets/admin.js"></script>
</body>
</html>
