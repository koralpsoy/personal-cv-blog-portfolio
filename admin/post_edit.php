<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../htmlpurifier-4.15.0/library/HTMLPurifier.auto.php';
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = null;
$page_title = 'Neuen Beitrag erstellen';

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $post = $stmt->fetch();
    if ($post) {
        $page_title = 'Beitrag bearbeiten';
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // SICHERHEITS-FIX: CSRF-Token validieren
    if (!csrf_token_validate($_POST['csrf_token'] ?? '')) {
        $error = 'Ungültige Anfrage. Bitte versuchen Sie es erneut.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if (empty($title)) {
            $error = 'Der Titel ist ein Pflichtfeld.';
        } else {
            $purifier_config = HTMLPurifier_Config::createDefault();
            $purifier = new HTMLPurifier($purifier_config);
            $clean_content = $purifier->purify($content);

            $params = [
                ':title' => $title,
                ':content' => $clean_content,
            ];

            if ($id) {
                $sql = "UPDATE posts SET title = :title, content = :content WHERE id = :id";
                $params[':id'] = $id;
                $pdo->prepare($sql)->execute($params);
                header('Location: posts.php?msg=updated');
                exit;
            } else {
                $sql = "INSERT INTO posts (title, content, created_at) VALUES (:title, :content, NOW())";
                $pdo->prepare($sql)->execute($params);
                $new_id = $pdo->lastInsertId();
                header('Location: post_edit.php?id=' . $new_id . '&msg=created');
                exit;
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<style>
  .ck-editor__editable_inline { min-height: 400px; background-color: #1c1c1e !important; color: #e6e7eb !important; border-color: #3a3a3c !important; }
  .ck.ck-toolbar { background-color: #2c2c2e !important; border-color: #3a3a3c !important; }
  .ck.ck-button, .ck.ck-button:hover, .ck.ck-dropdown__button { color: #e6e7eb !important; }
  .ck.ck-button:hover, .ck.ck-dropdown__button:hover { background-color: #3a3a3c !important; }
</style>

<section class="panel p-6 max-w-4xl mx-auto">
  <h2 class="text-xl font-semibold"><?= h($page_title) ?></h2>
  <?php if($error): ?><p class="mt-2 text-sm text-rose-300"><?= h($error) ?></p><?php endif; ?>
  <?php if(isset($_GET['msg']) && $_GET['msg'] === 'created'): ?><p class="mt-2 text-sm text-emerald-300">Beitrag erfolgreich erstellt.</p><?php endif; ?>
  
  <form method="post" class="mt-4 space-y-4">
    <!-- SICHERHEITS-FIX: CSRF-Token-Feld hinzufügen -->
    <?= csrf_token_field() ?>
    <div>
      <label for="title" class="block text-sm font-medium text-zinc-300 mb-1">Titel</label>
      <input id="title" class="w-full px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" type="text" name="title" value="<?= h($post['title'] ?? '') ?>" required>
    </div>
    <div>
      <label for="content-editor" class="block text-sm font-medium text-zinc-300 mb-1">Inhalt</label>
      <textarea id="content-editor" name="content"><?= h($post['content'] ?? '') ?></textarea>
    </div>
    <button class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[color:rgb(10,132,255)] text-white shadow-glow" type="submit">Speichern</button>
  </form>
</section>

<script>
    ClassicEditor.create( document.querySelector( '#content-editor' ) ).catch( error => console.error( error ) );
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
