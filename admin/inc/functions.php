<?php
/**
 * admin/inc/functions.php — funcții helper pentru panoul de admin FrameStory.
 * Nu se accesează direct (protejat prin ADMIN_ACCESS + .htaccess).
 */
if (!defined('ADMIN_ACCESS')) {
    http_response_code(403);
    exit('Acces direct interzis.');
}

define('BLOG_JSON_PATH', dirname(__DIR__, 2) . '/data/blog.json');
define('PRESS_JSON_PATH', dirname(__DIR__, 2) . '/data/presa.json');
define('SETTINGS_JSON_PATH', dirname(__DIR__, 2) . '/data/settings.json');
define('IMG_DIR_FS', dirname(__DIR__, 2) . '/img');
define('SITEMAP_PATH', dirname(__DIR__, 2) . '/sitemap.xml');
define('SITE_URL', 'https://framestory.ro');

/* ─────────── output helper ─────────── */
function h($str) {
    return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
}

/* ─────────── mbstring, cu fallback dacă extensia lipsește de pe server ─────────── */
function str_len($s) {
    return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);
}
function str_lower($s) {
    return function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s);
}

/* ─────────── CSRF ─────────── */
function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrf_check($token) {
    return isset($_SESSION['csrf']) && is_string($token) && hash_equals($_SESSION['csrf'], $token);
}

/* ─────────── slug ─────────── */
function slugify($text) {
    $map = [
        'ă' => 'a', 'â' => 'a', 'î' => 'i', 'ș' => 's', 'ş' => 's', 'ț' => 't', 'ţ' => 't',
        'Ă' => 'a', 'Â' => 'a', 'Î' => 'i', 'Ș' => 's', 'Ş' => 's', 'Ț' => 't', 'Ţ' => 't',
    ];
    $text = strtr($text, $map);
    $text = str_lower($text);
    $text = preg_replace('/[^a-z0-9]+/u', '-', $text);
    $text = trim($text, '-');
    $text = preg_replace('/-+/', '-', $text);
    return $text === '' ? 'articol' : $text;
}

/** Întoarce un slug unic, adăugând -2, -3... dacă e nevoie. */
function unique_slug($base, array $existingSlugs, $excludeSlug = null) {
    $existingSlugs = array_filter($existingSlugs, function ($s) use ($excludeSlug) {
        return $excludeSlug === null || $s !== $excludeSlug;
    });
    $existingSlugs = array_flip($existingSlugs);
    $slug = $base;
    $i = 2;
    while (isset($existingSlugs[$slug])) {
        $slug = $base . '-' . $i;
        $i++;
    }
    return $slug;
}

/* ─────────── data/blog.json ─────────── */
/**
 * Citește o colecție JSON (array de obiecte) de la o cale dată.
 * Folosită atât pentru data/blog.json, cât și pentru data/presa.json.
 */
function read_json_collection($path) {
    $json = @file_get_contents($path);
    if ($json === false) return [];
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

/** Scrie o colecție JSON la o cale dată, cu lock exclusiv. */
function write_json_collection($path, array $items) {
    $json = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) return false;
    $fp = fopen($path, 'c');
    if (!$fp) return false;
    $ok = false;
    if (flock($fp, LOCK_EX)) {
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, $json);
        fflush($fp);
        flock($fp, LOCK_UN);
        $ok = true;
    }
    fclose($fp);
    return $ok;
}

function read_articles() {
    return read_json_collection(BLOG_JSON_PATH);
}
function write_articles(array $articles) {
    return write_json_collection(BLOG_JSON_PATH, $articles);
}

function read_press() {
    return read_json_collection(PRESS_JSON_PATH);
}
function write_press(array $items) {
    return write_json_collection(PRESS_JSON_PATH, $items);
}

/** Citește data/settings.json ca array asociativ, cu valori implicite dacă lipsesc chei. */
function read_settings() {
    $defaults = [
        'email' => '', 'telefon' => '', 'locatie' => '', 'ga_id' => '',
        'social' => ['facebook' => '', 'linkedin' => '', 'instagram' => '', 'tiktok' => ''],
    ];
    $json = @file_get_contents(SETTINGS_JSON_PATH);
    $data = $json !== false ? json_decode($json, true) : null;
    if (!is_array($data)) $data = [];
    $data += $defaults;
    $data['social'] = (is_array($data['social'] ?? null) ? $data['social'] : []) + $defaults['social'];
    return $data;
}
function write_settings(array $settings) {
    $json = json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    return @file_put_contents(SETTINGS_JSON_PATH, $json, LOCK_EX) !== false;
}

/* ─────────── imagini ─────────── */
class ImageError extends Exception {}

