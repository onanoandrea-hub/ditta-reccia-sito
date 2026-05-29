<?php

declare(strict_types=1);

$configPath = dirname(__DIR__) . '/config/config.php';
if (!is_file($configPath)) {
  http_response_code(500);
  header('Content-Type: text/plain; charset=utf-8');
  echo "Config mancante: copia config/config.example.php in config/config.php";
  exit;
}

$config = require $configPath;

function db(): PDO
{
  static $pdo = null;
  global $config;
  if ($pdo instanceof PDO) {
    return $pdo;
  }

  $db = $config['db'];
  $host = (string) ($db['host'] ?? '127.0.0.1');
  // Evita connessione via socket Unix quando host è "localhost".
  if ($host === 'localhost') {
    $host = '127.0.0.1';
  }

  $charset = $db['charset'] ?? 'utf8mb4';
  if (!empty($db['unix_socket'])) {
    $dsn = sprintf('mysql:unix_socket=%s;dbname=%s;charset=%s', $db['unix_socket'], $db['name'], $charset);
  } else {
    $port = isset($db['port']) ? ';port=' . (int) $db['port'] : '';
    $dsn = sprintf('mysql:host=%s%s;dbname=%s;charset=%s', $host, $port, $db['name'], $charset);
  }

  $pdo = new PDO($dsn, $db['user'], $db['pass'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);

  return $pdo;
}

function h(string $value): string
{
  return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** @return list<string> */
function gallery_categories(): array
{
  return ['lavori', 'fotovoltaico', 'clienti'];
}

function valid_category(string $category): bool
{
  return in_array($category, gallery_categories(), true);
}

function category_label(string $category): string
{
  $labels = [
    'lavori' => 'Lavori',
    'fotovoltaico' => 'Fotovoltaico',
    'clienti' => 'I nostri clienti',
  ];
  return $labels[$category] ?? $category;
}

function uploads_dir(string $category): string
{
  return dirname(__DIR__) . '/uploads/' . $category;
}

function public_upload_url(string $category, string $filename): string
{
  return 'uploads/' . rawurlencode($category) . '/' . rawurlencode($filename);
}
