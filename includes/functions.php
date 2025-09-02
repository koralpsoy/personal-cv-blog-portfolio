<?php
/**
 * functions.php — Icons: all white, robust slugs, inline SVGs for LinkedIn & Microsoft Teams
 * - Uses inline SVGs (white) for: linkedin, teams/msteams/microsoftteams (per your provided SVGs)
 * - Uses Simple Icons CDN (CC0) for other brands (forced white) — can be swapped later
 * - Provides inline white SVGs for generics: email, web, link/generic
 * - Skype removed from catalog
 */

// =================================================================
// Core Helper Functions
// =================================================================

function base_url() {
    $config_path = __DIR__ . '/../config.php';
    if (file_exists($config_path)) {
        $config = require $config_path;
        if (!empty($config['site']['base_url'])) {
            return rtrim($config['site']['base_url'], '/');
        }
    }
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_dir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
    return rtrim($protocol . $host . $script_dir, '/');
}

function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function excerpt($text, $length = 180) {
    $plain = trim(strip_tags($text));
    return (mb_strlen($plain) <= $length) ? $plain : mb_substr($plain, 0, $length) . '…';
}

function redirect($path) {
    $url = rtrim(base_url(), '/') . '/' . ltrim($path, '/');
    header('Location: ' . $url);
    exit;
}

function is_logged_in() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return !empty($_SESSION['user_id']);
}

function dev_mode() {
    if (isset($_GET['debug']) && $_GET['debug'] === '1') {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }
}
dev_mode();

// =================================================================
// CSRF Protection
// =================================================================

