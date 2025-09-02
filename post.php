<?php
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: blog.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = :id");
$stmt->execute([':id' => $id]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: blog.php');
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<section class="panel p-6 md:p-8 max-w-4xl mx-auto">
    <h1 class="text-3xl md:text-4xl font-bold tracking-tight"><?= h($post['title']) ?></h1>
    <p class="text-zinc-400 mt-2">Veröffentlicht am <?= date('d. F Y', strtotime($post['created_at'])) ?></p>

    <div class="mt-8 prose prose-invert max-w-none">
        <?php
          // WICHTIG: Gib den Inhalt ohne Escaping aus, da er HTML vom Editor enthält.
          // Die Bereinigung sollte idealerweise beim Speichern erfolgen, aber für die Anzeige ist das so korrekt.
        ?>
        <?= $post['content'] ?>
    </div>

    <div class="mt-12 border-t border-zinc-800 pt-6">
        <a href="blog.php" class="text-accent hover:underline">&larr; Zurück zur Blog-Übersicht</a>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
