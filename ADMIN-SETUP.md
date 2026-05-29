# Backoffice foto (PHP + MySQL)

## 1) Database (phpMyAdmin) — importante

L'errore **#1046 Nessun database selezionato** significa che non hai scelto il database prima di eseguire lo SQL.

### Passi corretti
1. Accedi a **phpMyAdmin** dal pannello Tophost.
2. Nel menu **a sinistra**, clicca sul **nome del database** del sito  
   (es. `dittarecc_reccia` o simile: lo trovi anche in `config.php` del hosting).
3. Solo dopo che il database è selezionato (in alto vedi il nome), vai su **SQL**.
4. Incolla il contenuto di `sql/install.sql` (oppure scheda **Importa** → scegli il file).
5. Clicca **Esegui**.

### Se non hai ancora un database
1. In phpMyAdmin: scheda **Database** → **Crea database** → nome a piacere → Crea.
2. Clicca il nuovo database a sinistra.
3. Poi esegui `sql/install.sql` come sopra.

### Alternativa: riga USE
Nel file `sql/install.sql` puoi decommentare e modificare:
```sql
USE nome_del_tuo_database;
```
con il nome esatto del database Tophost, poi eseguire tutto il file.

## 2) Configurazione PHP
1. Copia `config/config.example.php` in `config/config.php`.
2. Inserisci **gli stessi dati** del database Tophost:
   - `host` → su Tophost di solito è **`sql.tuodominio.it`** (es. `sql.dittarecciariccardo.it`)  
     `localhost` / `127.0.0.1` spesso danno errore 2002 o "Connection refused"
   - `name` = nome database
   - `user` = utente MySQL
   - `pass` = password MySQL

## 3) Primo accesso admin
1. Apri `https://tuodominio.it/admin/install.php`
2. Crea username e password.
3. **Importante:** dopo il setup elimina `admin/install.php` dal server.

## 4) Uso quotidiano
- Accedi: `/admin/login.php`
- Carica foto in **Lavori**, **Fotovoltaico** o **I nostri clienti**
- Le foto compaiono in `lavori.html`, `fotovoltaico.html` e `clienti.html`

### Galleria clienti (sito già online)
Se il database esisteva prima di questa funzione, in phpMyAdmin esegui anche `sql/migrate-clienti.sql` (con il database selezionato a sinistra).
