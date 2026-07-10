<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

require "../config/database.php";

// Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM documents WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: index.php");
    exit();
}

// Add
if (isset($_POST['save'])) {
    $stmt = $pdo->prepare("INSERT INTO documents (citizen_id, title, type, status) VALUES (?, ?, ?, 'pending')");
    $stmt->execute([$_POST['citizen_id'], $_POST['title'], $_POST['type']]);
    header("Location: index.php");
    exit();
}

$documents = $pdo->query("SELECT d.*, c.fullname as citizen_name FROM documents d LEFT JOIN citizens c ON d.citizen_id = c.id ORDER BY d.created_at DESC")->fetchAll();
$citizens = $pdo->query("SELECT id, fullname FROM citizens ORDER BY fullname")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents - CMS Baladiya</title>
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

        .doc-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .doc-birth { background: rgba(52, 152, 219, 0.1); color: #3498db; }
        .doc-residence { background: rgba(46, 204, 113, 0.1); color: #2ecc71; }
        .doc-marriage { background: rgba(155, 89, 182, 0.1); color: #9b59b6; }
        .doc-death { background: rgba(149, 165, 166, 0.1); color: #95a5a6; }
        .doc-other { background: rgba(241, 196, 15, 0.1); color: #f1c40f; }

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
            <a class="nav-link active" href="index.php">
                <i class="bi bi-file-earmark-text"></i>
                <span>Documents</span>
            </a>
        </div>
        <div class="nav-item">
            <a class="nav-link" href="../requests/index.php">
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
        <h2><i class="bi bi-file-earmark-text me-2 text-primary"></i>Documents</h2>
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION['user'], 0, 1)); ?>
            </div>
            <span><?php echo htmlspecialchars($_SESSION['user']); ?></span>
        </div>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4 animate-in">
            <h5 class="text-muted mb-0">Manage Documents</h5>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="bi bi-plus-lg me-2"></i>Add Document
            </button>
        </div>

        <div class="card animate-in">
            <div class="card-body p-0">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Document</th>
                            <th>Citizen</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $d): 
                            $iconClass = 'doc-other';
                            if ($d['type'] == 'birth_certificate') $iconClass = 'doc-birth';
                            elseif ($d['type'] == 'residence_certificate') $iconClass = 'doc-residence';
                            elseif ($d['type'] == 'marriage_certificate') $iconClass = 'doc-marriage';
                            elseif ($d['type'] == 'death_certificate') $iconClass = 'doc-death';
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="doc-icon <?php echo $iconClass; ?>">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($d['title']); ?></div>
                                        <small class="text-muted">ID: #<?php echo $d['id']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($d['citizen_name'] ?? 'N/A'); ?></td>
                            <td><?php echo str_replace('_', ' ', $d['type']); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $d['status'] == 'approved' ? 'success' : 
                                         ($d['status'] == 'rejected' ? 'danger' : 'warning'); 
                                ?>">
                                    <?php echo $d['status']; ?>
                                </span>
                            </td>
                            <td><?php echo date('Y-m-d', strtotime($d['created_at'])); ?></td>
                            <td>
                                <a href="?delete=<?php echo $d['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Delete this document?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($documents)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-folder2-open fs-1 d-block mb-3"></i>
                                No documents found
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
                    <h5 class="modal-title"><i class="bi bi-file-earmark-plus me-2"></i>Add New Document</h5>
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
                        <label class="form-label fw-bold">Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Enter document title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Type</label>
                        <select name="type" class="form-select">
                            <option value="birth_certificate">Birth Certificate</option>
                            <option value="residence_certificate">Residence Certificate</option>
                            <option value="marriage_certificate">Marriage Certificate</option>
                            <option value="death_certificate">Death Certificate</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>Save Document
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