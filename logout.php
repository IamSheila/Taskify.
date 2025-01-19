<?php
session_start();
include 'config.php';

if (isset($_SESSION['user_id'])) {
    // Update user status to 'offline' before logging out
    $stmt = $pdo->prepare("UPDATE users SET user_status = 'offline' WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);

    // Destroy the session
    session_destroy();
    header('Location: login.php');
    exit();
} else {
    header('Location: login.php');
    exit();
}
?>
