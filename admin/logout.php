<?php
require __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (csrf_token_validate($_POST['csrf_token'] ?? '')) {
        // Session-Variablen löschen
        $_SESSION = [];

        // Session-Cookie löschen
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Session zerstören
        session_destroy();
    }
}

redirect('../index.php');
