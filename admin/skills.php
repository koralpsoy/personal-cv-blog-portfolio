<?php
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$error = '';
$success = '';

// Handle POST requests for categories and skills
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_token_validate($_POST['csrf_token'] ?? '')) {
    // Handle category updates/creations
    if (isset($_POST['save_categories'])) {
        $categories = $_POST['categories'] ?? [];
        
        $update_stmt = $pdo->prepare("UPDATE skill_categories SET name = :name, sort_order = :sort_order WHERE id = :id");
        $insert_stmt = $pdo->prepare("INSERT INTO skill_categories (name, sort_order) VALUES (:name, :sort_order)");

        foreach ($categories as $cat) {
            $id = $cat['id'] ?? 0;
            $name = trim($cat['name'] ?? '');
            $sort_order = (int)($cat['sort_order'] ?? 0);

            if (!empty($name)) {
                if ($id) {
                    $update_stmt->execute([':name' => $name, ':sort_order' => $sort_order, ':id' => $id]);
                } else {
                    $insert_stmt->execute([':name' => $name, ':sort_order' => $sort_order]);
                }
            }
        }
        $success = 'Kategorien erfolgreich gespeichert.';
    }

    // Handle category deletion
    if (isset($_POST['delete_category'])) {
        $category_id = (int)$_POST['category_id'];
        
        // Check if any skills are using this category
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM skills WHERE category_id = :id");
        $check_stmt->execute([':id' => $category_id]);
        $skill_count = $check_stmt->fetchColumn();

        if ($skill_count > 0) {
            $error = 'Kategorie kann nicht gelöscht werden, da ihr noch Skills zugeordnet sind.';
        } else {
            $delete_stmt = $pdo->prepare("DELETE FROM skill_categories WHERE id = :id");
            $delete_stmt->execute([':id' => $category_id]);
            $success = 'Kategorie wurde gelöscht.';
        }
    }
}


// Fetch all data
$categories = $pdo->query("SELECT * FROM skill_categories ORDER BY sort_order ASC, name ASC")->fetchAll();
$skills_stmt = $pdo->query("
    SELECT s.*, sc.name as category_name 
    FROM skills s 
    LEFT JOIN skill_categories sc ON s.category_id = sc.id 
    ORDER BY sc.sort_order ASC, sc.name ASC, s.sort_order ASC, s.percentage DESC, s.name ASC
");
$skills = $skills_stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto grid lg:grid-cols-[1fr,400px] gap-8">
  
  <!-- Main content: Skills list -->
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-semibold text-white">Skills verwalten</h1>
      <a href="skill_edit.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[color:rgb(10,132,255)] text-white shadow-glow text-sm">
        Neuen Skill hinzufügen
      </a>
    </div>

    <div class="panel p-6">
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead class="text-xs text-zinc-400 uppercase bg-zinc-900/60">
            <tr>
              <th scope="col" class="px-6 py-3">Skill-Name</th>
              <th scope="col" class="px-6 py-3">Kategorie</th>
              <th scope="col" class="px-6 py-3">Bewertung (%)</th>
              <th scope="col" class="px-6 py-3">Sortierung</th>
              <th scope="col" class="px-6 py-3 text-right">Aktionen</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($skills as $skill): ?>
            <tr class="border-b border-zinc-800 hover:bg-zinc-900/40">
              <td class="px-6 py-4 font-medium text-white"><?= h($skill['name']) ?></td>
              <td class="px-6 py-4 text-zinc-400"><?= h($skill['category_name'] ?? 'Ohne Kategorie') ?></td>
              <td class="px-6 py-4 text-zinc-300"><?= h($skill['percentage']) ?>%</td>
              <td class="px-6 py-4"><?= h($skill['sort_order']) ?></td>
              <td class="px-6 py-4 text-right">
                <a href="skill_edit.php?id=<?= (int)$skill['id'] ?>" class="font-medium text-blue-500 hover:underline mr-4">Bearbeiten</a>
                <form method="post" action="skill_delete.php" onsubmit="return confirm('Bist du sicher, dass du diesen Skill löschen möchtest?');" style="display: inline;">
                    <input type="hidden" name="id" value="<?= (int)$skill['id'] ?>">
                    <?= csrf_token_field() ?>
                    <button type="submit" class="font-medium text-rose-500 hover:underline bg-transparent border-none p-0 cursor-pointer">Löschen</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Sidebar: Category Management -->
  <aside class="space-y-6 lg:sticky top-24 self-start">
    <h2 class="text-xl font-semibold text-white">Kategorien verwalten</h2>
    
    <?php if($error): ?><p class="mt-2 text-sm text-rose-300 p-3 bg-rose-900/50 rounded-lg"><?= h($error) ?></p><?php endif; ?>
    <?php if($success): ?><p class="mt-2 text-sm text-emerald-300 p-3 bg-emerald-900/50 rounded-lg"><?= h($success) ?></p><?php endif; ?>

    <form method="post" class="panel p-6 space-y-4">
        <?= csrf_token_field() ?>
        <div id="category-list" class="space-y-3">
            <div class="grid grid-cols-[1fr,80px] gap-2 text-xs text-zinc-400 px-1 pb-1">
                <label>Kategoriename</label>
                <label class="text-center">Sort.</label>
            </div>
            <?php foreach ($categories as $category): ?>
            <div class="grid grid-cols-[1fr,80px,40px] gap-2 items-center category-item">
                <input type="hidden" name="categories[<?= $category['id'] ?>][id]" value="<?= $category['id'] ?>">
                <input type="text" name="categories[<?= $category['id'] ?>][name]" value="<?= h($category['name']) ?>" class="input-field">
                <input type="number" name="categories[<?= $category['id'] ?>][sort_order]" value="<?= h($category['sort_order']) ?>" class="input-field text-center">
                <button type="submit" name="delete_category" value="1" onclick="return confirm('Sicher, dass du diese Kategorie löschen willst?');" formaction="" formmethod="post" class="text-rose-500 hover:text-rose-400 h-full w-full flex items-center justify-center rounded-lg bg-zinc-800/60 hover:bg-zinc-700/60">
                    <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>
            <?php endforeach; ?>
        </div>
        
        <button type="button" id="add-category" class="text-sm text-blue-400 hover:text-blue-300">+ Neue Kategorie hinzufügen</button>
        
        <hr class="border-zinc-800">
        
        <button type="submit" name="save_categories" value="1" class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-[color:rgb(10,132,255)] text-white shadow-glow w-full">Kategorien speichern</button>
    </form>
  </aside>
</div>

<style>
.input-field{background-color:rgba(39,39,42,.6);border:1px solid #3f3f46;border-radius:8px;padding:8px 12px;width:100%}
</style>

<script>
document.getElementById('add-category').addEventListener('click', function() {
    const list = document.getElementById('category-list');
    const timestamp = Date.now();
    const newItem = document.createElement('div');
    newItem.className = 'grid grid-cols-[1fr,80px] gap-2 items-center category-item';
    newItem.innerHTML = `
        <input type="text" name="categories[${timestamp}][name]" placeholder="Neuer Name" class="input-field">
        <input type="number" name="categories[${timestamp}][sort_order]" value="0" class="input-field text-center">
    `;
    list.appendChild(newItem);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
