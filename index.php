<?php
// Lade die zentrale Bootstrap-Datei.
require_once __DIR__ . '/includes/bootstrap.php';

// Initialisiere alle Daten-Arrays als leer.
$settings_raw = [];
$social_links = [];
$timeline_entries = [];
$projects = [];
$skills_by_category = [];
$services = [];
$testimonials = [];
$latest_posts = [];
$db_error = null;

// Lade alle Daten in einem try-catch-Block, um fatale Fehler abzufangen.
try {
    $settings_raw = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
    $social_links = $pdo->query("SELECT * FROM social_links ORDER BY sort_order ASC")->fetchAll();
    $timeline_entries = $pdo->query("SELECT * FROM timeline ORDER BY sort_order DESC, id DESC")->fetchAll();
    $projects = $pdo->query("SELECT * FROM projects ORDER BY sort_order ASC, created_at DESC")->fetchAll();
    
    // Skills mit Kategorien abrufen und gruppieren
    $skills_query = $pdo->query("
        SELECT s.*, sc.name as category_name 
        FROM skills s
        LEFT JOIN skill_categories sc ON s.category_id = sc.id
        ORDER BY sc.sort_order ASC, sc.name ASC, s.sort_order ASC, s.percentage DESC, s.name ASC
    ");
    $skills_raw = $skills_query->fetchAll();
    
    foreach ($skills_raw as $skill) {
        $skills_by_category[$skill['category_name'] ?? 'Weitere'][] = $skill;
    }

    $services = $pdo->query("SELECT * FROM services ORDER BY sort_order ASC, title ASC")->fetchAll();
    $testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY sort_order ASC LIMIT 2")->fetchAll();
    $latest_posts = $pdo->query("SELECT id, title, content, created_at FROM posts ORDER BY created_at DESC LIMIT 2")->fetchAll();
} catch (PDOException $e) {
    $db_error = "Datenbankfehler: " . $e->getMessage();
}

// Definiere die Sektionen als PHP-Funktionen, um Duplizierung zu vermeiden
function render_profile_panel($settings_raw, $social_links) { ?>
    <div class="panel p-6 text-center lg:text-center lift" data-reveal>
        <div class="w-48 h-48 sm:w-56 sm:h-56 rounded-full mx-auto bg-zinc-800/60 ring-4 ring-zinc-700/50 flex items-center justify-center overflow-hidden">
          <?php $profile_image = $settings_raw['profile_image_url'] ?? 'assets/koralp.jpg'; ?>
          <img src="<?= h($profile_image) ?>" alt="Profilbild von Koralp Soy" class="w-full h-full object-cover">
      </div>
      <h1 class="mt-4 text-2xl font-bold tracking-tight"><?= h($settings_raw['profile_name'] ?? 'Koralp Soy') ?></h1>
      <p class="mt-1 text-zinc-300"><?= h($settings_raw['profile_title'] ?? 'Wirtschaftsinformatiker (B.Sc.)') ?></p>
      
      <div class="mt-6 flex flex-col items-center lg:items-start space-y-3">
        <?php foreach ($social_links as $link): ?>
            <a href="<?= h($link['url'] ?? '#') ?>" target="_blank" rel="noopener noreferrer" class="group inline-flex items-center gap-3 text-zinc-300 hover:text-white transition-colors">
                <?= get_social_icon_svg(h($link['name'] ?? '')) ?>
                <span><?= h($link['value'] ?? '') ?></span>
            </a>
        <?php endforeach; ?>
      </div>
    </div>
<?php }

function render_skills_panel($skills_by_category) { ?>
    <?php if (!empty($skills_by_category)): ?>
    <div id="skills" class="panel p-6 lift" data-reveal>
      <h3 class="text-xl font-semibold mb-4">Skills</h3>
      <div class="space-y-6">
        <?php foreach ($skills_by_category as $category_name => $skills_in_category): ?>
        <div>
          <h4 class="font-semibold text-zinc-300 mb-3"><?= h($category_name) ?></h4>
          <div class="grid grid-cols-2 gap-4">
            <?php foreach ($skills_in_category as $skill): ?>
              <div class="flex flex-col items-center text-center">
                <div class="skill-circle" style="--p:<?= (int)($skill['percentage'] ?? 0) ?>;"><span class="skill-percentage"><?= (int)($skill['percentage'] ?? 0) ?>%</span></div>
                <div class="text-sm font-medium mt-2"><?= h($skill['name'] ?? '') ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
<?php }

function render_hero_section($settings_raw) { ?>
    <section class="panel p-6 md:p-8 lift" data-reveal>
      <p class="text-accent font-medium"><?= h($settings_raw['hero_kicker'] ?? 'Hallo, ich bin Koralp Soy') ?></p>
      <h2 class="mt-3 text-3xl md:text-4xl font-bold tracking-tight"><?= nl2br(h($settings_raw['hero_headline'] ?? "Full‑Stack Entwickler & E‑Commerce Experte.")) ?></h2>
      <div class="mt-4 text-zinc-300 leading-relaxed space-y-4">
        <?= $settings_raw['hero_text'] ?? '<p>Mit über 8 Jahren Erfahrung in der Softwareentwicklung und im E-Commerce-Management helfe ich Unternehmen, ihre digitale Präsenz zu optimieren. Von maßgeschneiderten Shop-Lösungen bis hin zu komplexen Datenpipelines – ich entwickle robuste und skalierbare Systeme, die echten Mehrwert schaffen.</p>' ?>
      </div>
    </section>
<?php }

function render_timeline_panel($timeline_entries) { ?>
    <?php if (!empty($timeline_entries)): ?>
    <div class="panel p-6 lift" data-reveal>
        <h3 class="text-xl font-semibold">Lebenslauf</h3>
        <div class="timeline mt-4 space-y-6">
            <?php foreach ($timeline_entries as $entry): ?>
            <div class="timeline-item">
                <div class="text-sm text-zinc-400"><?= h($entry['date_range'] ?? '') ?></div>
                <h4 class="font-medium mt-1"><?= h($entry['title'] ?? '') ?></h4>
                <p class="text-sm text-zinc-300 mt-1"><?= h($entry['description'] ?? '') ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
<?php }

function render_portfolio_section($projects) { ?>
    <?php if (!empty($projects)): ?>
    <section id="portfolio" class="panel p-6 lift" data-reveal>
      <h3 class="text-xl font-semibold">Portfolio</h3>
      <div class="portfolio-gallery mt-4 grid md:grid-cols-2 gap-6">
        <?php foreach ($projects as $project): ?>
          <?php
            $imageUrl = h($project['image_url'] ?? '');
            $projectUrl = h($project['project_url'] ?? '#');
            $title = h($project['title'] ?? '');
            $projectDescription = h($project['description'] ?? '');

            // Optimierte Beschreibung mit Icon für externe Links
            $lightboxDescription = '<div class="project-link-wrapper">';
            $lightboxDescription .= '<span class="project-title">' . $title . '</span>';
            if (!empty($projectUrl) && $projectUrl !== '#') {
                $lightboxDescription .= ' • <a href="' . $projectUrl . '" target="_blank" rel="noopener" class="project-external-link">Projekt ansehen <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-left:4px"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg></a>';
            }
            $lightboxDescription .= '</div>';
          ?>

          <?php if (!empty($imageUrl)): ?>
            <a class="project-card group portfolio-item"
               href="<?= $imageUrl ?>"
               title="<?= $title ?>"
               data-description="<?= htmlspecialchars($lightboxDescription, ENT_QUOTES, 'UTF-8') ?>"
               data-gallery="portfolio">
          <?php else: ?>
            <div class="project-card group">
          <?php endif; ?>

            <div class="aspect-[16/10] rounded-xl overflow-hidden bg-zinc-800/60 ring-1 ring-zinc-700/50 flex items-center justify-center">
                <?php if (!empty($imageUrl)): ?>
                    <img src="<?= $imageUrl ?>" alt="Vorschaubild für <?= $title ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                <?php else: ?>
                    <span class="text-zinc-500 text-sm">Kein Bild</span>
                <?php endif; ?>
            </div>
            <h4 class="font-medium mt-3"><?= $title ?></h4>
            <p class="text-sm text-zinc-300 mt-1"><?= $projectDescription ?></p>
            <div class="mt-3 flex flex-wrap gap-2">
                <?php foreach (explode(',', $project['tags'] ?? '') as $tag): if(trim($tag)):?>
                    <span class="tag"><?= h(trim($tag)) ?></span>
                <?php endif; endforeach; ?>
            </div>
          
          <?php if (!empty($imageUrl)): ?>
            </a>
          <?php else: ?>
            </div>
          <?php endif; ?>

        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>
<?php }


function render_services_section($services) { ?>
    <?php if (!empty($services)): ?>
    <section id="services" class="panel p-6 lift" data-reveal>
      <h3 class="text-xl font-semibold">Services</h3>
      <div class="mt-4 grid md:grid-cols-2 gap-4">
        <?php foreach ($services as $service): ?>
          <div class="service-card">
            <h4 class="font-semibold"><?= h($service['title'] ?? '') ?></h4>
            <p class="text-sm text-zinc-300 mt-1"><?= h($service['description'] ?? '') ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>
<?php }

function render_blog_and_testimonials_section($testimonials, $latest_posts) { ?>
    <?php if (!empty($testimonials) || !empty($latest_posts)): ?>
    <section class="space-y-8">
        <?php if (!empty($testimonials)): ?>
        <div class="grid md:grid-cols-2 gap-4">
            <?php foreach ($testimonials as $testimonial): ?>
            <div class="testimonial-card lift" data-reveal>
                <p class="text-zinc-200">„<?= h($testimonial['text'] ?? '') ?>“</p>
                <footer class="mt-3 font-semibold">
                <?= h($testimonial['author_name'] ?? '') ?>
                <span class="text-zinc-400 font-normal"> – <?= h($testimonial['author_role'] ?? '') ?></span>
                </footer>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($latest_posts)): ?>
        <div class="space-y-4">
          <div class="panel p-6 lift" data-reveal>
            <h3 class="text-xl font-semibold">Aus dem Blog</h3>
          </div>
          <div class="grid md:grid-cols-2 gap-4">
          <?php foreach ($latest_posts as $post): ?>
            <a class="panel p-4 hover:bg-zinc-900/60 transition lift" href="post.php?id=<?= (int)($post['id'] ?? 0) ?>" data-reveal>
              <div class="text-sm text-zinc-400"><?= date('d.m.Y', strtotime($post['created_at'] ?? 'now')) ?></div>
              <h3 class="font-medium mt-1"><?= h($post['title'] ?? '') ?></h3>
              <p class="text-zinc-300 mt-1 text-sm"><?= h(excerpt($post['content'] ?? '')) ?></p>
            </a>
          <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>
<?php }

