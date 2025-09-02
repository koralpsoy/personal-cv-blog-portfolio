<?php
// Core bootstrap file. This handles sessions, DB connection, and functions.
require_once __DIR__ . '/bootstrap.php';

// Check if we are in the admin area by looking at the script path.
$is_admin_area = (strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false);
$is_login_page = (strpos($_SERVER['SCRIPT_NAME'], '/admin/login.php') !== false);

// If we are in the admin area AND it's not the login page, enforce the login check.
if ($is_admin_area && !$is_login_page) {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . $base_url . '/admin/login.php');
        exit;
    }
}

// For the public-facing site, fetch data to determine which nav links to show.
// This ensures navigation is consistent across all public pages (index, blog, post).
if (!$is_admin_area) {
    try {
        // Use unique variable names for navigation to avoid conflicts with page content variables.
        // fetchColumn() is efficient as it only checks for the existence of at least one row.
        $nav_projects_exist = $pdo->query("SELECT 1 FROM projects LIMIT 1")->fetchColumn();
        $nav_skills_exist = $pdo->query("SELECT 1 FROM skills LIMIT 1")->fetchColumn();
        $nav_services_exist = $pdo->query("SELECT 1 FROM services LIMIT 1")->fetchColumn();
        $nav_posts_exist = $pdo->query("SELECT 1 FROM posts LIMIT 1")->fetchColumn();
    } catch (PDOException $e) {
        // In case of a DB error, assume nothing exists to avoid breaking the page.
        $nav_projects_exist = false;
        $nav_skills_exist = false;
        $nav_services_exist = false;
        $nav_posts_exist = false;
    }
}

?><!doctype html>
<html lang="de" class="scroll-smooth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($is_admin_area ? 'Admin Panel' : '') . h($config['site']['site_name'] ?? 'Portfolio') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: { extend: { fontFamily: { sans: ['Inter','ui-sans-serif','system-ui'] }, colors: { night: '#0a0a0a', ink: '#101214', accent: '#0a84ff' }, boxShadow: { glow: '0 0 25px rgba(10,132,255,.25)' } } }
    }
  </script>
  <!-- KORRIGIERT: CSS für GLightbox statt baguetteBox -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
  <link rel="stylesheet" href="<?= $base_url ?>/assets/style.css">
