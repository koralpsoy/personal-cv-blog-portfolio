<?php
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$item = null;
$page_title = 'Neues Testimonial';

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM testimonials WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $item = $stmt->fetch();
    if ($item) { $page_title = 'Testimonial bearbeiten'; }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_token_validate($_POST['csrf_token'] ?? '')) {
        $error = 'Ungültige Anfrage. Bitte versuchen Sie es erneut.';
    } else {
        $author_name = trim($_POST['author_name'] ?? '');
        $author_role = trim($_POST['author_role'] ?? '');
        $text = trim($_POST['text'] ?? '');
        $sort_order = (int)($_POST['sort_order'] ?? 0);

        if (empty($author_name) || empty($text)) {
            $error = 'Name des Autors und der Text sind Pflichtfelder.';
        } else {
            $params = [
                ':author_name' => $author_name,
                ':author_role' => $author_role,
                ':text' => $text,
                ':sort_order' => $sort_order
            ];
            if ($id) {
                $sql = "UPDATE testimonials SET author_name = :author_name, author_role = :author_role, text = :text, sort_order = :sort_order WHERE id = :id";
                $params[':id'] = $id;
            } else {
                $sql = "INSERT INTO testimonials (author_name, author_role, text, sort_order) VALUES (:author_name, :author_role, :text, :sort_order)";
            }
            $pdo->prepare($sql)->execute($params);
            header('Location: testimonials.php?msg=saved');
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
          <label for="author_name" class="block text-sm font-medium text-zinc-300 mb-1">Name des Autors</label>
          <input id="author_name" class="w-full px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" type="text" name="author_name" value="<?= h($item['author_name'] ?? '') ?>" required>
        </div>
         <div>
          <label for="author_role" class="block text-sm font-medium text-zinc-300 mb-1">Rolle/Firma des Autors</label>
          <input id="author_role" class="w-full px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" type="text" name="author_role" value="<?= h($item['author_role'] ?? '') ?>">
        </div>
        <div>
          <label for="text" class="block text-sm font-medium text-zinc-300 mb-1">Text</label>
          <textarea id="text" class="w-full min-h-[120px] px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" name="text" required><?= h($item['text'] ?? '') ?></textarea>
        </div>
        <div>
          <label for="sort_order" class="block text-sm font-medium text-zinc-300 mb-1">Sortierreihenfolge</label>
          <input id="sort_order" class="w-full px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" type="number" name="sort_order" value="<?= h($item['sort_order'] ?? 0) ?>">
        </div>
        <button class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[color:rgb(10,132,255)] text-white shadow-glow" type="submit">Speichern</button>
      </form>
    </section>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
