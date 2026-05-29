<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit;
}

$category = (string) ($_POST['category'] ?? 'lavori');
$id = (int) ($_POST['id'] ?? 0);

if (!verify_csrf($_POST['csrf'] ?? null) || !valid_category($category) || $id <= 0) {
  header('Location: index.php?category=' . urlencode($category));
  exit;
}

$stmt = db()->prepare('SELECT filename FROM photos WHERE id = ? AND category = ? LIMIT 1');
$stmt->execute([$id, $category]);
$row = $stmt->fetch();

if ($row) {
  $path = uploads_dir($category) . '/' . $row['filename'];
  if (is_file($path)) {
    @unlink($path);
  }
  $del = db()->prepare('DELETE FROM photos WHERE id = ? AND category = ?');
  $del->execute([$id, $category]);
}

header('Location: index.php?category=' . urlencode($category));
exit;
