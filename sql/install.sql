-- =============================================================================
-- phpMyAdmin: PRIMA seleziona il database nel menu a sinistra, POI esegui questo SQL.
-- Se vedi errore #1046 "Nessun database selezionato" = non hai cliccato il DB a sinistra.
--
-- In alternativa, decommenta e modifica la riga sotto con il nome del tuo database Tophost:
-- USE nome_del_tuo_database;
-- =============================================================================

CREATE TABLE IF NOT EXISTS photos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category ENUM('lavori', 'fotovoltaico', 'clienti') NOT NULL,
  title VARCHAR(255) NOT NULL DEFAULT '',
  filename VARCHAR(255) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_category_sort (category, sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(64) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- L'account admin si crea da /admin/install.php (una sola volta).
