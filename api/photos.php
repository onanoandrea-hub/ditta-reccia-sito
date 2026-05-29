<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=60');

$category = isset($_GET['category']) ? (string) $_GET['category'] : '';
if (!valid_category($category)) {
  http_response_code(400);
  echo json_encode(['error' => 'invalid_category']);
  exit;
}

$stmt = db()->prepare(
  'SELECT title, filename FROM photos WHERE category = ? ORDER BY sort_order ASC, id DESC'
);
$stmt->execute([$category]);

$out = [];
foreach ($stmt->fetchAll() as $row) {
  $url = public_upload_url($category, $row['filename']);
  $title = trim((string) $row['title']);
  if ($title === '') {
    $title = 'Foto';
  }
  $out[] = [
    'title' => $title,
    'src' => $url,
    'thumb' => $url,
  ];
}

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
