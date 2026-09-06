<?php
require_once __DIR__ . '/inc/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
    http_response_code(403);
    echo json_encode(['error' => 'Neautentificat.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Cerere invalidă.']);
    exit;
}
if (!csrf_check($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['error' => 'Sesiune expirată — reîncarcă pagina.']);
    exit;
}

$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Încărcare eșuată (cod ' . $file['error'] . ').']);
    exit;
}

$base = trim((string) ($_POST['base'] ?? ''));
$base = $base !== '' ? slugify($base) : 'imagine-' . date('YmdHis');

try {
    $filename = process_uploaded_image($file['tmp_name'], $base);
} catch (ImageError $e) {
    http_response_code(422);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

$webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $filename);
$alt = trim((string) ($_POST['alt'] ?? ''));

$html = '<div class="art-inline-img"><picture><source srcset="img/' . h($webp) . '" type="image/webp" /><img src="img/' . h($filename) . '" alt="' . h($alt) . '" loading="lazy" /></picture></div>';

echo json_encode([
    'filename' => $filename,
    'webp' => $webp,
    'url' => '../img/' . $filename,
    'html' => $html,
]);
