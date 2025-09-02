<?php
/**
 * Core bootstrap file.
 */

// FIX: Setzt den Header für die Zeichenkodierung.
// Dies ist der zuverlässigste Weg, um Anzeigefehler bei Zeichen zu vermeiden.
header('Content-Type: text/html; charset=utf-8');

// Session Management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kerndateien laden & globale Variablen erstellen
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$config = require __DIR__ . '/../config.php';
$base_url = base_url();
