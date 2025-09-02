<?php
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$project = null;
$page_title = 'Neues Projekt hinzufügen';

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $project = $stmt->fetch();
    if ($project) {
        $page_title = 'Projekt bearbeiten';
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_token_validate($_POST['csrf_token'] ?? '')) {
        $error = 'Ungültige Anfrage. Bitte versuchen Sie es erneut.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $project_url = trim($_POST['project_url'] ?? '');
        $tags = trim($_POST['tags'] ?? '');
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $image_path = $project['image_url'] ?? '';

        if (empty($title)) {
            $error = 'Der Titel ist ein Pflichtfeld.';
        } else {
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../assets/projects/';
                if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }
                $filename = uniqid() . '-' . basename($_FILES['image']['name']);
                $target_file = $upload_dir . $filename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    if (!empty($image_path) && file_exists(__DIR__ . '/../' . $image_path)) {
                        unlink(__DIR__ . '/../' . $image_path);
                    }
                    $image_path = 'assets/projects/' . $filename;
                } else {
                    $error = 'Fehler beim Hochladen des Bildes.';
                }
            }

            if (empty($error)) {
                $params = [
                    ':title' => $title,
                    ':description' => $description,
                    ':project_url' => $project_url,
                    ':tags' => $tags,
                    ':sort_order' => $sort_order,
                    ':image_url' => $image_path
                ];

                if ($id) {
                    $sql = "UPDATE projects SET title = :title, description = :description, project_url = :project_url, tags = :tags, sort_order = :sort_order, image_url = :image_url WHERE id = :id";
                    $params[':id'] = $id;
                } else {
                    $sql = "INSERT INTO projects (title, description, project_url, tags, sort_order, image_url, created_at) VALUES (:title, :description, :project_url, :tags, :sort_order, :image_url, NOW())";
                }
                $pdo->prepare($sql)->execute($params);
                header('Location: projects.php?msg=saved');
                exit;
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<section class="panel p-6 max-w-3xl mx-auto">
  <h2 class="text-xl font-semibold"><?= h($page_title) ?></h2>
  <?php if($error): ?><p class="mt-2 text-sm text-rose-300"><?= h($error) ?></p><?php endif; ?>
  
  <form method="post" class="mt-4 space-y-4" enctype="multipart/form-data">
    <?= csrf_token_field() ?>
    <div>
      <label for="title" class="block text-sm font-medium text-zinc-300 mb-1">Titel</label>
      <input id="title" class="w-full px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" type="text" name="title" value="<?= h($project['title'] ?? '') ?>" required>
    </div>
    <div>
      <label for="description" class="block text-sm font-medium text-zinc-300 mb-1">Beschreibung</label>
      <textarea id="description" class="w-full min-h-[120px] px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" name="description"><?= h($project['description'] ?? '') ?></textarea>
    </div>
    <div>
      <label for="project_url" class="block text-sm font-medium text-zinc-300 mb-1">Projekt-URL</label>
      <input id="project_url" class="w-full px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" type="url" name="project_url" value="<?= h($project['project_url'] ?? '') ?>" placeholder="https://example.com">
    </div>
    <div>
      <label for="tags" class="block text-sm font-medium text-zinc-300 mb-1">Tags (Komma-getrennt)</label>
      <input id="tags" class="w-full px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" type="text" name="tags" value="<?= h($project['tags'] ?? '') ?>" placeholder="PHP,React,Figma">
    </div>
    <div>
      <label for="sort_order" class="block text-sm font-medium text-zinc-300 mb-1">Sortierreihenfolge</label>
      <input id="sort_order" class="w-full px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" type="number" name="sort_order" value="<?= h($project['sort_order'] ?? 0) ?>">
    </div>
    <div>
      <label for="image" class="block text-sm font-medium text-zinc-300 mb-1">Projektbild</label>
      <input id="image" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-zinc-800 file:text-zinc-300 hover:file:bg-zinc-700" type="file" name="image">
      <?php if (!empty($project['image_url'])): ?>
        <p class="text-xs text-zinc-400 mt-2">Aktuelles Bild: <img src="../<?= h($project['image_url']) ?>" alt="Vorschau" class="inline-block h-10 w-auto rounded-md ml-2"></p>
      <?php endif; ?>
    </div>
    <button class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[color:rgb(10,132,255)] text-white shadow-glow" type="submit">Speichern</button>
  </form>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
