<?php
require_once 'db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit;
}

// Fetch header
$stmt = $pdo->prepare("SELECT * FROM vouchers WHERE id = ?");
$stmt->execute([$id]);
$voucher = $stmt->fetch();

if (!$voucher) {
    die("Voucher not found.");
}

// Fetch items
$stmt = $pdo->prepare("SELECT * FROM voucher_items WHERE voucher_id = ?");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

$total_debit = 0;
$total_credit = 0;
foreach ($items as $item) {
    if ($item['type'] == 'debit') $total_debit += $item['amount'];
    else $total_credit += $item['amount'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Voucher #<?= $id ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">FinanceSystem</a>
            <div class="navbar-nav">
                <a class="nav-link" href="create.php">New Voucher</a>
                <a class="nav-link" href="index.php">List Vouchers</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card shadow">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Voucher Details #<?= htmlspecialchars($id) ?></h4>
                <a href="edit.php?id=<?= htmlspecialchars($id) ?>" class="btn btn-sm btn-light">Edit Voucher</a>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="fw-bold">Voucher Date:</label>
                        <p><?= htmlspecialchars($voucher['voucher_date']) ?></p>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold">Created At:</label>
                        <p><?= htmlspecialchars($voucher['created_at']) ?></p>
                    </div>
                </div>

                <table class="table table-bordered">
                    <thead class="table-secondary">
                        <tr>
                            <th>Description</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['description']) ?></td>
                            <td class="text-end"><?= ($item['type'] == 'debit') ? number_format($item['amount'], 2) : '' ?></td>
                            <td class="text-end"><?= ($item['type'] == 'credit') ? number_format($item['amount'], 2) : '' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td>TOTAL</td>
                            <td class="text-end"><?= number_format($total_debit, 2) ?></td>
                            <td class="text-end"><?= number_format($total_credit, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="card-footer text-muted small text-end">
                Status: <?= ($total_debit == $total_credit) ? '<span class="text-success">Balanced</span>' : '<span class="text-danger">Unbalanced</span>' ?>
            </div>
        </div>
        <div class="mt-3">
            <a href="index.php" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
</body>
</html>
