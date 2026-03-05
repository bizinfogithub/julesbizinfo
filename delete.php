<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $id = $_POST['id'] ?? null;
    if (!$id) {
        header("Location: index.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM vouchers WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: index.php?msg=deleted");
        exit;
    } catch (Exception $e) {
        die("Delete failed: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit;
}
?>
