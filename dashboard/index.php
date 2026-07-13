<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
requireAuth();

$stats = [
    'citizens' => $pdo->query("SELECT COUNT(*) FROM citizens")->fetchColumn(),
    'active_citizens' => $pdo->query("SELECT COUNT(*) FROM citizens WHERE status = 'active'")->fetchColumn(),
    'documents' => $pdo->query("SELECT COUNT(*) FROM documents")->fetchColumn(),
    'pending_requests' => $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'pending'")->fetchColumn(),
];

$recentCitizens = $pdo->query("SELECT * FROM citizens ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentRequests = $pdo->query("SELECT r.*, c.full_name as citizen_name FROM requests r JOIN citizens c ON r.citizen_id = c.id ORDER BY r.created_at DESC LIMIT 5")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Dashboard</h2>
        <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?>!</p>
    </div>
    <span class="badge bg-primary px-3 py-2 rounded-pill">
        <i class="bi bi-calendar3 me-1"></i> <?= date('l, F j, Y') ?>
    </span>
</div>

<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted text-uppercase small fw-semibold mb-1">Total Citizens</p>
                        <h3 class="mb-0"><?= number_format($stats['citizens']) ?></h3>
                    </div>
                    <div class="p-2 rounded-3" style="background:rgba(99,102,241,0.1);">
                        <i class="bi bi-people fs-4 text-primary"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge bg-success bg-opacity-25 text-success">
                        <i class="bi bi-arrow-up"></i> <?= $stats['active_citizens'] ?> active
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted text-uppercase small fw-semibold mb-1">Documents</p>
                        <h3 class="mb-0"><?= number_format($stats['documents']) ?></h3>
                    </div>
                    <div class="p-2 rounded-3" style="background:rgba(16,185,129,0.1);">
                        <i class="bi bi-file-earmark-text fs-4 text-success"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-muted small">Total files in system</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted text-uppercase small fw-semibold mb-1">Pending</p>
                        <h3 class="mb-0"><?= number_format($stats['pending_requests']) ?></h3>
                    </div>
                    <div class="p-2 rounded-3" style="background:rgba(245,158,11,0.1);">
                        <i class="bi bi-inbox fs-4 text-warning"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="/requests/" class="text-warning text-decoration-none small">Review now <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card danger h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted text-uppercase small fw-semibold mb-1">Quick Add</p>
                        <h3 class="mb-0"><i class="bi bi-plus-lg"></i></h3>
                    </div>
                    <div class="p-2 rounded-3" style="background:rgba(99,102,241,0.1);">
                        <i class="bi bi-person-plus fs-4 text-primary"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="/citizens/create.php" class="btn btn-sm btn-primary w-100">New Citizen</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people me-2"></i>Recent Citizens</span>
                <a href="/citizens/" class="btn btn-sm btn-outline-primary rounded-pill">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr><th>Citizen</th><th>ID</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentCitizens as $c): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar" style="width:32px;height:32px;font-size:0.75rem;">
                                            <?= strtoupper(substr($c['full_name'], 0, 1)) ?>
                                        </div>
                                        <a href="/citizens/view.php?id=<?= $c['id'] ?>" class="text-decoration-none" style="color:var(--text);">
                                            <?= htmlspecialchars($c['full_name']) ?>
                                        </a>
                                    </div>
                                </td>
                                <td class="text-muted"><?= htmlspecialchars($c['id_number']) ?></td>
                                <td class="text-muted small"><?= formatDate($c['created_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-inbox me-2"></i>Recent Requests</span>
                <a href="/requests/" class="btn btn-sm btn-outline-primary rounded-pill">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr><th>Citizen</th><th>Type</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentRequests as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['citizen_name']) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($r['request_type']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $r['status'] === 'approved' ? 'success' : ($r['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                        <?= ucfirst($r['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>