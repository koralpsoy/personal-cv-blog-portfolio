<?php
/**
 * Database connection using PDO
 */
$config = require __DIR__ . '/../config.php';

// Robustheits-Check: Sicherstellen, dass die DB-Zugangsdaten geladen wurden.
if (empty($config['db']['name']) || empty($config['db']['user'])) {
    http_response_code(500);
    // Detaillierte Fehlermeldung, die Ihnen hilft, das Problem zu finden.
    die('FEHLER: Datenbank-Zugangsdaten konnten nicht geladen werden. Bitte überprüfen Sie, ob die `.env`-Datei im korrekten Verzeichnis (eine Ebene über dem Ordner) liegt, lesbar ist und die Variablen DB_NAME, DB_USER etc. korrekt enthält.');
}

$dsn = 'mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['name'] . ';charset=' . $config['db']['charset'];

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], $options);
} catch (PDOException $e) {
    http_response_code(500);
    // Die Ausgabe der e->getMessage() gibt den genauen Grund des Fehlers an (z.B. falsches Passwort)
    die('Datenbank-Verbindung fehlgeschlagen: ' . htmlspecialchars($e->getMessage()));
}

