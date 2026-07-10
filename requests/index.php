<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

require "../config/database.php";

// Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM requests WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: index.php");
    exit();
}

// Update status
if (isset($_POST['update_status'])) {
    $stmt = $pdo->prepare("UPDATE requests SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['id']]);
    header("Location: index.php");
    exit();
}

// Add
if (isset($_POST['save'])) {
    $stmt = $pdo->prepare("INSERT INTO requests (citizen_id, type, description, status) VALUES (?, ?, ?, 'pending')");
    $stmt->execute([$_POST['citizen_id'], $_POST['type'], $_POST['description']]);
    header("Location: index.php");
    exit();
}

$requests = $pdo->query("SELECT r.*, c.fullname as citizen_name FROM requests r LEFT JOIN citizens c ON r.citizen_id = c.id ORDER BY r.created_at DESC")->fetchAll();
$citizens = $pdo->query("SELECT id, fullname FROM citizens ORDER BY fullname")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requests - CMS Baladiya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header i {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 10px;
            display: block;
        }

        .sidebar-header h4 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .sidebar-header small {
            color: rgba(255,255,255,0.6);
            font-size: 0.8rem;
        }

        .nav-menu { padding: 20px 0; }

        .nav-item { padding: 0 15px; margin-bottom: 5px; }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            transform: translateX(5px);
        }

        .nav-link i {
            font-size: 1.2rem;
            margin-right: 12px;
            width: 24px;
            text-align: center;
        }

        .nav-link.logout { color: #ff6b6b; }
        .nav-link.logout:hover {
            background: linear-gradient(135deg, #ff6b6b, #ee5a5a);
            color: white;
        }

        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        .topbar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .topbar h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
        }

        .content { padding: 30px; }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 12px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #f1f2f6;
            padding: 20px 25px;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            border: none;
            color: #7f8c8d;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            padding: 15px 25px;
        }

        .table td {
            border: none;
            padding: 15px 25px;
            vertical-align: middle;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
        }

        .req-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .req-document { background: rgba(52, 152, 219, 0.1); color: #3498db; }
        .req-complaint { background: rgba(231, 76, 60, 0.1); color: #e74c3c; }
        .req-suggestion { background: rgba(46, 204, 113, 0.1); color: #2ecc71; }
        .req-other { background: rgba(149, 165, 166, 0.1); color: #95a5a6; }

        .status-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 6px 12px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .status-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .badge {
            padding: 8px 14px;
            border-radius: 10px;
            font-weight: 500;
        }

        .btn-action {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-action:hover { transform: scale(1.1); }
        .btn-delete { background: rgba(231, 76, 60, 0.1); color: #e74c3c; }

        .modal-content {
            border-radius: 16px;
            border: none;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 16px 16px 0 0;
            padding: 20px 25px;
        }

        .modal-header .btn-close { filter: invert(1); }

        .form-control, .form-select {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 12px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-in {
            animation: fadeInUp 0.6s ease forwards;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <i class="bi bi-building"></i>
        <h4>CMS Baladiya</h4>
        <small><?php echo htmlspecialchars($_SESSION['user']); ?></small>
    </div>
    <nav class="nav-menu">
        <div class="nav-item">
            <a class="nav-link" href="../dashboard/index.php">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        </div>
        <div class="nav-item">
            <a class="nav-link" href="../citizens/index.php">
                <i class="bi bi-people"></i>
                <span>Citizens</span>
            </a>
        </div>
        <div class="nav-item">
            <a class="nav-link" href="../documents/index.php">
                <i class="bi bi-file-earmark-text"></i>
                <span>Documents</span>
            </a>
        </div>
        <div class="nav-item">
            <a class="nav-link active" href="index.php">
                <i class="bi bi-inbox"></i>
                <span>Requests</span>
            </a>
        </div>
        <div class="nav-item">
            <a class="nav-link" href="../reports/index.php">
                <i class="bi bi-graph-up"></i>
                <span>Reports</span>
            </a>
        </div>
        <div class="nav-item">
            <a class="nav-link logout" href="../auth/logout.php">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </div>
    </nav>
</div>

<!-- Main Content -->
<div class="main-wrapper">
    <div class="topbar">
        <h2><i class="bi bi-inbox me-2 text-warning"></i>Requests</h2>
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION['user'], 0, 1)); ?>
            </div>
            <span><?php echo htmlspecialchars($_SESSION['user']); ?></span>
        </div>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4 animate-in">
            <h5 class="text-muted mb-0">Manage Requests</h5>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="bi bi-plus-lg me-2"></i>New Request
            </button>
        </div>

        <div class="card animate-in">
            <div class="card-body p-0">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Request</th>
                            <th>Citizen</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $r): 
                            $iconClass = 'req-other';
                            if ($r['type'] == 'document_request') $iconClass = 'req-document';
                            elseif ($r['type'] == 'complaint') $iconClass = 'req-complaint';
                            elseif ($r['type'] == 'suggestion') $iconClass = 'req-suggestion';
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="req-icon <?php echo $iconClass; ?>">
                                        <i class="bi bi-<?php 
                                            echo $r['type'] == 'document_request' ? 'file-earmark' : 
                                                 ($r['type'] == 'complaint' ? 'exclamation-triangle' : 
                                                 ($r['type'] == 'suggestion' ? 'lightbulb' : 'question-circle')); 
                                        ?>"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Request #<?php echo $r['id']; ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars(substr($r['description'], 0, 40)); ?>...</small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($r['citizen_name'] ?? 'N/A'); ?></td>
                            <td><?php echo str_replace('_', ' ', $r['type']); ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                    <select name="status" class="status-select" onchange="this.form.submit()">
                                        <option value="pending" <?php echo $r['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="in_progress" <?php echo $r['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="completed" <?php echo $r['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="rejected" <?php echo $r['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                    <input type="hidden" name="update_status">
                                </form>
                            </td>
                            <td><?php echo date('Y-m-d', strtotime($r['created_at'])); ?></td>
                            <td>
                                <a href="?delete=<?php echo $r['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Delete this request?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                No requests found
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>New Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Citizen</label>
                        <select name="citizen_id" class="form-select" required>
                            <option value="">Select Citizen</option>
                            <?php foreach ($citizens as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['fullname']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Type</label>
                        <select name="type" class="form-select">
                            <option value="document_request">Document Request</option>
                            <option value="complaint">Complaint</option>
                            <option value="suggestion">Suggestion</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Enter request description..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
            }
        });
    });
</script>

</body>
</html>