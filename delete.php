<?php
require_once 'config.php';

if (isset($_SESSION['user_id']) && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM urls WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
}
header("Location: dashboard.php");
exit;