function csrf_token_generate() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_token_validate($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return !empty($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_token_field() {
    $token = csrf_token_generate();
    return '<input type="hidden" name="csrf_token" value="' . h($token) . '">';
}

// =================================================================
// Icons (catalog + helpers)
// =================================================================

/**
 * Categorized list for your picker (white icons; no brand colors stored here).
 * NOTE: Skype intentionally removed per request.
 */
function get_all_icons_categorized() {
    return [
        'Social & Messaging' => [
            ['name' => 'facebook', 'label' => 'Facebook'], ['name' => 'instagram', 'label' => 'Instagram'], ['name' => 'x', 'label' => 'X (Twitter)'],
            ['name' => 'threads', 'label' => 'Threads'], ['name' => 'linkedin', 'label' => 'LinkedIn'], ['name' => 'xing', 'label' => 'XING'],
            ['name' => 'youtube', 'label' => 'YouTube'], ['name' => 'tiktok', 'label' => 'TikTok'], ['name' => 'pinterest', 'label' => 'Pinterest'],
            ['name' => 'whatsapp', 'label' => 'WhatsApp'], ['name' => 'telegram', 'label' => 'Telegram'], ['name' => 'discord', 'label' => 'Discord'],
            ['name' => 'messenger', 'label' => 'Messenger'], ['name' => 'snapchat', 'label' => 'Snapchat'], ['name' => 'reddit', 'label' => 'Reddit'],
            ['name' => 'signal', 'label' => 'Signal'], ['name' => 'teams', 'label' => 'Microsoft Teams'],
            ['name' => 'zoom', 'label' => 'Zoom'], ['name' => 'slack', 'label' => 'Slack'], ['name' => 'twitch', 'label' => 'Twitch'], 
            ['name' => 'mastodon', 'label' => 'Mastodon'], ['name' => 'bluesky', 'label' => 'Bluesky'], ['name' => 'flickr', 'label' => 'Flickr'],
        ],
        'Developer & Tech' => [
            ['name' => 'github', 'label' => 'GitHub'], ['name' => 'gitlab', 'label' => 'GitLab'], ['name' => 'bitbucket', 'label' => 'Bitbucket'],
            ['name' => 'stackoverflow', 'label' => 'Stack Overflow'], ['name' => 'devto', 'label' => 'Dev.to'], ['name' => 'hashnode', 'label' => 'Hashnode'],
            ['name' => 'medium', 'label' => 'Medium'], ['name' => 'hackthebox', 'label' => 'Hack The Box'], ['name' => 'tryhackme', 'label' => 'TryHackMe'],
            ['name' => 'hackerone', 'label' => 'HackerOne'], ['name' => 'kaggle', 'label' => 'Kaggle'], ['name' => 'replit', 'label' => 'Replit'],
        ],
        'Creative & Portfolio' => [
            ['name' => 'behance', 'label' => 'Behance'], ['name' => 'dribbble', 'label' => 'Dribbble'], ['name' => 'artstation', 'label' => 'ArtStation'],
            ['name' => 'deviantart', 'label' => 'DeviantArt'], ['name' => 'vimeo', 'label' => 'Vimeo'],
            ['name' => '500px', 'label' => '500px'], ['name' => 'producthunt', 'label' => 'Product Hunt'], ['name' => 'polywork', 'label' => 'Polywork'],
        ],
        'Music & Audio' => [
            ['name' => 'spotify', 'label' => 'Spotify'], ['name' => 'soundcloud', 'label' => 'SoundCloud'], ['name' => 'bandcamp', 'label' => 'Bandcamp'],
            ['name' => 'audiomack', 'label' => 'Audiomack'], ['name' => 'lastfm', 'label' => 'Last.fm'], ['name' => 'mixcloud', 'label' => 'Mixcloud'],
        ],
        'General & Other' => [
            ['name' => 'email', 'label' => 'E-Mail'], ['name' => 'web', 'label' => 'Web/Website'], ['name' => 'link', 'label' => 'Generic Link'],
            ['name' => 'rss', 'label' => 'RSS Feed'], ['name' => 'patreon', 'label' => 'Patreon'], ['name' => 'kofi', 'label' => 'Ko-fi'],
            ['name' => 'goodreads', 'label' => 'Goodreads'], ['name' => 'letterboxd', 'label' => 'Letterboxd'], ['name' => 'wikipedia', 'label' => 'Wikipedia'],
        ]
    ];
}

/**
 * Normalize & map human names to Simple Icons slugs.
 * - lowercased, spaces/underscores removed
 * - handle known special cases
 * - return NULL for generic (email/web/link/generic) so we use inline SVG
 * NOTE: LinkedIn & Teams are handled as inline SVGs below and don't need slugs.
 */
function si_slug($name) {
    $n = strtolower(trim($name));
    $normalized = preg_replace('/[\s_]+/', '', $n);

    // generics -> inline
    if (in_array($normalized, ['email','mail','web','website','link','generic'], true)) {
        return null;
    }

    switch ($normalized) {
        case 'devto':        return 'devdotto';
        case 'lastfm':       return 'lastdotfm';
        case 'kofi':
        case 'ko-fi':        return 'kofi'; // Ko‑Fi CDN slug
        // LinkedIn/Teams are returned via inline SVGs
        default:             return $normalized; // most slugs match normalized form
    }
}

/**
 * Build CDN URL (white by default) for brands other than LinkedIn/Teams.
 * Returns NULL for generics.
 */
function get_social_icon_url($icon_name, $color = null) {
    $key = strtolower(trim($icon_name));
    // Skip CDN for inline-handled brands
    if (in_array($key, ['linkedin','teams','msteams','microsoftteams'], true)) {
        return null;
    }
    $slug = si_slug($icon_name);
    if ($slug === null) {
        return null; // generics -> inline fallback
    }
    $safe_color = $color ? ltrim($color, '#') : 'ffffff'; // enforce white by default
    return "https://cdn.simpleicons.org/{$slug}/{$safe_color}";
}

/**
 * Render <img> (brand) or inline <svg> (brands handled inline + generics) — all white by default.
 */
function render_social_icon($icon_name, $brand_color = null, $classes = 'w-6 h-6', $alt = null) {
    $key = strtolower(trim($icon_name));
    $alt_text = $alt ?? ucfirst($key) . ' Logo';

    // 1) Inline for LinkedIn & Teams (from your provided SVGs, normalized to white)
    if (in_array($key, ['linkedin','teams','msteams','microsoftteams'], true)) {
        return inline_brand_svg($key, $classes, $alt_text);
    }

    // 2) CDN for other brands
    $url = get_social_icon_url($key, $brand_color);
    if ($url === null) {
        // 3) Inline generics
        return inline_generic_svg($key, $classes, $alt_text);
    }

    // onerror fallback: generic link SVG if CDN fails
    $fallback = htmlspecialchars(inline_generic_svg('link', $classes, $alt_text), ENT_QUOTES, 'UTF-8');
    return '<img src="' . h($url) . '" alt="' . h($alt_text) . '" class="' . h($classes) . '" loading="lazy" onerror="this.outerHTML=\'' . $fallback . '\'" />';
}

/** Backward-compatible wrapper for existing calls (used in profile_edit.php) */
function get_social_icon_svg($icon_name, $classes = 'w-6 h-6') {
    return render_social_icon($icon_name, null, $classes);
}

// =================================================================
// Inline SVGs (white) — brands provided by you + generic icons
// =================================================================

function inline_brand_svg($name, $classes, $alt_text) {
    switch (strtolower($name)) {
        case 'linkedin':          return svg_linkedin($classes, $alt_text);
        case 'teams':
        case 'msteams':
        case 'microsoftteams':    return svg_microsoft_teams($classes, $alt_text);
        default:                  return inline_generic_svg('link', $classes, $alt_text);
    }
}

/** Your provided LinkedIn SVG (normalized to fill #ffffff) */
function svg_linkedin($classes, $alt) {
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="' . h($classes) . '" role="img" aria-label="' . h($alt) . '">
        <path fill="#ffffff" d="M5 3c0 1.062-.71 1.976-2.001 1.976C1.784 4.976 1 4.114 1 3.052C1 1.962 1.76 1 3 1s1.976.91 2 2M1 19V6h4v13zm6-8.556c0-1.545-.051-2.836-.102-3.951h3.594l.178 1.723h.076c.506-.811 1.746-2 3.822-2C17.1 6.216 19 7.911 19 11.558V19h-4v-6.861c0-1.594-.607-2.81-2-2.81c-1.062 0-1.594.86-1.873 1.569c-.102.254-.127.608-.127.963V19H7z"/>
    </svg>';
}

/** Your provided Microsoft Teams SVG (normalized; keep white fill) */
function svg_microsoft_teams($classes, $alt) {
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" class="' . h($classes) . '" role="img" aria-label="' . h($alt) . '">
        <g fill="#ffffff">
            <path d="M9.186 4.797a2.42 2.42 0 1 0-2.86-2.448h1.178c.929 0 1.682.753 1.682 1.682zm-4.295 7.738h2.613c.929 0 1.682-.753 1.682-1.682V5.58h2.783a.7.7 0 0 1 .682.716v4.294a4.197 4.197 0 0 1-4.093 4.293c-1.618-.04-3-.99-3.667-2.35Zm10.737-9.372a1.674 1.674 0 1 1-3.349 0a1.674 1.674 0 0 1 3.349 0m-2.238 9.488l-.12-.002a5.2 5.2 0 0 0 .381-2.07V6.306a1.7 1.7 0 0 0-.15-.725h1.792c.39 0 .707.317.707.707v3.765a2.6 2.6 0 0 1-2.598 2.598z"/>
            <path d="M.682 3.349h6.822c.377 0 .682.305.682.682v6.822a.8.8 0 0 1-.682.682H.682A.68.68 0 0 1 0 10.853V4.03c0-.377.305-.682.682-.682Zm5.206 2.596v-.72h-3.59v.72h1.357V9.66h.87V5.945z"/>
        </g>
    </svg>';
}

// -------------------------
// Inline SVG (white) for generics
// -------------------------
function inline_generic_svg($name, $classes, $alt_text) {
    $n = strtolower(trim($name));
    switch ($n) {
        case 'email':
        case 'mail':    return svg_email($classes, $alt_text);
        case 'web':
        case 'website': return svg_globe($classes, $alt_text);
        case 'generic':
        case 'link':
        default:        return svg_link($classes, $alt_text);
    }
}

function svg_email($classes, $alt) {
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="' . h($classes) . '" role="img" aria-label="' . h($alt) . '">
        <rect x="3" y="5" width="18" height="14" rx="2" ry="2" fill="none" stroke="#ffffff" stroke-width="2"/>
        <path d="M4 7l8 6 8-6" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>';
}

function svg_globe($classes, $alt) {
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="' . h($classes) . '" role="img" aria-label="' . h($alt) . '">
        <circle cx="12" cy="12" r="9" fill="none" stroke="#ffffff" stroke-width="2"/>
        <path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
    </svg>';
}

function svg_link($classes, $alt) {
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="' . h($classes) . '" role="img" aria-label="' . h($alt) . '">
        <path d="M10 14a5 5 0 0 1 0-7l2-2a5 5 0 1 1 7 7l-1.5 1.5" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M14 10a5 5 0 0 1 0 7l-2 2a5 5 0 1 1-7-7L6.5 10.5" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>';
}
