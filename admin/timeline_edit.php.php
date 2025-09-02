<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$entry = null;
$page_title = 'Neuen Timeline-Eintrag erstellen';

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM timeline WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $entry = $stmt->fetch();
    if ($entry) {
        $page_title = 'Timeline-Eintrag bearbeiten';
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_token_validate($_POST['csrf_token'] ?? '')) {
        $error = 'Ungültige Anfrage. Bitte versuchen Sie es erneut.';
    } else {
        $date_range = trim($_POST['date_range'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sort_order = (int)($_POST['sort_order'] ?? 0);

        $params = [':date_range' => $date_range, ':title' => $title, ':description' => $description, ':sort_order' => $sort_order];
        if ($id) {
            $sql = "UPDATE timeline SET date_range = :date_range, title = :title, description = :description, sort_order = :sort_order WHERE id = :id";
            $params[':id'] = $id;
        } else {
            $sql = "INSERT INTO timeline (date_range, title, description, sort_order) VALUES (:date_range, :title, :description, :sort_order)";
        }
        $pdo->prepare($sql)->execute($params);
        header('Location: timeline.php?msg=saved');
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
?>
<div class="flex-grow flex items-center justify-center">
    <section class="panel p-6 w-full max-w-2xl">
      <h2 class="text-xl font-semibold"><?= h($page_title) ?></h2>
      <?php if($error): ?><p class="mt-2 text-sm text-rose-300"><?= h($error) ?></p><?php endif; ?>
      <form method="post" class="mt-4 space-y-4">
        <?= csrf_token_field() ?>
        <div class="grid grid-cols-1 md:grid-cols-[1fr,120px] gap-4">
            <div>
                <label for="date_range" class="label">Zeitraum</label>
                <input id="date_range" name="date_range" type="text" value="<?= h($entry['date_range'] ?? '') ?>" class="input-field" placeholder="z.B. 2023–heute">
            </div>
            <div>
                <label for="sort_order" class="label">Sortierung</label>
                <input id="sort_order" name="sort_order" type="number" value="<?= h($entry['sort_order'] ?? 0) ?>" class="input-field">
            </div>
        </div>
        <div>
            <label for="title" class="label">Titel</label>
            <input id="title" name="title" type="text" value="<?= h($entry['title'] ?? '') ?>" class="input-field" placeholder="z.B. Firma · Position">
        </div>
        <div>
            <label for="description" class="label">Beschreibung</label>
            <textarea id="description" name="description" class="input-field min-h-[120px]"><?= h($entry['description'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[color:rgb(10,132,255)] text-white shadow-glow">Speichern</button>
      </form>
    </section>
</div>
<style>.input-field{background-color:rgba(39,39,42,.6);border:1px solid #3f3f46;border-radius:8px;padding:8px 12px;width:100%}.label{display:block;font-size:14px;font-weight:500;color:#d4d4d8;margin-bottom:4px}</style>
<?php include __DIR__ . '/../includes/footer.php'; ?>
