<?php
/**
 * config.sample.php — șablon. NU e nevoie să-l folosești manual.
 *
 * La prima accesare a /admin/ (dacă nu există încă "config.php" lângă acest
 * fișier), panoul de admin îți arată un formular de setup unde alegi
 * utilizatorul și parola, iar "config.php" se generează automat.
 *
 * Dacă vrei totuși să-l creezi manual: copiază fișierul ca "config.php",
 * generează hash-ul parolei rulând în terminal:
 *
 *   php -r "echo password_hash('parola-ta', PASSWORD_DEFAULT), PHP_EOL;"
 *
 * și lipește rezultatul mai jos.
 */

return [
    'username'      => 'admin',
    'password_hash' => '$2y$10$examplehasheduptothispointdonotusethisdirectly',
];
