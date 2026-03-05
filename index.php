<?php
require_once 'db.php';

// Pagination settings
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search and Filter
$search = $_GET['search'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

$params = [];
$where_clauses = [];

if (!empty($search)) {
    $where_clauses[] = "id = ?";
    $params[] = $search;
}
if (!empty($date_from)) {
    $where_clauses[] = "voucher_date >= ?";
    $params[] = $date_from;
}
if (!empty($date_to)) {
    $where_clauses[] = "voucher_date <= ?";
    $params[] = $date_to;
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Count for pagination
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM vouchers $where_sql");
$count_stmt->execute($params);
$total_rows = $count_stmt->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// Fetch vouchers
$sql = "SELECT * FROM vouchers $where_sql ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vouchers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">FinanceSystem</a>
            <div class="navbar-nav">
                <a class="nav-link" href="create.php">New Voucher</a>
                <a class="nav-link active" href="index.php">List Vouchers</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'saved'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Voucher saved successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Voucher deleted successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow mb-4">
            <div class="card-header bg-white">
                <form action="index.php" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">Voucher #</label>
                        <input type="number" name="search" class="form-control" placeholder="Search ID..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($date_from) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($date_to) ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">Filter / Search</button>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Voucher #</th>
                            <th>Date</th>
                            <th>Created At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($vouchers)): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">No vouchers found.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($vouchers as $v): ?>
                        <tr>
                            <td><?= htmlspecialchars($v['id']) ?></td>
                            <td><?= htmlspecialchars($v['voucher_date']) ?></td>
                            <td><?= htmlspecialchars($v['created_at']) ?></td>
                            <td class="text-end">
                                <a href="view.php?id=<?= htmlspecialchars($v['id']) ?>" class="btn btn-sm btn-info text-white">View</a>
                                <a href="edit.php?id=<?= htmlspecialchars($v['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                                <form action="delete.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="id" value="<?= $v['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($total_pages > 1): ?>
            <div class="card-footer bg-white">
                <nav>
                    <ul class="pagination pagination-sm mb-0 justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                <a class="page-link" href="index.php?page=<?= $i ?>&search=<?= urlencode($search) ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
