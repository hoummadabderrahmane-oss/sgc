<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

require "../config/database.php";

// Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM reports WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: index.php");
    exit();
}

// Add
if (isset($_POST['save'])) {
    $stmt = $pdo->prepare("INSERT INTO reports (title, content, type, created_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_POST['title'],
        $_POST['content'],
        $_POST['type'],
        $_SESSION['user_id'] ?? 1
    ]);
    header("Location: index.php");
    exit();
}

$reports = $pdo->query("SELECT r.*, u.fullname as author FROM reports r LEFT JOIN users u ON r.created_by = u.id ORDER BY r.created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - CMS Baladiya</title>
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

        .report-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
        }

        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }

        .report-header {
            padding: 20px 25px;
            border-bottom: 1px solid #f1f2f6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .report-type {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .type-monthly { background: rgba(52, 152, 219, 0.1); color: #3498db; }
        .type-annual { background: rgba(46, 204, 113, 0.1); color: #2ecc71; }
        .type-incident { background: rgba(231, 76, 60, 0.1); color: #e74c3c; }
        .type-other { background: rgba(149, 165, 166, 0.1); color: #95a5a6; }

        .report-body {
            padding: 25px;
        }

        .report-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .report-content {
            color: #7f8c8d;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .report-footer {
            padding: 15px 25px;
            background: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .report-author {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .author-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .btn-delete-sm {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-delete-sm:hover {
            background: #e74c3c;
            color: white;
            transform: scale(1.1);
        }

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

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
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
            <a class="nav-link" href="../requests/index.php">
                <i class="bi bi-inbox"></i>
                <span>Requests</span>
            </a>
        </div>
        <div class="nav-item">
            <a class="nav-link active" href="index.php">
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
        <h2><i class="bi bi-graph-up me-2 text-danger"></i>Reports</h2>
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION['user'], 0, 1)); ?>
            </div>
            <span><?php echo htmlspecialchars($_SESSION['user']); ?></span>
        </div>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4 animate-in">
            <h5 class="text-muted mb-0">Manage Reports</h5>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="bi bi-plus-lg me-2"></i>New Report
            </button>
        </div>

        <div class="row">
            <?php foreach ($reports as $index => $r): 
                $typeClass = 'type-other';
                if ($r['type'] == 'monthly') $typeClass = 'type-monthly';
                elseif ($r['type'] == 'annual') $typeClass = 'type-annual';
                elseif ($r['type'] == 'incident') $typeClass = 'type-incident';
            ?>
            <div class="col-md-6 col-lg-4 mb-4 animate-in delay-<?php echo ($index % 3) + 1; ?>">
                <div class="report-card">
                    <div class="report-header">
                        <span class="report-type <?php echo $typeClass; ?>">
                            <i class="bi bi-<?php 
                                echo $r['type'] == 'monthly' ? 'calendar-month' : 
                                     ($r['type'] == 'annual' ? 'calendar' : 
                                     ($r['type'] == 'incident' ? 'exclamation-circle' : 'file-text')); 
                            ?> me-1"></i>
                            <?php echo ucfirst($r['type']); ?>
                        </span>
                        <small class="text-muted"><?php echo date('M d, Y', strtotime($r['created_at'])); ?></small>
                    </div>
                    <div class="report-body">
                        <div class="report-title"><?php echo htmlspecialchars($r['title']); ?></div>
                        <div class="report-content"><?php echo nl2br(htmlspecialchars(substr($r['content'], 0, 150))); ?>...</div>
                    </div>
                    <div class="report-footer">
                        <div class="report-author">
                            <div class="author-avatar">
                                <?php echo strtoupper(substr($r['author'] ?? 'U', 0, 1)); ?>
                            </div>
                            <span><?php echo htmlspecialchars($r['author'] ?? 'Unknown'); ?></span>
                        </div>
                        <a href="?delete=<?php echo $r['id']; ?>" class="btn-delete-sm" onclick="return confirm('Delete this report?')" title="Delete">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($reports)): ?>
            <div class="col-12 text-center py-5 text-muted animate-in">
                <i class="bi bi-journal-text fs-1 d-block mb-3"></i>
                <h4>No reports yet</h4>
                <p>Create your first report to get started</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-journal-plus me-2"></i>New Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Title</label>
                        <input type="text" name="title" class="form-control" placeholder="Enter report title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Type</label>
                        <select name="type" class="form-select">
                            <option value="monthly">Monthly</option>
                            <option value="annual">Annual</option>
                            <option value="incident">Incident</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Content</label>
                        <textarea name="content" class="form-control" rows="10" placeholder="Enter report content..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>Save Report
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