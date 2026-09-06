<?php
require_once __DIR__ . '/inc/bootstrap.php';
require_login();

$items = read_press();

$flash = '';
if (($_GET['saved'] ?? '') === '1') $flash = 'Mențiune salvată.';
if (($_GET['deleted'] ?? '') === '1') $flash = 'Mențiune ștearsă.';
?>
<!doctype html>
<html lang="ro">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Presă — Admin FrameStory</title>
  <link rel="stylesheet" href="assets/admin.css" />
</head>
<body>
  <?php include __DIR__ . '/inc/topbar.php'; ?>

  <main class="admin-main">
    <div class="admin-header-row">
      <h1>Presă <span class="hint-inline">(<?= count($items) ?> mențiuni)</span></h1>
      <a href="press-edit.php" class="btn btn-primary">+ Mențiune nouă</a>
    </div>

    <?php if ($flash): ?><div class="flash flash-ok"><?= h($flash) ?></div><?php endif; ?>

    <input type="text" id="pressFilter" class="press-filter" placeholder="Caută după sursă sau titlu…" />

    <?php if (!$items): ?>
      <p class="empty-state">Nu există încă nicio mențiune de presă. <a href="press-edit.php">Adaugă prima</a>.</p>
    <?php else: ?>
      <table class="article-table" id="pressTable">
        <thead>
          <tr>
            <th>Sursă</th>
            <th>Titlu</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $it): ?>
            <tr data-search="<?= h(str_lower(($it['site'] ?? '') . ' ' . ($it['title'] ?? ''))) ?>">
              <td><?= h($it['site'] ?? '') ?></td>
              <td><?= h($it['title'] ?? '') ?></td>
              <td class="actions-cell">
                <a href="press-edit.php?id=<?= urlencode($it['id'] ?? '') ?>">Editează</a>
                <a href="<?= h($it['url'] ?? '#') ?>" target="_blank" rel="noopener">Deschide</a>
                <form method="post" action="press-delete.php" onsubmit="return confirm('Sigur ștergi mențiunea de la « <?= h(addslashes($it['site'] ?? '')) ?> »?');" class="inline-form">
                  <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />
                  <input type="hidden" name="id" value="<?= h($it['id'] ?? '') ?>" />
                  <button type="submit" class="link-btn link-btn-danger">Șterge</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </main>

  <script>
    var filterInput = document.getElementById("pressFilter");
    if (filterInput) {
      filterInput.addEventListener("input", function () {
        var q = filterInput.value.trim().toLowerCase();
        document.querySelectorAll("#pressTable tbody tr").forEach(function (row) {
          row.hidden = q !== "" && row.dataset.search.indexOf(q) === -1;
        });
      });
    }
  </script>
</body>
</html>
