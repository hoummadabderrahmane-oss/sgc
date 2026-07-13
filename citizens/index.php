<?php
$pageTitle = 'Citizens';
require_once __DIR__ . '/../includes/header.php';
requireAuth();

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$perPage = 10;

$where = [];
$params = [];
if ($search) {
    $where[] = "(full_name LIKE ? OR id_number LIKE ? OR phone LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}
if ($status) {
    $where[] = "status = ?";
    $params[] = $status;
}
$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM citizens $whereClause");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$pagination = paginate($page, $perPage, $total);

$stmt = $pdo->prepare("SELECT * FROM citizens $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?");
foreach ($params as $i => $p) $stmt->bindValue($i + 1, $p);
$stmt->bindValue(count($params) + 1, $perPage, PDO::PARAM_INT);
$stmt->bindValue(count($params) + 2, $pagination['offset'], PDO::PARAM_INT);
$stmt->execute();
$citizens = $stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Citizens</h2>
        <p class="text-muted mb-0">Manage municipality citizens</p>
    </div>
    <a href="create.php" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i> Add Citizen
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-6">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="form-control" placeholder="Search by name, ID, or phone..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-funnel me-2"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Citizen</th>
                        <th>ID Number</th>
                        <th>Contact</th>
                        <th>City</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citizens as $c): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar">
                                    <?= strtoupper(substr($c['full_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="fw-semibold"><?= htmlspecialchars($c['full_name']) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($c['email'] ?? 'No email') ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="font-monospace text-muted"><?= htmlspecialchars($c['id_number']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($c['phone'] ?? 'N/A') ?></td>
                        <td class="text-muted"><?= htmlspecialchars($c['city'] ?? 'N/A') ?></td>
                        <td>
                            <span class="badge bg-<?= $c['status'] === 'active' ? 'success' : 'secondary' ?>">
                                <?= ucfirst($c['status']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="view.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-info me-1"><i class="bi bi-eye"></i></a>
                            <a href="edit.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-warning me-1"><i class="bi bi-pencil"></i></a>
                            <a href="delete.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger btn-delete" onclick="return confirm('Delete this citizen?')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($pagination['totalPages'] > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
        <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>