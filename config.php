<?php

/**
 * Lädt Umgebungsvariablen aus einer .env-Datei.
 * Die .env-Datei sollte eine Ebene über dem Verzeichnis dieser config.php liegen.
 * @param string $path Der Pfad zur .env-Datei.
 */
// FEHLERBEHEBUNG: Prüfen, ob die Funktion bereits deklariert wurde, bevor sie definiert wird.
if (!function_exists('load_env')) {
    function load_env($path) {
        if (!file_exists($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Ignoriere Kommentarzeilen
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, '"'); // Entfernt Anführungszeichen am Anfang/Ende

            // Setze die Umgebungsvariable, falls sie nicht bereits existiert
            if (!getenv($name)) {
                putenv(sprintf('%s=%s', $name, $value));
            }
        }
    }
}

// Lade die .env-Datei, die eine Ebene höher liegt als das aktuelle Verzeichnis
load_env(__DIR__ . '/../.env');


// Konfiguration aus Umgebungsvariablen oder mit Standardwerten zurückgeben
return [
    'db' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'name' => getenv('DB_NAME'),
        'user' => getenv('DB_USER'),
        'pass' => getenv('DB_PASS'),
        'charset' => getenv('DB_CHARSET') ?: 'utf8mb4'
    ],
    'site' => [
        'base_url' => '', // Dies kann bleiben oder auch in die .env-Datei
        'site_name' => '',
        'owner_email' => ''
    ]
];


