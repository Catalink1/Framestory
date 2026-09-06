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
function read_articles() {
    $json = @file_get_contents(BLOG_JSON_PATH);
    if ($json === false) return [];
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function write_articles(array $articles) {
    $json = json_encode($articles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) return false;
    $fp = fopen(BLOG_JSON_PATH, 'c');
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
