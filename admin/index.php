<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

require_admin();

$message = '';
$error = '';
$category = isset($_GET['category']) ? (string) $_GET['category'] : 'lavori';
if (!valid_category($category)) {
  $category = 'lavori';
}

/**
 * @return list<array{name:string,type:string,tmp_name:string,error:int,size:int}>
 */
function normalize_uploaded_files(array $field): array
{
  if (!isset($field['name'])) {
    return [];
  }
  if (!is_array($field['name'])) {
    return [$field];
  }

  $out = [];
  $count = count($field['name']);
  for ($i = 0; $i < $count; $i++) {
    if (($field['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
      continue;
    }
    $out[] = [
      'name' => (string) $field['name'][$i],
      'type' => (string) ($field['type'][$i] ?? ''),
      'tmp_name' => (string) ($field['tmp_name'][$i] ?? ''),
      'error' => (int) ($field['error'][$i] ?? UPLOAD_ERR_NO_FILE),
      'size' => (int) ($field['size'][$i] ?? 0),
    ];
  }
  return $out;
}

function title_from_original_name(string $originalName): string
{
  $base = pathinfo($originalName, PATHINFO_FILENAME);
  $base = str_replace(['_', '-'], ' ', $base);
  $base = trim($base);
  return $base !== '' ? $base : 'Foto';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
  if (!verify_csrf($_POST['csrf'] ?? null)) {
    $error = 'Sessione scaduta. Riprova.';
  } else {
    $postCategory = (string) ($_POST['category'] ?? '');
    $baseTitle = trim((string) ($_POST['title'] ?? ''));
    $allowed = [
      'image/jpeg' => 'jpg',
      'image/png' => 'png',
      'image/webp' => 'webp',
    ];
    $maxBytes = 8 * 1024 * 1024;

    if (!valid_category($postCategory)) {
      $error = 'Categoria non valida.';
    } elseif (!isset($_FILES['photos']) || !is_array($_FILES['photos'])) {
      $error = 'Nessun file selezionato.';
    } else {
      $files = normalize_uploaded_files($_FILES['photos']);
      if (!$files) {
        $error = 'Nessun file selezionato.';
      } else {
        $dir = uploads_dir($postCategory);
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
          $error = 'Impossibile creare la cartella upload.';
        } else {
          $finfo = new finfo(FILEINFO_MIME_TYPE);
          $stmt = db()->prepare(
            'INSERT INTO photos (category, title, filename, sort_order) VALUES (?, ?, ?, 0)'
          );
          $ok = 0;
          $failures = [];

          foreach ($files as $i => $file) {
            $label = $file['name'] !== '' ? $file['name'] : ('file #' . ($i + 1));

            if ($file['error'] !== UPLOAD_ERR_OK) {
              $failures[] = $label . ': errore upload';
              continue;
            }
            if ($file['size'] > $maxBytes) {
              $failures[] = $label . ': troppo grande (max 8 MB)';
              continue;
            }

            $mime = $finfo->file($file['tmp_name']);
            if (!isset($allowed[$mime])) {
              $failures[] = $label . ': formato non supportato';
              continue;
            }

            $filename = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
            $dest = $dir . '/' . $filename;
            if (!move_uploaded_file($file['tmp_name'], $dest)) {
              $failures[] = $label . ': salvataggio fallito';
              continue;
            }

            if (count($files) === 1 && $baseTitle !== '') {
              $photoTitle = $baseTitle;
            } elseif ($baseTitle !== '' && count($files) > 1) {
              $photoTitle = $baseTitle . ' ' . ($i + 1);
            } else {
              $photoTitle = title_from_original_name($file['name']);
            }

            $stmt->execute([$postCategory, $photoTitle, $filename]);
            $ok++;
          }

          $category = $postCategory;

          if ($ok > 0) {
            $message = $ok === 1 ? '1 foto caricata.' : $ok . ' foto caricate.';
          }
          if ($failures) {
            $error = ($error !== '' ? $error . ' ' : '') . implode(' · ', $failures);
          }
          if ($ok === 0 && $error === '') {
            $error = 'Nessuna foto caricata.';
          }
        }
      }
    }
  }
}

$stmt = db()->prepare(
  'SELECT id, title, filename, created_at FROM photos WHERE category = ? ORDER BY id DESC'
);
$stmt->execute([$category]);
$photos = $stmt->fetchAll();
?>
<!doctype html>
<html lang="it">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Backoffice gallerie</title>
    <link rel="stylesheet" href="assets/admin.css" />
  </head>
  <body class="admin-body">
    <div class="admin-wrap">
      <div class="admin-card">
        <div class="admin-row" style="justify-content: space-between;">
          <div>
            <h1>Gallerie foto</h1>
            <p class="admin-muted">Carica qui le immagini: compaiono automaticamente nel sito.</p>
          </div>
          <div class="admin-row">
            <a class="admin-btn admin-btn--ghost" href="../index.html" target="_blank" rel="noreferrer">Vai al sito</a>
            <a class="admin-btn admin-btn--ghost" href="logout.php">Esci</a>
          </div>
        </div>
      </div>

      <?php if ($message !== ''): ?>
        <div class="admin-alert admin-alert--ok"><?= h($message) ?></div>
      <?php endif; ?>
      <?php if ($error !== ''): ?>
        <div class="admin-alert admin-alert--err"><?= h($error) ?></div>
      <?php endif; ?>

      <div class="admin-card">
        <h2>Carica foto</h2>
        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="action" value="upload" />
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
          <div class="admin-field">
            <label for="category">Galleria</label>
            <select id="category" name="category">
              <?php foreach (gallery_categories() as $cat): ?>
                <option value="<?= h($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= h(category_label($cat)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="admin-field">
            <label for="title">Titolo (opzionale, per tutte le foto)</label>
            <input id="title" name="title" placeholder="Es. Impianto civile Sarroch" />
            <span class="admin-muted">Se carichi più file senza titolo, useremo il nome del file.</span>
          </div>
          <div class="admin-field">
            <label for="photos">Immagini (JPG, PNG, WEBP – max 8 MB ciascuna)</label>
            <input
              id="photos"
              name="photos[]"
              type="file"
              accept="image/jpeg,image/png,image/webp"
              multiple
              required
            />
            <span class="admin-muted">Puoi selezionare più foto insieme (Ctrl/Cmd + clic o trascina).</span>
          </div>
          <button class="admin-btn" type="submit">Carica foto</button>
        </form>
      </div>

      <div class="admin-card">
        <div class="admin-row" style="justify-content: space-between;">
          <h2>Foto in galleria: <?= h(category_label($category)) ?></h2>
          <div class="admin-row">
            <?php foreach (gallery_categories() as $cat): ?>
              <a class="admin-btn admin-btn--ghost" href="?category=<?= h($cat) ?>"><?= h(category_label($cat)) ?></a>
            <?php endforeach; ?>
          </div>
        </div>

        <?php if (!$photos): ?>
          <p class="admin-muted">Nessuna foto in questa galleria.</p>
        <?php else: ?>
          <div class="admin-grid">
            <?php foreach ($photos as $p): ?>
              <?php $url = '../' . public_upload_url($category, $p['filename']); ?>
              <div class="admin-thumb">
                <img src="<?= h($url) ?>" alt="<?= h($p['title'] ?: 'Foto') ?>" loading="lazy" />
                <div class="admin-thumb__meta">
                  <strong><?= h($p['title'] ?: 'Foto') ?></strong><br />
                  <span class="admin-muted"><?= h($p['created_at']) ?></span>
                </div>
                <form method="post" action="delete.php" onsubmit="return confirm('Eliminare questa foto?');">
                  <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>" />
                  <input type="hidden" name="id" value="<?= (int) $p['id'] ?>" />
                  <input type="hidden" name="category" value="<?= h($category) ?>" />
                  <button type="submit">Elimina</button>
                </form>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </body>
</html>