</head>
<body class="bg-<?= $is_admin_area ? 'ink' : 'night' ?> text-zinc-100 selection:bg-[color:rgb(10,132,255)]/20 selection:text-white">
<header class="sticky top-0 z-40 backdrop-blur border-b border-zinc-800 bg-<?= $is_admin_area ? '[#101214cc]' : '[#0a0a0acc]' ?>">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-3">
    
    <!-- Logo always links to the public frontend homepage -->
    <a class="inline-flex items-center gap-2 font-semibold tracking-tight" href="<?= $base_url ?>/" title="Zur öffentlichen Webseite">
      <span class="px-2 py-1 rounded-lg bg-[color:rgb(10,132,255)]/20 border border-[color:rgb(10,132,255)]/30 text-[color:rgb(10,132,255)]">KS</span>
      <span><?= h($is_admin_area ? 'Admin Panel' : ($config['site']['site_name'] ?? 'Portfolio')) ?></span>
    </a>

    <?php if ($is_admin_area): ?>
      <!-- ADMIN NAVIGATION -->
      <nav class="hidden lg:flex items-center gap-1 text-sm">
        <a class="px-3 py-1.5 rounded-lg hover:bg-zinc-900/60" href="<?= $base_url ?>/admin/index.php">Dashboard</a>
        <a class="px-3 py-1.5 rounded-lg hover:bg-zinc-900/60" href="<?= $base_url ?>/admin/profile_edit.php">Profil/Startseite</a>
        <a class="px-3 py-1.5 rounded-lg hover:bg-zinc-900/60" href="<?= $base_url ?>/admin/timeline.php">Lebenslauf</a>
        <a class="px-3 py-1.5 rounded-lg hover:bg-zinc-900/60" href="<?= $base_url ?>/admin/projects.php">Projekte</a>
        <a class="px-3 py-1.5 rounded-lg hover:bg-zinc-900/60" href="<?= $base_url ?>/admin/skills.php">Skills</a>
        <a class="px-3 py-1.5 rounded-lg hover:bg-zinc-900/60" href="<?= $base_url ?>/admin/services.php">Services</a>
        <a class="px-3 py-1.5 rounded-lg hover:bg-zinc-900/60" href="<?= $base_url ?>/admin/testimonials.php">Testimonials</a>
        <a class="px-3 py-1.5 rounded-lg hover:bg-zinc-900/60" href="<?= $base_url ?>/admin/posts.php">Blog</a>
      </nav>
      <div class="flex items-center gap-2">
        <a href="<?= $base_url ?>/" target="_blank" class="hidden sm:inline-block px-3 py-1.5 rounded-lg hover:bg-zinc-900/60 text-sm">Seite ansehen</a>
        <form method="post" action="<?= $base_url ?>/admin/logout.php" class="hidden sm:inline">
            <?= csrf_token_field() ?>
            <button type="submit" class="px-3 py-1.5 rounded-lg hover:bg-zinc-900/60 bg-transparent border-none cursor-pointer text-sm">Logout</button>
        </form>
        <button id="mobile-menu-button" class="lg:hidden p-2 rounded-md text-zinc-300 hover:bg-zinc-800"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg></button>
      </div>
    <?php else: ?>
      <!-- PUBLIC NAVIGATION -->
      <nav class="hidden lg:flex items-center gap-1 text-sm">
        <a class="px-3 py-1.5 rounded-lg hover:bg-zinc-900/60" href="<?= $base_url ?>/">Home</a>
        <?php if (!empty($nav_projects_exist)): ?><a class="px-3 py-1.5 rounded-lg hover:bg-zinc-900/60" href="<?= $base_url ?>/#portfolio">Portfolio</a><?php endif; ?>
        <?php if (!empty($nav_skills_exist)): ?><a class="px-3 py-1.5 rounded-lg hover:bg-zinc-900/60" href="<?= $base_url ?>/#skills">Skills</a><?php endif; ?>
        <?php if (!empty($nav_services_exist)): ?><a class="px-3 py-1.5 rounded-lg hover:bg-zinc-900/60" href="<?= $base_url ?>/#services">Services</a><?php endif; ?>
        <?php if (!empty($nav_posts_exist)): ?><a class="px-3 py-1.5 rounded-lg hover:bg-zinc-900/60" href="<?= $base_url ?>/blog.php">Blog</a><?php endif; ?>
        <a class="px-3 py-1.5 rounded-lg hover:bg-zinc-900/60" href="<?= $base_url ?>/#kontakt">Kontakt</a>
      </nav>
      <div class="hidden lg:flex items-center ml-4">
        <?php if (!empty($_SESSION['user_id'])): ?>
          <a href="<?= $base_url ?>/admin/index.php" class="mr-2 px-3 py-1.5 rounded-lg bg-[color:rgb(10,132,255)] text-white shadow-glow text-sm">Dashboard</a>
        <?php else: ?>
          <a href="<?= $base_url ?>/admin/login.php" class="px-3 py-1.5 rounded-lg hover:bg-zinc-900/60 text-sm">Login</a>
        <?php endif; ?>
      </div>
      <button id="mobile-menu-button" class="lg:hidden p-2 rounded-md text-zinc-300 hover:bg-zinc-800"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg></button>
    <?php endif; ?>
  </div>

  <!-- Unified Mobile Navigation Panel -->
  <div id="mobile-menu" class="hidden lg:hidden bg-ink/95 backdrop-blur-lg absolute top-full left-0 w-full border-t border-zinc-800">
    <nav class="flex flex-col p-4 space-y-2">
      <?php if ($is_admin_area): ?>
        <a class="px-4 py-2 rounded-lg hover:bg-zinc-800" href="<?= $base_url ?>/admin/index.php">Dashboard</a>
        <a class="px-4 py-2 rounded-lg hover:bg-zinc-800" href="<?= $base_url ?>/admin/profile_edit.php">Profil/Startseite</a>
        <a class="px-4 py-2 rounded-lg hover:bg-zinc-800" href="<?= $base_url ?>/admin/timeline.php">Lebenslauf</a>
        <a class="px-4 py-2 rounded-lg hover:bg-zinc-800" href="<?= $base_url ?>/admin/projects.php">Projekte</a>
        <a class="px-4 py-2 rounded-lg hover:bg-zinc-800" href="<?= $base_url ?>/admin/skills.php">Skills</a>
        <a class="px-4 py-2 rounded-lg hover:bg-zinc-800" href="<?= $base_url ?>/admin/services.php">Services</a>
        <a class="px-4 py-2 rounded-lg hover:bg-zinc-800" href="<?= $base_url ?>/admin/testimonials.php">Testimonials</a>
        <a class="px-4 py-2 rounded-lg hover:bg-zinc-800" href="<?= $base_url ?>/admin/posts.php">Blog</a>
        <div class="border-t border-zinc-700 my-2"></div>
        <a href="<?= $base_url ?>/" target="_blank" class="px-4 py-2 rounded-lg hover:bg-zinc-800">Seite ansehen</a>
        <form method="post" action="<?= $base_url ?>/admin/logout.php" class="w-full"><button type="submit" class="w-full text-left px-4 py-2 rounded-lg hover:bg-zinc-800">Logout</button><?= csrf_token_field() ?></form>
      <?php else: ?>
        <a class="px-4 py-2 rounded-lg hover:bg-zinc-800" href="<?= $base_url ?>/">Home</a>
        <?php if (!empty($nav_projects_exist)): ?><a class="px-4 py-2 rounded-lg hover:bg-zinc-800" href="<?= $base_url ?>/#portfolio">Portfolio</a><?php endif; ?>
        <?php if (!empty($nav_skills_exist)): ?><a class="px-4 py-2 rounded-lg hover:bg-zinc-800" href="<?= $base_url ?>/#skills">Skills</a><?php endif; ?>
        <?php if (!empty($nav_services_exist)): ?><a class="px-4 py-2 rounded-lg hover:bg-zinc-800" href="<?= $base_url ?>/#services">Services</a><?php endif; ?>
        <?php if (!empty($nav_posts_exist)): ?><a class="px-4 py-2 rounded-lg hover:bg-zinc-800" href="<?= $base_url ?>/blog.php">Blog</a><?php endif; ?>
        <a class="px-4 py-2 rounded-lg hover:bg-zinc-800" href="<?= $base_url ?>/#kontakt">Kontakt</a>
        <div class="border-t border-zinc-700 my-2"></div>
        <?php if (!empty($_SESSION['user_id'])): ?>
          <a href="<?= $base_url ?>/admin/index.php" class="px-4 py-2 rounded-lg bg-[color:rgb(10,132,255)] text-white">Dashboard</a>
        <?php else: ?>
          <a href="<?= $base_url ?>/admin/login.php" class="px-4 py-2 rounded-lg hover:bg-zinc-800">Login</a>
        <?php endif; ?>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="max-w-7xl mx-auto px-4 sm:px-6 py-8 w-full">

