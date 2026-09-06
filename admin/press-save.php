<?php
require_once __DIR__ . '/inc/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: press-dashboard.php');
    exit;
}

$items = read_press();
$errors = [];

if (!csrf_check($_POST['csrf_token'] ?? '')) {
    $errors[] = 'Sesiunea a expirat. Reîncarcă pagina și încearcă din nou.';
}

$idOriginal = trim((string) ($_POST['id_original'] ?? ''));
$isEdit = $idOriginal !== '';

$site = trim((string) ($_POST['site'] ?? ''));
$title = trim((string) ($_POST['title'] ?? ''));
$url = trim((string) ($_POST['url'] ?? ''));

if ($site === '') $errors[] = 'Sursa e obligatorie.';
if ($title === '') $errors[] = 'Titlul e obligatoriu.';
if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) $errors[] = 'Link-ul nu e o adresă validă (trebuie să înceapă cu http:// sau https://).';

if ($errors) {
    $item = ['id' => $idOriginal, 'site' => $site, 'title' => $title, 'url' => $url];
    http_response_code(400);
    ?>
    <!doctype html>
    <html lang="ro">
    <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <title>Eroare la salvare — Admin FrameStory</title>
      <link rel="stylesheet" href="assets/admin.css" />
    </head>
    <body>
      <?php include __DIR__ . '/inc/topbar.php'; ?>
      <main class="admin-main admin-main-narrow">
        <h1><?= $isEdit ? 'Editează mențiune' : 'Mențiune nouă de presă' ?></h1>
        <?php include __DIR__ . '/inc/press-form.php'; ?>
      </main>
    </body>
    </html>
    <?php
    exit;
}

$existingIds = array_map(function ($it) { return $it['id'] ?? ''; }, $items);
$id = $isEdit ? $idOriginal : unique_slug(slugify($site), $existingIds);

$entry = ['id' => $id, 'site' => $site, 'title' => $title, 'url' => $url];

if ($isEdit) {
    $found = false;
    foreach ($items as $i => $it) {
        if (($it['id'] ?? '') === $idOriginal) {
            $items[$i] = $entry;
            $found = true;
            break;
        }
    }
    if (!$found) $items[] = $entry;
} else {
    $items[] = $entry;
}

write_press($items);

header('Location: press-dashboard.php?saved=1');
exit;