function unique_filename($base, $ext) {
    $name = $base . '.' . $ext;
    $i = 2;
    while (file_exists(IMG_DIR_FS . '/' . $name)) {
        $name = $base . '-' . $i . '.' . $ext;
        $i++;
    }
    return $name;
}

/**
 * Procesează o imagine uploadată: redimensionează la max 1920px lățime,
 * salvează originalul (jpg/png) + un .webp alături, în img/.
 * Întoarce numele fișierului salvat (ex. "titlu-articol.jpg").
 */
function process_uploaded_image($tmpPath, $baseName) {
    if (!function_exists('imagewebp')) {
        throw new ImageError('Serverul nu are suport WebP în extensia GD. Cere-i prietenei tale să activeze GD cu suport WebP — altfel imaginile nu se vor afișa corect pe site.');
    }

    $info = @getimagesize($tmpPath);
    if (!$info) {
        throw new ImageError('Fișierul încărcat nu este o imagine validă.');
    }
    [$width, $height, $type] = $info;

    switch ($type) {
        case IMAGETYPE_JPEG:
            $src = @imagecreatefromjpeg($tmpPath);
            $ext = 'jpg';
            break;
        case IMAGETYPE_PNG:
            $src = @imagecreatefrompng($tmpPath);
            $ext = 'png';
            break;
        default:
            throw new ImageError('Sunt acceptate doar imagini JPG sau PNG.');
    }
    if (!$src) {
        throw new ImageError('Imaginea nu a putut fi citită (fișier corupt?).');
    }

    $maxW = 1920;
    if ($width > $maxW) {
        $newW = $maxW;
        $newH = (int) round($height * ($maxW / $width));
        $resized = imagecreatetruecolor($newW, $newH);
        if ($ext === 'png') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }
        imagecopyresampled($resized, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);
        imagedestroy($src);
        $src = $resized;
    }

    $filename = unique_filename($baseName, $ext);
    $fsPath = IMG_DIR_FS . '/' . $filename;

    $saved = $ext === 'jpg'
        ? imagejpeg($src, $fsPath, 82)
        : imagepng($src, $fsPath, 6);

    if (!$saved) {
        imagedestroy($src);
        throw new ImageError('Imaginea nu a putut fi salvată pe server (verifică drepturile de scriere pe img/).');
    }

    $webpName = preg_replace('/\.(jpe?g|png)$/i', '.webp', $filename);
    $webpSaved = imagewebp($src, IMG_DIR_FS . '/' . $webpName, 78);
    imagedestroy($src);

    if (!$webpSaved) {
        throw new ImageError('Versiunea WebP a imaginii nu a putut fi generată.');
    }

    return $filename;
}

/* ─────────── sitemap.xml ─────────── */
/**
 * Extrage loc/lastmod/changefreq/priority dintr-un bloc <url>...</url> brut.
 */
function parse_sitemap_url_block($block) {
    preg_match('/<loc>(.*?)<\/loc>/s', $block, $mLoc);
    preg_match('/<lastmod>(.*?)<\/lastmod>/s', $block, $mLast);
    preg_match('/<changefreq>(.*?)<\/changefreq>/s', $block, $mFreq);
    preg_match('/<priority>(.*?)<\/priority>/s', $block, $mPrio);
    return [
        'loc' => trim($mLoc[1] ?? ''),
        'lastmod' => trim($mLast[1] ?? ''),
        'changefreq' => trim($mFreq[1] ?? ''),
        'priority' => trim($mPrio[1] ?? ''),
    ];
}

function render_sitemap_url_block(array $u) {
    $out = "  <url>\n    <loc>{$u['loc']}</loc>\n";
    if ($u['lastmod'] !== '') $out .= "    <lastmod>{$u['lastmod']}</lastmod>\n";
    if ($u['changefreq'] !== '') $out .= "    <changefreq>{$u['changefreq']}</changefreq>\n";
    if ($u['priority'] !== '') $out .= "    <priority>{$u['priority']}</priority>\n";
    return $out . "  </url>";
}

/**
 * Regenerează sitemap.xml complet: păstrează neschimbate toate blocurile <url>
 * care NU sunt articole de blog (home, ancore, blog.html, presa.html...),
 * elimină intrările vechi de articole și le înlocuiește cu unele proaspete,
 * generate din data/blog.json curent. Rescriere completă și deterministă —
 * nu editare "chirurgicală" cu regex pe fișierul existent (fragilă, putea
 * muta/duplica blocuri la apeluri succesive).
 */
