<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

if (admin_logged_in()) {
  header('Location: index.php');
  exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim((string) ($_POST['username'] ?? ''));
  $password = (string) ($_POST['password'] ?? '');
  if (!login_admin($username, $password)) {
    $error = 'Credenziali non valide.';
  } else {
    header('Location: index.php');
    exit;
  }
}
?>
<!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login backoffice</title>
    <link rel="stylesheet" href="assets/admin.css" />
  </head>
  <body class="admin-body">
    <div class="admin-wrap">
      <div class="admin-card">
        <h1>Backoffice foto</h1>
        <p class="admin-muted">Accedi per caricare le immagini delle gallerie.</p>

        <?php if ($error !== ''): ?>
          <div class="admin-alert admin-alert--err"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="post">
          <div class="admin-field">
            <label for="username">Username</label>
            <input id="username" name="username" required autocomplete="username" />
          </div>
          <div class="admin-field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password" />
          </div>
          <button class="admin-btn" type="submit">Accedi</button>
        </form>
      </div>
    </div>
  </body>
</html>
