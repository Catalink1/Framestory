<?php
require_once __DIR__ . '/inc/bootstrap.php';
require_login();

$settings = read_settings();
$report = $_SESSION['settings_report'] ?? null;
unset($_SESSION['settings_report']);
?>
<!doctype html>
<html lang="ro">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Setări — Admin FrameStory</title>
  <link rel="stylesheet" href="assets/admin.css" />
</head>
<body>
  <?php include __DIR__ . '/inc/topbar.php'; ?>

  <main class="admin-main admin-main-narrow">
    <h1>Setări generale</h1>
    <p class="hint" style="margin-bottom:1.5rem">Aceste câmpuri actualizează automat contactul, linkurile social și ID-ul de Google Analytics de pe site (secțiunea vizibilă de contact + footer din <code>index.html</code>, tag-ul de analytics din paginile care îl au). Nu ating datele structurate (schema.org) — acelea rămân neschimbate.</p>

    <?php if ($report): ?>
      <div class="flash flash-ok">
        <strong>Rezultat salvare:</strong>
        <ul>
          <?php foreach ($report as $file => $ops): ?>
            <?php foreach ($ops as $label => $status): ?>
              <li>
                <?= h($file) ?> — <?= h($label) ?>:
                <?php if ($status === 'done'): ?>actualizat
                <?php elseif ($status === 'unchanged'): ?>neschimbat
                <?php elseif ($status === 'missing-file'): ?><span style="color:var(--admin-danger)">fișier negăsit</span>
                <?php else: ?><span style="color:var(--admin-danger)">sărit — valoarea veche nu s-a găsit exact în fișier, verifică manual</span><?php endif; ?>
              </li>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" action="settings-save.php" class="article-form">
      <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />

      <div class="field">
        <label for="email">Email de contact</label>
        <input type="email" id="email" name="email" value="<?= h($settings['email']) ?>" required />
      </div>

      <div class="field">
        <label for="telefon">Telefon <span class="hint-inline">(format afișat, ex. „0722 595 568")</span></label>
        <input type="text" id="telefon" name="telefon" value="<?= h($settings['telefon']) ?>" required />
      </div>

      <div class="field">
        <label for="locatie">Locație</label>
        <input type="text" id="locatie" name="locatie" value="<?= h($settings['locatie']) ?>" required />
      </div>

      <div class="field">
        <label for="ga_id">ID Google Analytics <span class="hint-inline">(ex. G-XXXXXXXXXX)</span></label>
        <input type="text" id="ga_id" name="ga_id" value="<?= h($settings['ga_id']) ?>" />
      </div>

      <div class="field">
        <label for="facebook">Facebook</label>
        <input type="url" id="facebook" name="facebook" value="<?= h($settings['social']['facebook']) ?>" />
      </div>
      <div class="field">
        <label for="linkedin">LinkedIn</label>
        <input type="url" id="linkedin" name="linkedin" value="<?= h($settings['social']['linkedin']) ?>" />
      </div>
      <div class="field">
        <label for="instagram">Instagram</label>
        <input type="url" id="instagram" name="instagram" value="<?= h($settings['social']['instagram']) ?>" />
      </div>
      <div class="field">
        <label for="tiktok">TikTok</label>
        <input type="url" id="tiktok" name="tiktok" value="<?= h($settings['social']['tiktok']) ?>" />
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Salvează setările</button>
      </div>
    </form>
  </main>
</body>
</html>