function render_contact_section() { 
    // Generate CAPTCHA question
    $num1 = rand(1, 9);
    $num2 = rand(1, 9);
    $_SESSION['captcha_answer'] = $num1 + $num2;
?>
    <section id="kontakt" class="panel p-6 lift" data-reveal>
      <h3 class="text-xl font-semibold">Kontakt</h3>
      <?php if (isset($_GET['contact'])): ?>
        <div class="my-3 p-3 rounded-lg text-sm <?= $_GET['contact'] === 'success' ? 'bg-emerald-900/50 text-emerald-300' : 'bg-rose-900/50 text-rose-300'; ?>">
          <?= $_GET['contact'] === 'success' ? 'Vielen Dank! Deine Nachricht wurde erfolgreich gesendet.' : 'Es gab einen Fehler. Bitte fülle alle Felder korrekt aus oder löse die Rechenaufgabe.'; ?>
        </div>
      <?php endif; ?>
      <form class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3" method="post" action="send_contact.php">
        <?= csrf_token_field() ?>
        
        <div class="honeypot-field">
            <label for="website">Please leave this field empty</label>
            <input type="text" id="website" name="website" autocomplete="off" tabindex="-1">
        </div>
        <input type="hidden" name="form_load_time" value="<?= time() ?>">

        <input class="px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" type="text" name="name" placeholder="Dein Name" required>
        <input class="px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" type="email" name="email" placeholder="Deine E‑Mail" required>
        
        <textarea class="sm:col-span-2 px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" name="message" placeholder="Deine Nachricht" rows="4" required></textarea>

        <div class="sm:col-span-2">
            <label for="captcha" class="block text-sm font-medium text-zinc-300 mb-1">Spam-Schutz: Was ist <?= $num1 ?> + <?= $num2 ?>?</label>
            <input id="captcha" class="w-full px-3 py-2 rounded-lg bg-zinc-900/60 border border-zinc-800" type="number" name="captcha" placeholder="Antwort" required>
        </div>

        <button type="submit" class="sm:col-span-2 inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-[color:rgb(10,132,255)] text-white shadow-glow">Nachricht senden</button>
      </form>
    </section>
<?php }

