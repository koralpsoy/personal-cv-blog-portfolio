<?php
// TEMPORÄRES Setup-Skript: Ruft es EINMAL im Browser auf, um einen Admin anzulegen.
// Danach diese Datei UNBEDINGT LÖSCHEN!
require __DIR__ . '/../includes/db.php';
$username = (isset($_POST['username']) ? $_POST['username'] : '');
$password = (isset($_POST['password']) ? $_POST['password'] : '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$username || !$password) {
        echo 'Bitte Benutzername/Passwort angeben.';
        exit;
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, created_at) VALUES (:u, :p, NOW())');
    $stmt->execute([':u' => $username, ':p' => $hash]);
    echo 'Admin-Benutzer angelegt. Bitte diese Datei (create_admin.php) jetzt vom Server löschen.';
    exit;
}
?>
<!doctype html><html lang="de"><meta charset="utf-8">
<title>Admin anlegen</title>
<form method="post" style="max-width:420px;margin:40px auto;font-family:system-ui">
  <h1>Admin anlegen</h1>
  <p><input name="username" placeholder="Benutzername" style="width:100%;padding:10px;margin:6px 0"></p>
  <p><input type="password" name="password" placeholder="Passwort" style="width:100%;padding:10px;margin:6px 0"></p>
  <button style="padding:10px 14px">Anlegen</button>
</form>
