<?php
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$skill = null;
$page_title = 'Neuen Skill hinzufügen';

// Fetch categories for the dropdown
$categories = $pdo->query("SELECT id, name FROM skill_categories ORDER BY sort_order ASC, name ASC")->fetchAll();

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM skills WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $skill = $stmt->fetch();
    if ($skill) {
        $page_title = 'Skill bearbeiten';
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_token_validate($_POST['csrf_token'] ?? '')) {
        $error = 'Ungültige Anfrage. Bitte versuchen Sie es erneut.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $percentage = (int)($_POST['percentage'] ?? 0);
        $sort_order = (int)($_POST['sort_order'] ?? 0);

        if (empty($name) || $percentage <= 0 || $percentage > 100 || empty($category_id)) {
            $error = 'Bitte einen gültigen Namen, eine Kategorie und eine Bewertung zwischen 1 und 100 angeben.';
        } else {
            $params = [
                ':name' => $name,
                ':category_id' => $category_id,
                ':percentage' => $percentage,
                ':sort_order' => $sort_order,
            ];

            if ($id) {
                $sql = "UPDATE skills SET name = :name, category_id = :category_id, percentage = :percentage, sort_order = :sort_order WHERE id = :id";
                $params[':id'] = $id;
            } else {
                $sql = "INSERT INTO skills (name, category_id, percentage, sort_order) VALUES (:name, :category_id, :percentage, :sort_order)";
            }
            $pdo->prepare($sql)->execute($params);
            header('Location: skills.php?msg=saved');
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
        <?= csrf_token_field() ?>
        <div>
          <label for="name" class="block text-sm font-medium text-zinc-300 mb-1">Skill-Name</label>
          <input id="name" class="w-full px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" type="text" name="name" value="<?= h($skill['name'] ?? '') ?>" required>
        </div>
        <div>
          <label for="category_id" class="block text-sm font-medium text-zinc-300 mb-1">Kategorie</label>
          <select id="category_id" name="category_id" class="w-full px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" required>
            <option value="">Bitte wählen...</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int)$category['id'] ?>" <?= (isset($skill['category_id']) && $skill['category_id'] == $category['id']) ? 'selected' : '' ?>>
                    <?= h($category['name']) ?>
                </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="percentage" class="block text-sm font-medium text-zinc-300 mb-1">Bewertung (1-100)</label>
          <input id="percentage" class="w-full px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" type="number" name="percentage" value="<?= h($skill['percentage'] ?? 75) ?>" min="1" max="100" required>
        </div>
        <div>
          <label for="sort_order" class="block text-sm font-medium text-zinc-300 mb-1">Sortierreihenfolge (innerhalb der Kategorie)</label>
          <input id="sort_order" class="w-full px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" type="number" name="sort_order" value="<?= h($skill['sort_order'] ?? 0) ?>">
        </div>
        <button class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[color:rgb(10,132,255)] text-white shadow-glow" type="submit">Speichern</button>
      </form>
    </section>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
