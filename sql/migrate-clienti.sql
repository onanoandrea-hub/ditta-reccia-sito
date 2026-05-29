-- Aggiunge la galleria "clienti" a un database già installato.
-- In phpMyAdmin: seleziona il database, poi esegui questo SQL.

ALTER TABLE photos
  MODIFY category ENUM('lavori', 'fotovoltaico', 'clienti') NOT NULL;