// Lade den Header.
include __DIR__ . '/includes/header.php';
?>

<?php if ($db_error): ?>
    <div class="panel p-6 text-rose-300"><?= h($db_error) ?></div>
<?php else: ?>
    <!-- Desktop Layout -->
    <div class="hidden lg:grid lg:grid-cols-[380px,1fr] gap-8">
        <aside class="lg:sticky top-28 self-start space-y-8" id="sidebar-scroller">
            <?php render_profile_panel($settings_raw, $social_links); ?>
            <?php render_skills_panel($skills_by_category); ?>
        </aside>
        <div class="space-y-8">
            <?php render_hero_section($settings_raw); ?>
            <?php render_timeline_panel($timeline_entries); ?>
            <?php render_portfolio_section($projects); ?>
            <?php render_services_section($services); ?>
            <?php render_blog_and_testimonials_section($testimonials, $latest_posts); ?>
            <?php render_contact_section(); ?>
        </div>
    </div>

    <!-- Mobile Layout -->
    <div class="lg:hidden space-y-8">
        <?php render_profile_panel($settings_raw, $social_links); ?>
        <?php render_hero_section($settings_raw); ?>
        <?php render_timeline_panel($timeline_entries); ?>
        <?php render_skills_panel($skills_by_category); ?>
        <?php render_portfolio_section($projects); ?>
        <?php render_services_section($services); ?>
        <?php render_blog_and_testimonials_section($testimonials, $latest_posts); ?>
        <?php render_contact_section(); ?>
    </div>
<?php endif; ?>

<?php
// Lade den Footer.
include __DIR__ . '/includes/footer.php';
?>