function sync_sitemap(array $articles) {
    $xml = @file_get_contents(SITEMAP_PATH);
    if ($xml === false) return false;

    preg_match_all('/<url>.*?<\/url>/s', $xml, $m);

    $kept = [];
    foreach ($m[0] as $block) {
        if (strpos($block, 'articol.html?slug=') !== false) continue;
        $kept[] = parse_sitemap_url_block($block);
    }

    foreach ($articles as $a) {
        if (empty($a['slug'])) continue;
        $kept[] = [
            'loc' => SITE_URL . '/articol.html?slug=' . rawurlencode($a['slug']),
            'lastmod' => h($a['data'] ?? date('Y-m-d')),
            'changefreq' => 'monthly',
            'priority' => (($a['tip'] ?? '') === 'pilon') ? '0.9' : '0.7',
        ];
    }

    $body = implode("\n", array_map('render_sitemap_url_block', $kept));
    $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
         . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
         . $body . "\n</urlset>\n";

    return @file_put_contents(SITEMAP_PATH, $out, LOCK_EX) !== false;
}

/* ─────────── categorii existente (pentru select) ─────────── */
function existing_categories(array $articles) {
    $cats = [];
    foreach ($articles as $a) {
        if (!empty($a['categorie'])) $cats[$a['categorie']] = true;
    }
    return array_keys($cats);
}

/* ─────────── sincronizare setări (email/telefon/social/GA) în paginile HTML ─────────── */

/**
 * Aplică o listă de înlocuiri "găsește exact X de N ori, înlocuiește cu Y"
 * pe un fișier. Dacă X nu apare exact de N ori, acea înlocuire e SĂRITĂ
 * (nu se scrie nimic pentru ea) — nu vrem niciodată să stricăm un fișier
 * pe baza unei presupuneri greșite despre conținutul lui curent.
 * Întoarce un raport: pentru fiecare operație, 'done' | 'skipped' | 'unchanged'.
 */
function apply_verified_replacements($filePath, array $ops) {
    $content = @file_get_contents($filePath);
    $report = [];
    if ($content === false) {
        foreach ($ops as $op) $report[$op['label']] = 'missing-file';
        return $report;
    }

    $changed = false;
    foreach ($ops as $op) {
        $find = $op['find'];
        $replace = $op['replace'];
        $expected = $op['count'];
        $label = $op['label'];

        if ($find === $replace) { $report[$label] = 'unchanged'; continue; }

        $actual = substr_count($content, $find);
        if ($actual !== $expected) { $report[$label] = 'skipped'; continue; }

        $content = str_replace($find, $replace, $content);
        $report[$label] = 'done';
        $changed = true;
    }

    if ($changed) {
        @file_put_contents($filePath, $content, LOCK_EX);
    }
    return $report;
}

/** Derivă din numărul de telefon afișat (ex. "0722 595 568") variantele folosite în site. */
function phone_variants($display) {
    $digits = preg_replace('/\D+/', '', $display);
    $intl = '';
    if (strlen($digits) === 10 && $digits[0] === '0') {
        $intl = '+40' . substr($digits, 1);
    }
    return ['display' => $display, 'digits' => $digits, 'intl' => $intl];
}

/**
 * Sincronizează valorile de setări (email, telefon, locație, social, GA ID)
 * din $old (settings.json dinainte de salvare) către $new (ce tocmai s-a
 * salvat), în index.html / blog.html / presa.html. NU atinge blocurile
 * JSON-LD (schema.org) — acelea rămân neschimbate intenționat, sunt o
 * îmbunătățire separată dacă e nevoie.
 *
 * Întoarce un raport plat, util pentru afișat în admin ce s-a schimbat
 * efectiv și ce a fost sărit (fișier lipsă valoare veche neregăsită etc.)
 */
