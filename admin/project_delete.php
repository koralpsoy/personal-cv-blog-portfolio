<?php
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/db.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

    if ($id && csrf_token_validate($token)) {
        $stmt = $pdo->prepare("SELECT image_url FROM projects WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $project = $stmt->fetch();

        if ($project) {
            $delete_stmt = $pdo->prepare("DELETE FROM projects WHERE id = :id");
            $delete_stmt->execute([':id' => $id]);

            if (!empty($project['image_url']) && file_exists(__DIR__ . '/../' . $project['image_url'])) {
                unlink(__DIR__ . '/../' . $project['image_url']);
            }
        }
    }
    header('Location: projects.php?msg=deleted');
    exit;
}

header('Location: projects.php');
exit;
