<?php
/**
 * admin/inc/press-form.php — formular creare/editare mențiune de presă.
 * Așteaptă: $item (assoc array), $errors (array), $isEdit (bool).
 */
if (!defined('ADMIN_ACCESS')) {
    http_response_code(403);
    exit('Acces direct interzis.');
}

$item = $item ?? [];
$errors = $errors ?? [];
$isEdit = $isEdit ?? false;

$v = function ($key) use ($item) {
    return h($item[$key] ?? '');
};
?>
<?php if ($errors): ?>
  <div class="flash flash-error">
    <ul>
      <?php foreach ($errors as $err): ?><li><?= h($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" action="press-save.php" class="article-form">
  <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />
  <input type="hidden" name="id_original" value="<?= $v('id') ?>" />

  <div class="field">
    <label for="site">Publicație / sursă</label>
    <input type="text" id="site" name="site" value="<?= $v('site') ?>" placeholder="ex. b2b-conect.ro" required autofocus />
  </div>

  <div class="field">
    <label for="title">Titlul articolului</label>
    <input type="text" id="title" name="title" value="<?= $v('title') ?>" required />
  </div>

  <div class="field">
    <label for="url">Link către articol</label>
    <input type="url" id="url" name="url" value="<?= $v('url') ?>" placeholder="https://..." required />
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Salvează modificările' : 'Adaugă mențiunea' ?></button>
    <a href="press-dashboard.php" class="btn btn-ghost">Anulează</a>
  </div>
</form>
