<?php
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/db.php';
include __DIR__ . '/includes/header.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($search) {
    $stmt = $pdo->prepare("SELECT id, title, content, created_at FROM posts WHERE title LIKE :q OR content LIKE :q ORDER BY created_at DESC");
    $stmt->execute([':q' => '%' . $search . '%']);
} else {
    $stmt = $pdo->query("SELECT id, title, content, created_at FROM posts ORDER BY created_at DESC");
}
$posts = $stmt->fetchAll();
?>
<section class="space-y-4">
  <div class="panel p-6">
    <h2 class="text-xl font-semibold">Blog</h2>
    <form method="get" class="mt-3">
      <input class="w-full px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" type="text" name="q" placeholder="Suche..." value="<?= h($search) ?>">
    </form>
  </div>
  <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach ($posts as $post): ?>
      <a class="panel p-4 hover:bg-zinc-900/60 transition" href="post.php?id=<?= (int)$post['id'] ?>">
        <div class="text-sm text-zinc-400"><?= date('d.m.Y', strtotime($post['created_at'])) ?></div>
        <h3 class="font-medium mt-1"><?= h($post['title']) ?></h3>
        <p class="text-zinc-300 mt-1"><?= h(excerpt($post['content'])) ?></p>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
