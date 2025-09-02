# personal-cv-blog-portfolio

[![License: CC BY-NC 4.0](https://img.shields.io/badge/License-CC_BY--NC_4.0-lightgrey.svg)](https://creativecommons.org/licenses/by-nc/4.0/)

Ein dynamisches **Portfolio + Blog + Lebenslauf** mit einfachem **Admin-Bereich**. Du kannst Projekte, Skills, Lebenslauf-Einträge und Blogposts bequem pflegen.

---

## So benutzt du es (kurz & einfach)

1. **Repo herunterladen**

   * Entweder ZIP downloaden und entpacken
   * Oder per Git:

   ```bash
   git clone <DEIN-REPO> personal-cv-blog
   ```

2. **Auf deinen Webspace hochladen**
   Lade alle Dateien in den Ordner, auf den deine Domain zeigt (**Document Root**).
   Beispiele:

   * All-Inkl/Strato: meist etwas wie `/www/htdocs/XXXXX/deine-domain.de/`

3. **Datenbank anlegen & Schema importieren**
   Erstelle eine MySQL/MariaDB-Datenbank (z. B. in **phpMyAdmin** oder per CLI) und importiere die **`schema.sql`**.

**phpMyAdmin:** Datenbank auswählen → *Importieren* → `schema.sql` hochladen → *OK*.
**CLI:**

```bash
mysql -u <USER> -p -e "CREATE DATABASE meine_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u <USER> -p meine_db < schema.sql
```

> Inhalt der `schema.sql` (komplett, zum Kopieren, falls du per Hand ausführen willst):

```sql
-- MySQL schema for blog + simple auth
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(60) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  content MEDIUMTEXT NOT NULL,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optional: seed example post
INSERT INTO posts (title, content, created_at) VALUES
('Willkommen auf meinem Blog', '<p>Das ist ein Beispiel-Beitrag. Viel Spaß beim Schreiben!</p>', NOW());

-- Basiskonfiguration
CREATE TABLE IF NOT EXISTS settings (
  setting_key   VARCHAR(100) PRIMARY KEY,
  setting_value TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS social_links (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  url  VARCHAR(255) NOT NULL,
  icon VARCHAR(80)  DEFAULT NULL,   -- optional: icon-name
  sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Lebenslauf / Timeline
CREATE TABLE IF NOT EXISTS timeline (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  subtitle VARCHAR(160) DEFAULT NULL,
  description MEDIUMTEXT,
  location VARCHAR(160) DEFAULT NULL,
  start_date DATE DEFAULT NULL,
  end_date   DATE DEFAULT NULL,
  sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Projekte
CREATE TABLE IF NOT EXISTS projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  description MEDIUMTEXT,
  url VARCHAR(255) DEFAULT NULL,
  image_url VARCHAR(255) DEFAULT NULL,
  tags VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Skills
CREATE TABLE IF NOT EXISTS skill_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS skills (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  level TINYINT DEFAULT 0,          -- 0..100 oder 0..10
  sort_order INT DEFAULT 0,
  FOREIGN KEY (category_id) REFERENCES skill_categories(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Services
CREATE TABLE IF NOT EXISTS services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(120) NOT NULL,
  description MEDIUMTEXT,
  icon VARCHAR(80) DEFAULT NULL,
  price_from DECIMAL(10,2) DEFAULT NULL,
  sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Testimonials
CREATE TABLE IF NOT EXISTS testimonials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  author VARCHAR(160) NOT NULL,
  role   VARCHAR(160) DEFAULT NULL,
  company VARCHAR(160) DEFAULT NULL,
  text MEDIUMTEXT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  sort_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

4. **`.env` anlegen (wichtiger Schritt!)**
   Lege eine Datei **`.env`** **eine Ebene über** deinem Projektordner ab (also **nicht** öffentlich erreichbar).

Beispiel:

* Projekt: `/www/htdocs/XXXXX/deine-domain.de/`
* `.env`:   `/www/htdocs/XXXXX/.env`

Inhalt der `.env` (Beispieldaten – bitte später durch eigene Werte ersetzen):

```env
DB_HOST=localhost
DB_NAME=meine_db
DB_USER=mein_user
DB_PASS=mein_passwort
DB_CHARSET=utf8mb4
```

Lege eine Datei `koralp.env` **eine Ebene über** deinem Projektordner ab (also **nicht** öffentlich erreichbar).
Beispiel:

* Projekt: `/www/htdocs/XXXXX/deine-domain.de/`
* ENV:     `/www/htdocs/XXXXX/koralp.env`

Inhalt der `koralp.env`:

```env
DB_HOST=localhost
DB_NAME=deine_db
DB_USER=dein_user
DB_PASS=dein_passwort
DB_CHARSET=utf8mb4
```

5. **Konfiguration prüfen & Site-Infos setzen**
   Die Datei **`config.php`** lädt die `.env` und enthält die **Site-Einstellungen**. Prüfe zwei Dinge:

**a) `.env`-Pfad in `config.php`**
Wenn du `koralp.env` in **`.env`** umbenannt hast, stelle sicher, dass in `config.php` die richtige Datei geladen wird:

```php
// Lade die .env-Datei, die eine Ebene höher liegt als das aktuelle Verzeichnis
load_env(__DIR__ . '/../.env');
```

> Falls du den Speicherort änderst, passe den Pfad entsprechend an.

**b) Site-Einstellungen anpassen**
In `config.php` gibt es den Block:

```php
'site' => [
    'base_url'   => 'https://example.com',
    'site_name'  => 'Mein Name – Portfolio & Blog',
    'owner_email'=> 'ich@example.com'
]
```

Passe **`base_url`**, **`site_name`** und **`owner_email`** an **deine** Werte an.
*(Wenn du deine echten Daten nicht im Repo haben willst, verwende Platzhalter und ersetze sie erst auf dem Server.)*

> Hinweis: Du kannst diese Werte alternativ auch in die `.env` auslagern (z. B. `SITE_BASE_URL`, `SITE_NAME`, `OWNER_EMAIL`) und dann in `config.php` mit `getenv('...') ?: 'Fallback'` auslesen.

**Checkliste – Wo muss ich `.env` ggf. noch erwähnen/anpassen?**

* `config.php` → `load_env(__DIR__ . '/../.env')` (Pfad zur Datei)
* `db.php` → enthält nur die Fehlermeldung; optional Text anpassen, damit dort auch **`.env`** steht (rein kosmetisch)

6. **Admin-Benutzer anlegen (einmalig)**
   Rufe im Browser `create_admin.php` auf, setze Benutzername/Passwort → danach **Datei löschen**.
   Rufe im Browser `create_admin.php` auf, setze Benutzername/Passwort → danach **Datei löschen**.

7. **Login & Inhalte pflegen**
   Öffne `https://deine-domain.de/login.php`, melde dich an und pflege **Timeline, Projekte, Skills, Services, Testimonials und Blogposts**.

---

## Was ist drin?

* Startseite mit Profil/Intro
* **Timeline (CV)**, **Projekte**, **Skills**, **Services**, **Testimonials**
* **Blog** (Übersicht & einzelne Posts)
* **Admin-Dashboard** mit einfachen Formularen (CRUD)
* Kontaktformular, Health-Check

> Keine Frameworks nötig: reines PHP (PDO), etwas JS/CSS.

---

## Typische Ordner/Dateien (kurzer Überblick)

```
/ (Webroot deiner Domain)
├── index.php, blog.php, post.php
├── login.php, logout.php
├── posts.php, post_edit.php, post_delete.php
├── projects.php, project_edit.php, project_delete.php
├── skills.php, skill_edit.php, skill_delete.php
├── services.php, service_edit.php, service_delete.php
├── testimonials.php, testimonial_edit.php, testimonial_delete.php
├── timeline.php, timeline_edit.php, timeline_delete.php
├── profile_edit.php
├── send_contact.php, health.php
├── style.css, main.js
├── db.php, bootstrap.php, config.php
└── schema.sql

(außerhalb des Webroots/ eine Ebene höher)
└── koralp.env
```

---

## Häufige Fragen (kurz)

**Seite weiß / Fehler 500?**
→ Prüfe `config.php` (richtiger ENV-Pfad?), DB-Zugangsdaten, PHP-Version ≥ 8.0.

**DB verbindet nicht?**
→ Stimmen Host, Name, User, Passwort? Charset `utf8mb4` verwenden.

**Kontaktmails kommen nicht an?**
→ SMTP/Server richtig konfiguriert (SPF/DKIM/DMARC)?

**Ich habe die ENV umbenannt. Was muss ich ändern?**
→ In `config.php` (und evtl. `bootstrap.php`/`db.php`) den Dateinamen/Pfad anpassen; suche nach `koralp.env`.

---

## Lizenz

Dieses Projekt steht unter **CC BY-NC 4.0** (Namensnennung – Nicht kommerziell).
**Kommerzielle Nutzung ist nicht erlaubt.**

* Menschenlesbare Zusammenfassung (DE): [https://creativecommons.org/licenses/by-nc/4.0/deed.de](https://creativecommons.org/licenses/by-nc/4.0/deed.de)
* Rechtscode (EN): [https://creativecommons.org/licenses/by-nc/4.0/legalcode](https://creativecommons.org/licenses/by-nc/4.0/legalcode)

Eine ausführliche Lizenzdatei liegt als **`CC-BY-NC-4.0.md`** bei.
