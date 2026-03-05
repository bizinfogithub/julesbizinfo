<?php require_once 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher Entry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">FinanceSystem</a>
            <div class="navbar-nav">
                <a class="nav-link active" href="create.php">New Voucher</a>
                <a class="nav-link" href="index.php">List Vouchers</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">New Voucher Entry</h4>
            </div>
            <div class="card-body">
                <form action="save.php" method="POST" id="voucherForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="voucher_date" class="form-label">Voucher Date *</label>
                            <input type="date" name="voucher_date" id="voucher_date" class="form-control" required>
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
                            <tr>
                                <td><input type="text" name="items[0][description]" class="form-control" required></td>
                                <td><input type="number" step="0.01" name="items[0][amount]" class="form-control" required></td>
                                <td>
                                    <select name="items[0][type]" class="form-select" required>
                                        <option value="debit">Debit</option>
                                        <option value="credit">Credit</option>
                                    </select>
                                </td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mb-3">
                        <button type="button" class="btn btn-secondary btn-sm" id="addRow">Add Row</button>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success px-5">Save Voucher</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let rowCount = 1;
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
                e.target.closest('tr').remove();
            }
        });
    </script>
</body>
</html>
