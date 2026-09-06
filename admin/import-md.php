<?php
require_once __DIR__ . '/inc/bootstrap.php';
require_login();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error = 'Sesiunea a expirat. Reîncarcă pagina și încearcă din nou.';
    } elseif (empty($_FILES['mdfile']['name']) || ($_FILES['mdfile']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        $error = 'Alege un fișier .md.';
    } elseif ($_FILES['mdfile']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Încărcarea fișierului a eșuat (cod ' . $_FILES['mdfile']['error'] . ').';
    } else {
        $name = $_FILES['mdfile']['name'];
        if (!preg_match('/\.(md|markdown|txt)$/i', $name)) {
            $error = 'Fișierul trebuie să aibă extensia .md, .markdown sau .txt.';
        } else {
            $content = @file_get_contents($_FILES['mdfile']['tmp_name']);
            if ($content === false) {
                $error = 'Fișierul nu a putut fi citit.';
            } else {
                $draft = draft_from_markdown($content);
                $_SESSION['import_draft'] = $draft;
                header('Location: edit.php?from_import=1');
                exit;
            }
        }
    }
}
?>
<!doctype html>
<html lang="ro">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Importă din Markdown — Admin FrameStory</title>
  <link rel="stylesheet" href="assets/admin.css" />
</head>
<body>
  <?php include __DIR__ . '/inc/topbar.php'; ?>

  <main class="admin-main admin-main-narrow">
    <h1>Importă articol din Markdown</h1>
    <p class="hint" style="margin-bottom:1.5rem">
      Încarcă un fișier <code>.md</code> cu bloc de metadate la început, între <code>---</code> și <code>---</code>
      (<code>title</code>, <code>seo_title</code>, <code>focus_keyphrase</code>, <code>keywords</code>, <code>excerpt</code>, <code>category</code>, opțional <code>date</code>/<code>type</code>),
      apoi textul articolului în Markdown obișnuit dedesubt. Poți include și blocuri HTML brute
      (ex. <code>&lt;div class="art-highlight"&gt;...&lt;/div&gt;</code>) direct în fișier — trec neschimbate.
      După încărcare, se deschide formularul obișnuit de articol, precompletat — nimic nu se publică automat.
    </p>

    <?php if ($error): ?><div class="flash flash-error"><?= h($error) ?></div><?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="article-form">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />
      <div class="field">
        <label for="mdfile">Fișier .md</label>
        <input type="file" id="mdfile" name="mdfile" accept=".md,.markdown,.txt" required />
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Analizează fișierul</button>
        <a href="dashboard.php" class="btn btn-ghost">Anulează</a>
      </div>
    </form>
  </main>
</body>
</html>
