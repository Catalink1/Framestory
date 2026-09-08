<?php
require_once __DIR__ . '/inc/bootstrap.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$needsSetup = $GLOBALS['adminConfig'] === null;
$error = '';

if ($needsSetup && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'setup') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error = 'Sesiune expirată, reîncarcă pagina și încearcă din nou.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $password2 = (string) ($_POST['password2'] ?? '');

        if ($username === '' || str_len($username) < 3) {
            $error = 'Alege un utilizator de minim 3 caractere.';
        } elseif (str_len($password) < 8) {
            $error = 'Parola trebuie să aibă minim 8 caractere.';
        } elseif ($password !== $password2) {
            $error = 'Parolele nu coincid.';
        } else {
            $config = [
                'username' => $username,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ];
            $php = "<?php\nreturn " . var_export($config, true) . ";\n";
            if (@file_put_contents(ADMIN_CONFIG_PATH, $php, LOCK_EX) === false) {
                $error = 'Nu am putut scrie config.php — verifică drepturile de scriere pe folderul admin/.';
            } else {
                $GLOBALS['adminConfig'] = $config;
                admin_login($username, $password);
                header('Location: dashboard.php');
                exit;
            }
        }
    }
}

if (!$needsSetup && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    if (!csrf_check($_POST['csrf_token'] ?? '')) {
        $error = 'Sesiune expirată, reîncarcă pagina și încearcă din nou.';
    } elseif (admin_login($_POST['username'] ?? '', $_POST['password'] ?? '')) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Utilizator sau parolă greșită.';
    }
}
?>
<!doctype html>
<html lang="ro">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $needsSetup ? 'Setup admin' : 'Autentificare' ?> — Admin</title>
  <link rel="stylesheet" href="assets/admin.css" />
</head>
<body class="login-body">
  <main class="login-box">
    <div class="login-logo">Cătălin <em>Cocoș</em></div>

    <?php if ($error): ?><div class="flash flash-error"><?= h($error) ?></div><?php endif; ?>

    <?php if ($needsSetup): ?>
      <h1>Configurare inițială</h1>
      <p class="login-sub">Alege un utilizator și o parolă pentru panoul de admin. Se face o singură dată.</p>
      <form method="post">
        <input type="hidden" name="action" value="setup" />
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />
        <div class="field">
          <label for="username">Utilizator</label>
          <input type="text" id="username" name="username" required autofocus />
        </div>
        <div class="field">
          <label for="password">Parolă <span class="hint-inline">(minim 8 caractere)</span></label>
          <input type="password" id="password" name="password" required minlength="8" />
        </div>
        <div class="field">
          <label for="password2">Confirmă parola</label>
          <input type="password" id="password2" name="password2" required minlength="8" />
        </div>
        <button type="submit" class="btn btn-primary btn-block">Creează contul</button>
      </form>
    <?php else: ?>
      <h1>Autentificare</h1>
      <form method="post">
        <input type="hidden" name="action" value="login" />
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />
        <div class="field">
          <label for="username">Utilizator</label>
          <input type="text" id="username" name="username" required autofocus />
        </div>
        <div class="field">
          <label for="password">Parolă</label>
          <input type="password" id="password" name="password" required />
        </div>
        <button type="submit" class="btn btn-primary btn-block">Intră</button>
      </form>
    <?php endif; ?>
  </main>
</body>
</html>
