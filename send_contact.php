<?php
// Core bootstrap file, loads functions and config
require_once __DIR__ . '/includes/bootstrap.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

// --- SPAM PROTECTION & VALIDATION ---

// 1. Honeypot Check: If the hidden 'website' field is filled, it's a bot.
if (!empty($_POST['website'])) {
    redirect('index.php?contact=success#kontakt');
}

// 2. Time-based Check: Measure how fast the form was submitted.
$form_load_time = isset($_POST['form_load_time']) ? (int)$_POST['form_load_time'] : 0;
$current_time = time();
$time_diff = $current_time - $form_load_time;

if ($form_load_time > 0 && $time_diff < 3) {
    redirect('index.php?contact=success#kontakt');
}

// 3. CSRF Token Validation
if (!csrf_token_validate($_POST['csrf_token'] ?? '')) {
    redirect('index.php?contact=error#kontakt');
}

// 4. CAPTCHA Validation (NEU)
$user_captcha_answer = isset($_POST['captcha']) ? (int)$_POST['captcha'] : null;
$correct_captcha_answer = $_SESSION['captcha_answer'] ?? null;
unset($_SESSION['captcha_answer']); // Unset after reading to prevent reuse

if ($user_captcha_answer !== $correct_captcha_answer) {
    // Redirect with an error if CAPTCHA is wrong.
    redirect('index.php?contact=error#kontakt');
}


// 5. Input validation and sanitization
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

// Check for empty fields or an invalid email format.
if (empty($name) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect('index.php?contact=error#kontakt');
}

// --- EMAIL SENDING ---

$to = $config['site']['owner_email'];
$subject = 'Neue Kontaktanfrage von ' . $name;

$body = "Du hast eine neue Nachricht über das Kontaktformular erhalten:\n\n";
$body .= "Name: " . $name . "\n";
$body .= "E-Mail: " . $email . "\n\n";
$body .= "Nachricht:\n" . $message . "\n";

$headers = 'From: ' . $email . "\r\n" .
           'Reply-To: ' . $email . "\r\n" .
           'X-Mailer: PHP/' . phpversion();

if (mail($to, $subject, $body, $headers)) {
    redirect('index.php?contact=success#kontakt');
} else {
    redirect('index.php?contact=error#kontakt');
}

