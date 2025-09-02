<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

    if ($id && csrf_token_validate($token)) {
        $stmt = $pdo->prepare("DELETE FROM timeline WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
    header('Location: timeline.php?msg=deleted');
    exit;
}

header('Location: timeline.php');
exit;
