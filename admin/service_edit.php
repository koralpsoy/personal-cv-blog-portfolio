<?php
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$service = null;
$page_title = 'Neuen Service hinzufügen';

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $service = $stmt->fetch();
    if ($service) {
        $page_title = 'Service bearbeiten';
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // SICHERHEITS-FIX: CSRF-Token validieren
    if (!csrf_token_validate($_POST['csrf_token'] ?? '')) {
        $error = 'Ungültige Anfrage. Bitte versuchen Sie es erneut.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sort_order = (int)($_POST['sort_order'] ?? 0);

        if (empty($title) || empty($description)) {
            $error = 'Titel und Beschreibung sind Pflichtfelder.';
        } else {
            $params = [':title' => $title, ':description' => $description, ':sort_order' => $sort_order];
            if ($id) {
                $sql = "UPDATE services SET title = :title, description = :description, sort_order = :sort_order WHERE id = :id";
                $params[':id'] = $id;
            } else {
                $sql = "INSERT INTO services (title, description, sort_order) VALUES (:title, :description, :sort_order)";
            }
            $pdo->prepare($sql)->execute($params);
            header('Location: services.php?msg=saved');
            exit;
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="flex-grow flex items-center justify-center">
    <section class="panel p-6 w-full max-w-lg">
      <h2 class="text-xl font-semibold"><?= h($page_title) ?></h2>
      <?php if($error): ?><p class="mt-2 text-sm text-rose-300"><?= h($error) ?></p><?php endif; ?>
      
      <form method="post" class="mt-4 space-y-4">
        <!-- SICHERHEITS-FIX: CSRF-Token-Feld hinzufügen -->
        <?= csrf_token_field() ?>
        <div>
          <label for="title" class="block text-sm font-medium text-zinc-300 mb-1">Titel</label>
          <input id="title" class="w-full px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" type="text" name="title" value="<?= h($service['title'] ?? '') ?>" required>
        </div>
        <div>
          <label for="description" class="block text-sm font-medium text-zinc-300 mb-1">Beschreibung</label>
          <textarea id="description" class="w-full min-h-[100px] px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" name="description" required><?= h($service['description'] ?? '') ?></textarea>
        </div>
        <div>
          <label for="sort_order" class="block text-sm font-medium text-zinc-300 mb-1">Sortierreihenfolge</label>
          <input id="sort_order" class="w-full px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" type="number" name="sort_order" value="<?= h($service['sort_order'] ?? 0) ?>">
        </div>
        <button class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[color:rgb(10,132,255)] text-white shadow-glow" type="submit">Speichern</button>
      </form>
    </section>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
