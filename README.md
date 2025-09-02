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
   Erstelle eine MySQL/MariaDB-Datenbank (z. B. in **phpMyAdmin**).
   Importiere `schema.sql` (Datei auswählen → Import).

4. **`koralp.env` anlegen (wichtiger Schritt!)**
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

5. **Konfiguration prüfen**
   Die Datei **`config.php`** lädt die ENV-Werte und enthält die Logik für die Verbindung.

   * **`config.sample.php` wird nicht mehr benutzt → bitte löschen.**
   * Wenn du **`koralp.env`** umbenennst (z. B. in `.env`) **oder woanders ablegst**, musst du in der **`config.php`** (und ggf. `bootstrap.php`/`db.php`) den **Pfad/Namen anpassen**.
   * Tipp: Suche im Code nach `koralp.env` oder nach `DB_HOST`/`getenv`/`parse_ini_file`.

   Beispiel für einen Pfad **eine Ebene höher** (nur als Orientierung):

   ```php
   $envFile = dirname(__DIR__) . '/koralp.env';
   // Wenn du sie umbenennst/verschiebst, hier den Pfad ändern
   ```

6. **Admin-Benutzer anlegen (einmalig)**
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
