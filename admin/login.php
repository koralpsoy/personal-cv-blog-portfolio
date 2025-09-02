<?php
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/db.php';
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE username = :u");
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        // WICHTIG: Session-ID nach erfolgreichem Login erneuern
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        
        // Weiterleitung zum Admin Dashboard
        header('Location: index.php'); 
        exit;
    } else {
        $error = 'Login fehlgeschlagen. Bitte überprüfe deine Eingaben.';
    }
}

include __DIR__ . '/../includes/header.php';
?>
<section class="panel p-6 max-w-xl mx-auto">
  <h2 class="text-xl font-semibold">Login</h2>
  <?php if($error): ?><p class="mt-2 text-sm text-rose-300"><?= h($error) ?></p><?php endif; ?>
  <form method="post" class="mt-4 space-y-3">
    <input class="w-full px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" type="text" name="username" placeholder="Benutzername" required>
    <input class="w-full px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" type="password" name="password" placeholder="Passwort" required>
    <button class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-[color:rgb(10,132,255)] text-white shadow-glow" type="submit">Einloggen</button>
  </form>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>

