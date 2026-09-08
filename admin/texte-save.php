<?php
require_once __DIR__ . '/inc/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: texte-dashboard.php');
    exit;
}

$items = read_texte();
$errors = [];

if (!csrf_check($_POST['csrf_token'] ?? '')) {
    $errors[] = 'Sesiunea a expirat. Reîncarcă pagina și încearcă din nou.';
}

$idOriginal = trim((string) ($_POST['id_original'] ?? ''));
$isEdit = $idOriginal !== '';

$titlu = trim((string) ($_POST['titlu'] ?? ''));
$sursa = trim((string) ($_POST['sursa'] ?? ''));
$url = trim((string) ($_POST['url'] ?? ''));
$tema = trim((string) ($_POST['tema'] ?? ''));
$data = trim((string) ($_POST['data'] ?? ''));
$lang = ($_POST['lang'] ?? 'ro') === 'en' ? 'en' : 'ro';

if ($titlu === '') $errors[] = 'Titlul e obligatoriu.';
if ($sursa === '') $errors[] = 'Publicația / sursa e obligatorie.';
if ($tema === '') $errors[] = 'Tema e obligatorie.';
if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) $errors[] = 'Link-ul nu e o adresă validă (trebuie să înceapă cu http:// sau https://).';
if ($data !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) $errors[] = 'Data nu e validă.';

if ($errors) {
    $item = ['id' => $idOriginal, 'titlu' => $titlu, 'sursa' => $sursa, 'url' => $url, 'tema' => $tema, 'data' => $data, 'lang' => $lang];
    $temeExistente = [];
    foreach ($items as $it) {
        if (!empty($it['tema'])) $temeExistente[$it['tema']] = true;
    }
    $temeExistente = array_keys($temeExistente);
    http_response_code(400);
    ?>
    <!doctype html>
    <html lang="ro">
    <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <title>Eroare la salvare — Admin</title>
      <link rel="stylesheet" href="assets/admin.css" />
    </head>
    <body>
      <?php include __DIR__ . '/inc/topbar.php'; ?>
      <main class="admin-main admin-main-narrow">
        <h1><?= $isEdit ? 'Editează text publicat' : 'Text publicat nou' ?></h1>
        <?php include __DIR__ . '/inc/texte-form.php'; ?>
      </main>
    </body>
    </html>
    <?php
    exit;
}

$existingIds = array_map(function ($it) { return $it['id'] ?? ''; }, $items);
$id = $isEdit ? $idOriginal : unique_slug(slugify($titlu), $existingIds);

$entry = ['id' => $id, 'titlu' => $titlu, 'sursa' => $sursa, 'url' => $url, 'tema' => $tema, 'data' => $data, 'lang' => $lang];

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

write_texte($items);

header('Location: texte-dashboard.php?saved=1');
exit;
