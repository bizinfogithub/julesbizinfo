<?php
require_once 'db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit;
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    $voucher_date = $_POST['voucher_date'];
    $items = $_POST['items'] ?? [];

    try {
        $pdo->beginTransaction();

        // Update header
        $stmt = $pdo->prepare("UPDATE vouchers SET voucher_date = ? WHERE id = ?");
        $stmt->execute([$voucher_date, $id]);

        // Delete old items and insert new ones (simpler than matching)
        $stmt = $pdo->prepare("DELETE FROM voucher_items WHERE voucher_id = ?");
        $stmt->execute([$id]);

        $stmt = $pdo->prepare("INSERT INTO voucher_items (voucher_id, description, amount, type) VALUES (?, ?, ?, ?)");
        foreach ($items as $item) {
            $stmt->execute([
                $id,
                $item['description'],
                $item['amount'],
                $item['type']
            ]);
        }

        $pdo->commit();
        header("Location: view.php?id=$id&msg=updated");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Update failed: " . $e->getMessage());
    }
}

// Fetch current data
$stmt = $pdo->prepare("SELECT * FROM vouchers WHERE id = ?");
$stmt->execute([$id]);
$voucher = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM voucher_items WHERE voucher_id = ?");
$stmt->execute([$id]);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Voucher #<?= $id ?></title>
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

    <div class="container pb-5">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">Edit Voucher #<?= $id ?></h4>
            </div>
            <div class="card-body">
                <form action="edit.php?id=<?= $id ?>" method="POST" id="voucherForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="voucher_date" class="form-label">Voucher Date *</label>
                            <input type="date" name="voucher_date" id="voucher_date" class="form-control" value="<?= htmlspecialchars($voucher['voucher_date']) ?>" required>
                        </div>
                    </div>

                    <table class="table table-bordered" id="itemsTable">
                        <thead class="table-secondary">
                            <tr>
                                <th>Description</th>
                                <th style="width: 200px;">Amount</th>
                                <th style="width: 150px;">Indicator</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $index => $item): ?>
                            <tr>
                                <td><input type="text" name="items[<?= $index ?>][description]" class="form-control" value="<?= htmlspecialchars($item['description']) ?>" required></td>
                                <td><input type="number" step="0.01" name="items[<?= $index ?>][amount]" class="form-control" value="<?= htmlspecialchars($item['amount']) ?>" required></td>
                                <td>
                                    <select name="items[<?= $index ?>][type]" class="form-select" required>
                                        <option value="debit" <?= $item['type'] == 'debit' ? 'selected' : '' ?>>Debit</option>
                                        <option value="credit" <?= $item['type'] == 'credit' ? 'selected' : '' ?>>Credit</option>
                                    </select>
                                </td>
                                <td><button type="button" class="btn btn-danger btn-sm remove-row">×</button></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="mb-3">
                        <button type="button" class="btn btn-secondary btn-sm" id="addRow">Add Row</button>
                    </div>

                    <div class="text-end">
                        <a href="index.php" class="btn btn-light me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary px-5">Update Voucher</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let rowCount = <?= count($items) ?>;
        document.getElementById('addRow').addEventListener('click', function() {
            const tbody = document.querySelector('#itemsTable tbody');
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><input type="text" name="items[${rowCount}][description]" class="form-control" required></td>
                <td><input type="number" step="0.01" name="items[${rowCount}][amount]" class="form-control" required></td>
                <td>
                    <select name="items[${rowCount}][type]" class="form-select" required>
                        <option value="debit">Debit</option>
                        <option value="credit">Credit</option>
                    </select>
                </td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">×</button></td>
            `;
            tbody.appendChild(row);
            rowCount++;
        });

        document.querySelector('#itemsTable').addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-row')) {
                if (document.querySelectorAll('#itemsTable tbody tr').length > 1) {
                    e.target.closest('tr').remove();
                } else {
                    alert('At least one item is required.');
                }
            }
        });
    </script>
</body>
</html>
