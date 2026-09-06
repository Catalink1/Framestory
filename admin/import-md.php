<?php
require_once __DIR__ . '/inc/bootstrap.php';
require_login();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error = 'Sesiunea a expirat. Reîncarcă pagina și încearcă din nou.';
    } else {
        // ambele input-uri de fișiere trimit sub același nume "files" — colectăm tot ce a venit din oricare
        $names = $_FILES['files']['name'] ?? [];
        $tmpNames = $_FILES['files']['tmp_name'] ?? [];
        $errors = $_FILES['files']['error'] ?? [];

        $mdTmpPath = null;
        $mdOriginalName = '';
        $images = []; // basename lowercase => tmp path
        $uploadErrors = [];

        foreach ($names as $i => $originalName) {
            if (($errors[$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
            if (($errors[$i] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                $uploadErrors[] = $originalName . ' (cod eroare ' . $errors[$i] . ')';
                continue;
            }
            $base = basename(str_replace('\\', '/', $originalName));
            if (preg_match('/\.(md|markdown|txt)$/i', $base)) {
                if ($mdTmpPath !== null) {
                    $error = 'Am găsit mai multe fișiere .md — încarcă un singur articol o dată (un .md + fotografiile lui).';
                    break;
                }
                $mdTmpPath = $tmpNames[$i];
                $mdOriginalName = $base;
            } elseif (preg_match('/\.(jpe?g|png)$/i', $base)) {
                $images[strtolower($base)] = $tmpNames[$i];
            }
            // alte tipuri de fișiere (ex. .DS_Store dintr-un folder) sunt ignorate silențios
        }

        if ($error === '') {
            if ($uploadErrors) {
                $error = 'Încărcarea a eșuat pentru: ' . implode(', ', $uploadErrors);
            } elseif ($mdTmpPath === null) {
                $error = 'Nu am găsit niciun fișier .md printre cele selectate.';
            } else {
                $content = @file_get_contents($mdTmpPath);
                if ($content === false) {
                    $error = 'Fișierul ' . h($mdOriginalName) . ' nu a putut fi citit.';
                } else {
                    [$draft, $warnings] = draft_from_markdown($content, $images);
                    $_SESSION['import_draft'] = $draft;
                    $_SESSION['import_warnings'] = $warnings;
                    header('Location: edit.php?from_import=1');
                    exit;
                }
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
      Încarcă fișierul <code>.md</code> — cu bloc de metadate la început, între <code>---</code> și <code>---</code>
      (<code>title</code>, <code>seo_title</code>, <code>focus_keyphrase</code>, <code>keywords</code>, <code>excerpt</code>, <code>category</code>, opțional <code>hero</code>, <code>date</code>, <code>type</code>) —
      împreună cu una sau mai multe fotografii, dintr-un folder sau selectate deodată.
      Prima fotografie fără referință explicită în text devine imaginea principală; restul le poți referi în text cu
      <code>![descriere](nume-poza.jpg)</code> și devin imagini inline; poți include și blocuri HTML brute
      (ex. <code>&lt;div class="art-highlight"&gt;...&lt;/div&gt;</code>) direct în fișier — trec neschimbate.
      După încărcare, se deschide formularul obișnuit de articol, precompletat — nimic nu se publică automat.
    </p>

    <?php if ($error): ?><div class="flash flash-error"><?= h($error) ?></div><?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="article-form">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />

      <div class="field">
        <label for="filesMulti">Fișiere (.md + fotografii)</label>
        <input type="file" id="filesMulti" name="files[]" multiple accept=".md,.markdown,.txt,image/jpeg,image/png" />
        <p class="hint">Ține Ctrl (sau Cmd pe Mac) apăsat ca să selectezi mai multe fișiere deodată.</p>
      </div>

      <div class="field">
        <label for="filesFolder">…sau alege un folder întreg <span class="hint-inline">(Chrome/Edge)</span></label>
        <input type="file" id="filesFolder" name="files[]" multiple webkitdirectory />
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Analizează fișierele</button>
        <a href="dashboard.php" class="btn btn-ghost">Anulează</a>
      </div>
    </form>
  </main>
</body>
</html>