function sync_site_settings(array $old, array $new) {
    $root = dirname(__DIR__, 2);
    $report = [];

    // ── contact + locație, doar în index.html (secțiunea vizibilă de contact) ──
    $oldPhone = phone_variants($old['telefon'] ?? '');
    $newPhone = phone_variants($new['telefon'] ?? '');

    $indexOps = [
        ['label' => 'Email', 'find' => (string) ($old['email'] ?? ''), 'replace' => (string) ($new['email'] ?? ''), 'count' => 4],
        // ancorate cu context (nu doar cifrele goale) — "0722595568" e coincidental
        // o subsecvență a formatului internațional "+40722595568", deci un match pe
        // cifrele goale ar număra greșit 3 apariții în loc de 2 și ar fi sărit degeaba.
        ['label' => 'Telefon (link tel:)', 'find' => 'tel:' . $oldPhone['digits'], 'replace' => 'tel:' . $newPhone['digits'], 'count' => 1],
        ['label' => 'Telefon (schema.org)', 'find' => '"telephone": "' . $oldPhone['digits'] . '"', 'replace' => '"telephone": "' . $newPhone['digits'] . '"', 'count' => 1],
        ['label' => 'Telefon (format afișat)', 'find' => $oldPhone['display'], 'replace' => $newPhone['display'], 'count' => 1],
        ['label' => 'Locație', 'find' => (string) ($old['locatie'] ?? ''), 'replace' => (string) ($new['locatie'] ?? ''), 'count' => 1],
        ['label' => 'Facebook', 'find' => (string) ($old['social']['facebook'] ?? ''), 'replace' => (string) ($new['social']['facebook'] ?? ''), 'count' => 1],
        // LinkedIn apare identic (fără query params) și în footer și în JSON-LD "sameAs" — 2 apariții reale.
        ['label' => 'LinkedIn', 'find' => (string) ($old['social']['linkedin'] ?? ''), 'replace' => (string) ($new['social']['linkedin'] ?? ''), 'count' => 2],
        ['label' => 'Instagram', 'find' => (string) ($old['social']['instagram'] ?? ''), 'replace' => (string) ($new['social']['instagram'] ?? ''), 'count' => 1],
        ['label' => 'TikTok', 'find' => (string) ($old['social']['tiktok'] ?? ''), 'replace' => (string) ($new['social']['tiktok'] ?? ''), 'count' => 1],
    ];
    if ($oldPhone['intl'] !== '' && $newPhone['intl'] !== '') {
        $indexOps[] = ['label' => 'Telefon (format internațional)', 'find' => $oldPhone['intl'], 'replace' => $newPhone['intl'], 'count' => 1];
    }
    $report['index.html'] = apply_verified_replacements($root . '/index.html', $indexOps);

    // ── ID Google Analytics, în toate paginile care îl au ──
    $oldGa = (string) ($old['ga_id'] ?? '');
    $newGa = (string) ($new['ga_id'] ?? '');
    if ($oldGa !== $newGa) {
        foreach (['index.html', 'blog.html', 'presa.html'] as $file) {
            $report[$file . ' (Google Analytics)'] = apply_verified_replacements(
                $root . '/' . $file,
                [['label' => 'ID Google Analytics', 'find' => $oldGa, 'replace' => $newGa, 'count' => 2]]
            );
        }
    }

    return $report;
}

/* ─────────── import articol din Markdown ─────────── */

/**
 * Desparte un fișier .md în [frontmatter, corp]. Frontmatter e blocul
 * "--- ... ---" de la începutul fișierului, cu perechi simple "cheie: valoare"
 * pe fiecare linie (nu implementăm YAML complet — nu avem nevoie de liste
 * sau obiecte imbricate, doar chei plate). Dacă nu există bloc frontmatter,
 * întoarce un array gol și tot fișierul ca și corp.
 */
function parse_frontmatter($content) {
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $content); // BOM, dacă există
    if (!preg_match('/^---\s*\r?\n(.*?)\r?\n---\s*\r?\n?/s', $content, $m)) {
        return [[], $content];
    }
    $meta = [];
    foreach (preg_split('/\r?\n/', $m[1]) as $line) {
        if (trim($line) === '' || strpos($line, ':') === false) continue;
        [$key, $value] = explode(':', $line, 2);
        $key = strtolower(trim($key));
        $value = trim($value);
        // scoate ghilimelele de încadrare, dacă există
        if (strlen($value) >= 2 && (
            ($value[0] === '"' && substr($value, -1) === '"') ||
            ($value[0] === "'" && substr($value, -1) === "'")
        )) {
            $value = substr($value, 1, -1);
        }
        $meta[$key] = $value;
    }
    $body = substr($content, strlen($m[0]));
    return [$meta, ltrim($body, "\r\n")];
}

/** Convertește un draft (frontmatter + body Markdown) în structura folosită de article-form.php. */
function draft_from_markdown($mdContent) {
    require_once __DIR__ . '/Parsedown.php';
    [$meta, $body] = parse_frontmatter($mdContent);

    $parsedown = new Parsedown();
    $parsedown->setSafeMode(false); // avem nevoie de HTML brut (casete evidențiate, stat-cards) trecut neatins
    $html = $parsedown->text(trim($body));

    return [
        'titlu' => $meta['title'] ?? '',
        'seo_title' => $meta['seo_title'] ?? '',
        'focus_keyphrase' => $meta['focus_keyphrase'] ?? '',
        'keywords' => $meta['keywords'] ?? '',
        'rezumat' => $meta['excerpt'] ?? '',
        'categorie' => $meta['category'] ?? ($meta['categorie'] ?? ''),
        'data' => $meta['date'] ?? date('Y-m-d'),
        'tip' => (($meta['type'] ?? ($meta['tip'] ?? '')) === 'pilon') ? 'pilon' : 'articol',
        'slug' => $meta['slug'] ?? '',
        'continut' => $html,
    ];
}
