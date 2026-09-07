<?php
/**
 * admin/inc/texte-form.php — formular creare/editare text publicat.
 * Așteaptă: $item (assoc array), $errors (array), $isEdit (bool),
 *           $temeExistente (array de string-uri, opțional).
 */
if (!defined('ADMIN_ACCESS')) {
    http_response_code(403);
    exit('Acces direct interzis.');
}

$item = $item ?? [];
$errors = $errors ?? [];
$isEdit = $isEdit ?? false;
$temeExistente = $temeExistente ?? [];
$lang = ($item['lang'] ?? 'ro') === 'en' ? 'en' : 'ro';

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

<form method="post" action="texte-save.php" class="article-form">
  <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />
  <input type="hidden" name="id_original" value="<?= $v('id') ?>" />

  <div class="field">
    <label for="titlu">Titlul textului</label>
    <input type="text" id="titlu" name="titlu" value="<?= $v('titlu') ?>" required autofocus />
  </div>

  <div class="field">
    <label for="url">Link către articol</label>
    <input type="url" id="url" name="url" value="<?= $v('url') ?>" placeholder="https://..." required />
  </div>

  <div class="field">
    <label for="sursa">Publicație / sursă</label>
    <input type="text" id="sursa" name="sursa" value="<?= $v('sursa') ?>" placeholder="ex. ekonews.ro" required />
    <p class="hint">Numele domeniului unde a apărut textul. Se afișează pe cartonaș.</p>
  </div>

  <div class="field">
    <label for="tema">Temă</label>
    <input type="text" id="tema" name="tema" value="<?= $v('tema') ?>" list="temeList" placeholder="ex. Cultură, artă &amp; patrimoniu" required />
    <?php if ($temeExistente): ?>
      <datalist id="temeList">
        <?php foreach ($temeExistente as $t): ?><option value="<?= h($t) ?>"></option><?php endforeach; ?>
      </datalist>
      <p class="hint">Grupează textul pe pagina publică. Alege una existentă din listă sau scrie una nouă.</p>
    <?php endif; ?>
  </div>

  <div class="field">
    <label for="data">Data publicării</label>
    <input type="date" id="data" name="data" value="<?= $v('data') ?>" />
    <p class="hint">Opțional. Ordonează textele în cadrul unei teme (cele mai noi sus).</p>
  </div>

  <div class="field">
    <label for="lang">Limba</label>
    <select id="lang" name="lang">
      <option value="ro" <?= $lang === 'ro' ? 'selected' : '' ?>>Română</option>
      <option value="en" <?= $lang === 'en' ? 'selected' : '' ?>>Engleză</option>
    </select>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Salvează modificările' : 'Adaugă textul' ?></button>
    <a href="texte-dashboard.php" class="btn btn-ghost">Anulează</a>
  </div>
</form>
