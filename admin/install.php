<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

$error = '';
$ok = '';

// Blocca se esiste già un admin
$count = (int) db()->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
if ($count > 0 && empty($_GET['force'])) {
  header('Location: login.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim((string) ($_POST['username'] ?? ''));
  $password = (string) ($_POST['password'] ?? '');
  $password2 = (string) ($_POST['password2'] ?? '');

  if ($username === '' || strlen($username) < 3) {
    $error = 'Username troppo corto (min 3 caratteri).';
  } elseif (strlen($password) < 8) {
    $error = 'Password troppo corta (min 8 caratteri).';
  } elseif ($password !== $password2) {
    $error = 'Le password non coincidono.';
  } else {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = db()->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)');
    $stmt->execute([$username, $hash]);
    $ok = 'Admin creato. Ora puoi accedere.';
    header('Refresh: 2; url=login.php');
  }
}
?>
<!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Installazione backoffice</title>
    <link rel="stylesheet" href="assets/admin.css" />
  </head>
  <body class="admin-body">
    <div class="admin-wrap">
      <div class="admin-card">
        <h1>Setup backoffice (una volta)</h1>
        <p class="admin-muted">Crea l'account per caricare le foto. Dopo il setup elimina o proteggi questo file.</p>

        <?php if ($error !== ''): ?>
          <div class="admin-alert admin-alert--err"><?= h($error) ?></div>
        <?php endif; ?>
        <?php if ($ok !== ''): ?>
          <div class="admin-alert admin-alert--ok"><?= h($ok) ?></div>
        <?php endif; ?>

        <form method="post">
          <div class="admin-field">
            <label for="username">Username</label>
            <input id="username" name="username" required autocomplete="username" />
          </div>
          <div class="admin-field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required autocomplete="new-password" />
          </div>
          <div class="admin-field">
            <label for="password2">Ripeti password</label>
            <input id="password2" name="password2" type="password" required autocomplete="new-password" />
          </div>
          <button class="admin-btn" type="submit">Crea admin</button>
        </form>
      </div>
    </div>
  </body>
</html>
