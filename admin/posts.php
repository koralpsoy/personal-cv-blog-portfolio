<?php
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$stmt = $pdo->query("SELECT id, title, created_at FROM posts ORDER BY created_at DESC");
$posts = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-white">Blogbeiträge verwalten</h1>
    <a href="post_edit.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[color:rgb(10,132,255)] text-white shadow-glow text-sm">
      Neuen Beitrag hinzufügen
    </a>
  </div>

  <div class="panel p-6">
    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left">
        <thead class="text-xs text-zinc-400 uppercase bg-zinc-900/60">
          <tr>
            <th scope="col" class="px-6 py-3">Titel</th>
            <th scope="col" class="px-6 py-3">Erstellt am</th>
            <th scope="col" class="px-6 py-3 text-right">Aktionen</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($posts as $post): ?>
          <tr class="border-b border-zinc-800 hover:bg-zinc-900/40">
            <td class="px-6 py-4 font-medium text-white"><?= h($post['title']) ?></td>
            <td class="px-6 py-4 text-zinc-300"><?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></td>
            <td class="px-6 py-4 text-right">
              <a href="post_edit.php?id=<?= (int)$post['id'] ?>" class="font-medium text-blue-500 hover:underline mr-4">Bearbeiten</a>
              <form method="post" action="post_delete.php" onsubmit="return confirm('Bist du sicher, dass du diesen Beitrag löschen möchtest?');" style="display: inline;">
                  <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
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

<?php include __DIR__ . '/../includes/footer.php'; ?>
