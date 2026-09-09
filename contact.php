<?php
/**
 * contact.php — primește formularul de contact de pe catalincocos.ro și
 * trimite mesajul prin mail() la contact@catalincocos.ro.
 *
 * Răspunde JSON: {ok:true} la succes, {ok:false,error:...} altfel.
 * JS-ul din pagină verifică doar codul HTTP (res.ok).
 */

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method']);
    exit;
}

/* Honeypot — câmpul _gotcha e ascuns; dacă e completat, e bot. */
if (trim((string) ($_POST['_gotcha'] ?? '')) !== '') {
    echo json_encode(['ok' => true]); // pretindem succes, aruncăm mesajul
    exit;
}

/* Fără CR/LF în valorile care ajung în headere (anti header-injection). */
$oneLine = static function ($s) {
    return trim(str_replace(["\r", "\n", "\t"], ' ', (string) $s));
};

$name    = $oneLine($_POST['name'] ?? '');
$email   = $oneLine($_POST['email'] ?? '');
$subject = $oneLine($_POST['subject'] ?? '');
$message = trim((string) ($_POST['message'] ?? ''));

$errors = [];
if ($name === '' || mb_strlen($name) > 120)              $errors[] = 'name';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))          $errors[] = 'email';
if ($message === '' || mb_strlen($message) > 5000)       $errors[] = 'message';

if ($errors) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'validare', 'fields' => $errors]);
    exit;
}

$to      = 'contact@catalincocos.ro';
$subjTxt = 'Mesaj nou de pe catalincocos.ro' . ($subject !== '' ? ' — ' . $subject : '');

$body = "Nume:   $name\n"
      . "Email:  $email\n"
      . "Tip:    " . ($subject !== '' ? $subject : '(nespecificat)') . "\n"
      . "IP:     " . ($_SERVER['REMOTE_ADDR'] ?? '?') . "\n"
      . "Data:   " . date('Y-m-d H:i:s') . "\n"
      . str_repeat('-', 40) . "\n\n"
      . $message . "\n";

$headers = implode("\r\n", [
    'From: catalincocos.ro <contact@catalincocos.ro>',
    'Reply-To: ' . ($name !== '' ? $name . ' ' : '') . '<' . $email . '>',
    'Content-Type: text/plain; charset=utf-8',
    'MIME-Version: 1.0',
    'X-Mailer: contact.php',
]);

$encodedSubject = '=?UTF-8?B?' . base64_encode($subjTxt) . '?=';

$sent = @mail($to, $encodedSubject, $body, $headers, '-f contact@catalincocos.ro');

if ($sent) {
    echo json_encode(['ok' => true]);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'trimitere']);
}
