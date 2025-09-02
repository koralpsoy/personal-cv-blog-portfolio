<?php
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

    if ($id && csrf_token_validate($token)) {
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
    header('Location: posts.php?msg=deleted');
    exit;
}

header('Location: posts.php');
exit;
