<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$userStmt = $pdo->prepare("SELECT username FROM users WHERE id = :id");
$userStmt->execute([':id' => $_SESSION['user_id']]);
$user = $userStmt->fetch();

include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto">
  <div class="panel p-6 mb-6 flex flex-wrap justify-between items-center gap-4">
    <div>
        <h1 class="text-2xl font-semibold text-white">Admin Dashboard</h1>
        <p class="text-zinc-400">Willkommen zurück, <?= h($user['username'] ?? 'Admin') ?>!</p>
    </div>
    <a href="profile_edit.php" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-sm font-semibold">
      Profil & Startseite bearbeiten
    </a>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="panel p-5 space-y-3"><h2 class="text-xl font-semibold">Lebenslauf</h2><p class="text-zinc-300 text-sm">Verwalte deine Timeline.</p><div class="flex flex-wrap gap-2"><a href="timeline_edit.php" class="btn-main">Neuer Eintrag</a><a href="timeline.php" class="btn-sec">Verwalten</a></div></div>
    <div class="panel p-5 space-y-3"><h2 class="text-xl font-semibold">Portfolio</h2><p class="text-zinc-300 text-sm">Verwalte deine Projekte.</p><div class="flex flex-wrap gap-2"><a href="project_edit.php" class="btn-main">Neues Projekt</a><a href="projects.php" class="btn-sec">Verwalten</a></div></div>
    <div class="panel p-5 space-y-3"><h2 class="text-xl font-semibold">Skills</h2><p class="text-zinc-300 text-sm">Bearbeite deine Fähigkeiten.</p><div class="flex flex-wrap gap-2"><a href="skill_edit.php" class="btn-main">Neuer Skill</a><a href="skills.php" class="btn-sec">Verwalten</a></div></div>
    <div class="panel p-5 space-y-3"><h2 class="text-xl font-semibold">Services</h2><p class="text-zinc-300 text-sm">Liste deine Services auf.</p><div class="flex flex-wrap gap-2"><a href="service_edit.php" class="btn-main">Neuer Service</a><a href="services.php" class="btn-sec">Verwalten</a></div></div>
    <div class="panel p-5 space-y-3"><h2 class="text-xl font-semibold">Testimonials</h2><p class="text-zinc-300 text-sm">Verwalte Kundenstimmen.</p><div class="flex flex-wrap gap-2"><a href="testimonial_edit.php" class="btn-main">Neues Testimonial</a><a href="testimonials.php" class="btn-sec">Verwalten</a></div></div>
    <div class="panel p-5 space-y-3"><h2 class="text-xl font-semibold">Blog</h2><p class="text-zinc-300 text-sm">Verwalte deine Beiträge.</p><div class="flex flex-wrap gap-2"><a href="post_edit.php" class="btn-main">Neuer Beitrag</a><a href="posts.php" class="btn-sec">Verwalten</a></div></div>
  </div>
</div>
<style>.btn-main{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:8px;background-color:#27272a;font-size:14px;}.btn-main:hover{background-color:#3f3f46;}.btn-sec{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:8px;background-color:rgba(39,39,42,0.6);font-size:14px;}.btn-sec:hover{background-color:#27272a;}</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
