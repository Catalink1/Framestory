<?php
require_once __DIR__ . '/inc/bootstrap.php';
require_login();

$items = read_texte();

$flash = '';
if (($_GET['saved'] ?? '') === '1') $flash = 'Text salvat.';
if (($_GET['deleted'] ?? '') === '1') $flash = 'Text șters.';
?>
<!doctype html>
<html lang="ro">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Texte publicate — Admin</title>
  <link rel="stylesheet" href="assets/admin.css" />
</head>
<body>
  <?php include __DIR__ . '/inc/topbar.php'; ?>

  <main class="admin-main">
    <div class="admin-header-row">
      <h1>Texte publicate <span class="hint-inline">(<?= count($items) ?> texte)</span></h1>
      <a href="texte-edit.php" class="btn btn-primary">+ Text nou</a>
    </div>

    <?php if ($flash): ?><div class="flash flash-ok"><?= h($flash) ?></div><?php endif; ?>

    <input type="text" id="texteFilter" class="press-filter" placeholder="Caută după titlu, publicație sau temă…" />

    <?php if (!$items): ?>
      <p class="empty-state">Nu există încă niciun text publicat. <a href="texte-edit.php">Adaugă primul</a>.</p>
    <?php else: ?>
      <table class="article-table" id="texteTable">
        <thead>
          <tr>
            <th>Publicație</th>
            <th>Titlu</th>
            <th>Temă</th>
            <th>Data</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $it): ?>
            <tr data-search="<?= h(str_lower(($it['sursa'] ?? '') . ' ' . ($it['titlu'] ?? '') . ' ' . ($it['tema'] ?? ''))) ?>">
              <td><?= h($it['sursa'] ?? '') ?><?= ($it['lang'] ?? 'ro') === 'en' ? ' <span class="hint-inline">EN</span>' : '' ?></td>
              <td><?= h($it['titlu'] ?? '') ?></td>
              <td><?= h($it['tema'] ?? '') ?></td>
              <td><?= h($it['data'] ?? '') ?></td>
              <td class="actions-cell">
                <a href="texte-edit.php?id=<?= urlencode($it['id'] ?? '') ?>">Editează</a>
                <a href="<?= h($it['url'] ?? '#') ?>" target="_blank" rel="noopener">Deschide</a>
                <form method="post" action="texte-delete.php" onsubmit="return confirm('Sigur ștergi textul « <?= h(addslashes($it['titlu'] ?? '')) ?> »?');" class="inline-form">
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
    var filterInput = document.getElementById("texteFilter");
    if (filterInput) {
      filterInput.addEventListener("input", function () {
        var q = filterInput.value.trim().toLowerCase();
        document.querySelectorAll("#texteTable tbody tr").forEach(function (row) {
          row.hidden = q !== "" && row.dataset.search.indexOf(q) === -1;
        });
      });
    }
  </script>
</body>
</html>
