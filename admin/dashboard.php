<?php
require_once __DIR__ . '/inc/bootstrap.php';
require_login();

$articles = read_articles();
// cele mai noi primele, ca pe blog.html
usort($articles, function ($a, $b) {
    return strcmp($b['data'] ?? '', $a['data'] ?? '');
});

$flash = '';
if (($_GET['saved'] ?? '') === '1') $flash = 'Articol salvat.';
if (($_GET['deleted'] ?? '') === '1') $flash = 'Articol șters.';

function fmt_ro_date($str) {
    $luni = ['ian', 'feb', 'mar', 'apr', 'mai', 'iun', 'iul', 'aug', 'sep', 'oct', 'noi', 'dec'];
    $parts = explode('-', (string) $str);
    if (count($parts) !== 3) return h($str);
    [$y, $m, $z] = $parts;
    return ((int) $z) . ' ' . ($luni[((int) $m) - 1] ?? '') . ' ' . $y;
}
?>
<!doctype html>
<html lang="ro">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Articole — Admin FrameStory</title>
  <link rel="stylesheet" href="assets/admin.css" />
</head>
<body>
  <?php include __DIR__ . '/inc/topbar.php'; ?>

  <main class="admin-main">
    <div class="admin-header-row">
      <h1>Articole</h1>
      <a href="edit.php" class="btn btn-primary">+ Articol nou</a>
    </div>

    <?php if ($flash): ?><div class="flash flash-ok"><?= h($flash) ?></div><?php endif; ?>

    <?php if (!$articles): ?>
      <p class="empty-state">Nu există încă niciun articol. <a href="edit.php">Scrie primul</a>.</p>
    <?php else: ?>
      <table class="article-table">
        <thead>
          <tr>
            <th>Titlu</th>
            <th>Categorie</th>
            <th>Tip</th>
            <th>Data</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($articles as $a): ?>
            <tr>
              <td><?= h($a['titlu'] ?? '') ?></td>
              <td><?= h($a['categorie'] ?? '') ?></td>
              <td><?= ($a['tip'] ?? '') === 'pilon' ? '<span class="badge badge-pilon">pilon</span>' : '<span class="badge">articol</span>' ?></td>
              <td><?= fmt_ro_date($a['data'] ?? '') ?></td>
              <td class="actions-cell">
                <a href="edit.php?slug=<?= urlencode($a['slug'] ?? '') ?>">Editează</a>
                <a href="../articol.html?slug=<?= urlencode($a['slug'] ?? '') ?>" target="_blank" rel="noopener">Vezi live</a>
                <form method="post" action="delete.php" onsubmit="return confirm('Sigur ștergi articolul « <?= h(addslashes($a['titlu'] ?? '')) ?> »?');" class="inline-form">
                  <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />
                  <input type="hidden" name="slug" value="<?= h($a['slug'] ?? '') ?>" />
                  <button type="submit" class="link-btn link-btn-danger">Șterge</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </main>
</body>
</html>
