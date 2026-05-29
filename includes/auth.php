<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

function admin_logged_in(): bool
{
  return !empty($_SESSION['admin_id']);
}

function require_admin(): void
{
  if (!admin_logged_in()) {
    header('Location: login.php');
    exit;
  }
}

function login_admin(string $username, string $password): bool
{
  $stmt = db()->prepare('SELECT id, password_hash FROM admin_users WHERE username = ? LIMIT 1');
  $stmt->execute([$username]);
  $row = $stmt->fetch();
  if (!$row || !password_verify($password, $row['password_hash'])) {
    return false;
  }
  $_SESSION['admin_id'] = (int) $row['id'];
  $_SESSION['admin_username'] = $username;
  return true;
}

function logout_admin(): void
{
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool) $p['secure'], (bool) $p['httponly']);
  }
  session_destroy();
}

function csrf_token(): string
{
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
  }
  return $_SESSION['csrf'];
}

function verify_csrf(?string $token): bool
{
  return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}
