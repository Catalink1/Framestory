<?php
require_once __DIR__ . '/inc/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$articles = read_articles();
$errors = [];

if (!csrf_check($_POST['csrf_token'] ?? '')) {
    $errors[] = 'Sesiunea a expirat. Reîncarcă pagina și încearcă din nou (textul introdus mai jos s-a pierdut, ne pare rău).';
}

$slugOriginal = trim((string) ($_POST['slug_original'] ?? ''));
$isEdit = $slugOriginal !== '';

$titlu = trim((string) ($_POST['titlu'] ?? ''));
$data = trim((string) ($_POST['data'] ?? ''));
$tip = ($_POST['tip'] ?? '') === 'pilon' ? 'pilon' : 'articol';
$rezumat = trim((string) ($_POST['rezumat'] ?? ''));
$continut = (string) ($_POST['continut'] ?? '');
$imagineExisting = trim((string) ($_POST['imagine_existing'] ?? ''));
$imagineAlt = trim((string) ($_POST['imagine_alt'] ?? ''));
$seoTitle = trim((string) ($_POST['seo_title'] ?? ''));
$focusKeyphrase = trim((string) ($_POST['focus_keyphrase'] ?? ''));
$keywords = trim((string) ($_POST['keywords'] ?? ''));

$categorieSelect = trim((string) ($_POST['categorie_select'] ?? ''));
$categorie = $categorieSelect === '__new__'
    ? trim((string) ($_POST['categorie_noua'] ?? ''))
    : $categorieSelect;

if ($titlu === '') $errors[] = 'Titlul e obligatoriu.';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) $errors[] = 'Data nu e validă.';
if ($categorie === '') $errors[] = 'Alege sau scrie o categorie.';
if ($rezumat === '') $errors[] = 'Rezumatul e obligatoriu.';
if (trim(strip_tags($continut)) === '') $errors[] = 'Conținutul articolului e gol.';

$existingSlugs = array_map(function ($a) { return $a['slug'] ?? ''; }, $articles);
$slugInput = trim((string) ($_POST['slug'] ?? ''));
$slugBase = slugify($slugInput !== '' ? $slugInput : $titlu);
$slug = unique_slug($slugBase, $existingSlugs, $isEdit ? $slugOriginal : null);

$imagine = $imagineExisting;
if (!$errors && !empty($_FILES['imagine']['name']) && ($_FILES['imagine']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['imagine'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Încărcarea imaginii a eșuat (cod eroare ' . $file['error'] . '). Verifică dimensiunea fișierului.';
    } else {
        try {
            $imagine = process_uploaded_image($file['tmp_name'], $slug);
        } catch (ImageError $e) {
            $errors[] = $e->getMessage();
        }
    }
}

if ($errors) {
    $article = [
        'slug' => $slugInput !== '' ? $slugInput : $slug,
        'titlu' => $titlu,
        'data' => $data !== '' ? $data : date('Y-m-d'),
        'categorie' => $categorie,
        'tip' => $tip,
        'rezumat' => $rezumat,
        'continut' => $continut,
        'imagine' => $imagineExisting,
        'imagine_alt' => $imagineAlt,
        'seo_title' => $seoTitle,
        'focus_keyphrase' => $focusKeyphrase,
        'keywords' => $keywords,
    ];
    $categories = existing_categories($articles);
    http_response_code(400);
    ?>
    <!doctype html>
    <html lang="ro">
    <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <title>Eroare la salvare — Admin</title>
      <link rel="stylesheet" href="assets/admin.css" />
      <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
    </head>
    <body>
      <?php include __DIR__ . '/inc/topbar.php'; ?>
      <main class="admin-main admin-main-narrow">
        <h1><?= $isEdit ? 'Editează articol' : 'Articol nou' ?></h1>
        <?php include __DIR__ . '/inc/article-form.php'; ?>
      </main>
      <script src="assets/admin.js"></script>
    </body>
    </html>
    <?php
    exit;
}

$entry = [
    'slug' => $slug,
    'titlu' => $titlu,
    'data' => $data,
    'categorie' => $categorie,
    'tip' => $tip,
    'rezumat' => $rezumat,
    'continut' => $continut,
];
if ($imagine !== '') $entry['imagine'] = $imagine;
if ($imagineAlt !== '') $entry['imagine_alt'] = $imagineAlt;
if ($seoTitle !== '') $entry['seo_title'] = $seoTitle;
if ($focusKeyphrase !== '') $entry['focus_keyphrase'] = $focusKeyphrase;
if ($keywords !== '') $entry['keywords'] = $keywords;

if ($isEdit) {
    $found = false;
    foreach ($articles as $i => $a) {
        if (($a['slug'] ?? '') === $slugOriginal) {
            $articles[$i] = $entry;
            $found = true;
            break;
        }
    }
    if (!$found) array_unshift($articles, $entry);
} else {
    array_unshift($articles, $entry);
}

write_articles($articles);
sync_sitemap($articles);

header('Location: dashboard.php?saved=1');
exit;
