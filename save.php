<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $voucher_date = $_POST['voucher_date'];
    $items = $_POST['items'] ?? [];

    if (empty($voucher_date) || empty($items)) {
        die("Missing required data.");
    }

    try {
        $pdo->beginTransaction();

        // 1. Insert Voucher Header
        $stmt = $pdo->prepare("INSERT INTO vouchers (voucher_date) VALUES (?)");
        $stmt->execute([$voucher_date]);
        $voucher_id = $pdo->lastInsertId();

        // 2. Insert Voucher Items
        $stmt = $pdo->prepare("INSERT INTO voucher_items (voucher_id, description, amount, type) VALUES (?, ?, ?, ?)");
        foreach ($items as $item) {
            $stmt->execute([
                $voucher_id,
                $item['description'],
                $item['amount'],
                $item['type']
            ]);
        }

        $pdo->commit();
        header("Location: index.php?msg=saved");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error saving voucher: " . $e->getMessage());
    }
}
?>
