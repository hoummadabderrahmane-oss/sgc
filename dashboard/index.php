<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

require "../config/database.php";

// Stats
$citizens_count = $pdo->query("SELECT COUNT(*) FROM citizens")->fetchColumn();
$documents_count = $pdo->query("SELECT COUNT(*) FROM documents")->fetchColumn();
$pending_requests = $pdo->query("SELECT COUNT(*) FROM requests WHERE status = 'pending'")->fetchColumn();
$reports_count = $pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn();

// Recent data
$recent_citizens = $pdo->query("SELECT * FROM citizens ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recent_requests = $pdo->query("SELECT r.*, c.fullname as citizen_name FROM requests r LEFT JOIN citizens c ON r.citizen_id = c.id ORDER BY r.created_at DESC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CMS Baladiya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            z-index: 1000;
            transition: all 0.3s ease;
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

        .nav-menu {
            padding: 20px 0;
        }

        .nav-item {
            padding: 0 15px;
            margin-bottom: 5px;
        }

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

        .nav-link.logout {
            color: #ff6b6b;
        }

        .nav-link.logout:hover {
            background: linear-gradient(135deg, #ff6b6b, #ee5a5a);
            color: white;
        }

        /* Main Content */
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

        .content {
            padding: 30px;
        }

        /* Stats Cards */
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }

        .stat-card.citizens::before { background: linear-gradient(180deg, #3498db, #2980b9); }
        .stat-card.documents::before { background: linear-gradient(180deg, #2ecc71, #27ae60); }
        .stat-card.requests::before { background: linear-gradient(180deg, #f39c12, #e67e22); }
        .stat-card.reports::before { background: linear-gradient(180deg, #e74c3c, #c0392b); }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }

        .stat-card.citizens .stat-icon { background: rgba(52, 152, 219, 0.1); color: #3498db; }
        .stat-card.documents .stat-icon { background: rgba(46, 204, 113, 0.1); color: #2ecc71; }
        .stat-card.requests .stat-icon { background: rgba(243, 156, 18, 0.1); color: #f39c12; }
        .stat-card.reports .stat-icon { background: rgba(231, 76, 60, 0.1); color: #e74c3c; }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        /* Tables */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #f1f2f6;
            padding: 20px 25px;
            border-radius: 16px 16px 0 0 !important;
        }

        .card-header h5 {
            margin: 0;
            font-weight: 700;
            color: #2c3e50;
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

        .badge {
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 500;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: fadeInUp 0.6s ease forwards;
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-wrapper {
                margin-left: 0;
            }
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
            <a class="nav-link active" href="index.php">
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
        <h2>Dashboard</h2>
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION['user'], 0, 1)); ?>
            </div>
            <span><?php echo htmlspecialchars($_SESSION['user']); ?></span>
        </div>
    </div>

    <div class="content">
        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-3 animate-in delay-1">
                <div class="stat-card citizens">
                    <div class="stat-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-number"><?php echo $citizens_count; ?></div>
                    <div class="stat-label">Total Citizens</div>
                </div>
            </div>
            <div class="col-md-3 animate-in delay-2">
                <div class="stat-card documents">
                    <div class="stat-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>
                    <div class="stat-number"><?php echo $documents_count; ?></div>
                    <div class="stat-label">Documents</div>
                </div>
            </div>
            <div class="col-md-3 animate-in delay-3">
                <div class="stat-card requests">
                    <div class="stat-icon">
                        <i class="bi bi-inbox"></i>
                    </div>
                    <div class="stat-number"><?php echo $pending_requests; ?></div>
                    <div class="stat-label">Pending Requests</div>
                </div>
            </div>
            <div class="col-md-3 animate-in delay-4">
                <div class="stat-card reports">
                    <div class="stat-icon">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <div class="stat-number"><?php echo $reports_count; ?></div>
                    <div class="stat-label">Reports</div>
                </div>
            </div>
        </div>

        <div class="row animate-in delay-4">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-people me-2 text-primary"></i>Recent Citizens</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>CIN</th>
                                    <th>Phone</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_citizens as $c): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                <?php echo strtoupper(substr($c['fullname'], 0, 1)); ?>
                                            </div>
                                            <?php echo htmlspecialchars($c['fullname']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($c['cin']); ?></td>
                                    <td><?php echo htmlspecialchars($c['phone']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-inbox me-2 text-warning"></i>Recent Requests</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_requests as $r): ?>
                                <tr>
                                    <td><?php echo str_replace('_', ' ', $r['type']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $r['status'] == 'pending' ? 'warning' : 
                                                 ($r['status'] == 'completed' ? 'success' : 'info'); 
                                        ?>"><?php echo $r['status']; ?></span>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($r['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>