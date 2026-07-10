<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

require "../config/database.php";

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM citizens WHERE id = ?");
$stmt->execute([$id]);
$c = $stmt->fetch();

if (!$c) {
    header("Location: index.php");
    exit();
}

$docs = $pdo->prepare("SELECT * FROM documents WHERE citizen_id = ?");
$docs->execute([$id]);
$documents = $docs->fetchAll();

$reqs = $pdo->prepare("SELECT * FROM requests WHERE citizen_id = ?");
$reqs->execute([$id]);
$requests = $reqs->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Citizen - CMS Baladiya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }

        .top-nav {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .profile-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .profile-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .profile-cin {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .info-list {
            padding: 30px;
        }

        .info-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f1f2f6;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-right: 15px;
        }

        .info-label {
            color: #7f8c8d;
            font-size: 0.85rem;
            margin-bottom: 3px;
        }

        .info-value {
            font-weight: 600;
            color: #2c3e50;
        }

        .section-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .section-header {
            padding: 20px 25px;
            border-bottom: 1px solid #f1f2f6;
            display: flex;
            align-items: center;
        }

        .section-header i {
            font-size: 1.3rem;
            margin-right: 10px;
            color: var(--primary-color);
        }

        .section-header h5 {
            margin: 0;
            font-weight: 700;
        }

        .badge-custom {
            padding: 8px 14px;
            border-radius: 10px;
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="top-nav">
    <div class="d-flex justify-content-between align-items-center">
        <a href="index.php" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>Back to Citizens
        </a>
        <a href="edit.php?id=<?php echo $c['id']; ?>" class="btn btn-warning">
            <i class="bi bi-pencil me-2"></i>Edit Citizen
        </a>
    </div>
</div>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($c['fullname'], 0, 1)); ?>
                    </div>
                    <div class="profile-name"><?php echo htmlspecialchars($c['fullname']); ?></div>
                    <div class="profile-cin"><i class="bi bi-card-text me-2"></i><?php echo htmlspecialchars($c['cin']); ?></div>
                </div>
                <div class="info-list">
                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-telephone"></i></div>
                        <div>
                            <div class="info-label">Phone</div>
                            <div class="info-value"><?php echo htmlspecialchars($c['phone']); ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-envelope"></i></div>
                        <div>
                            <div class="info-label">Email</div>
                            <div class="info-value"><?php echo htmlspecialchars($c['email']); ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-calendar"></i></div>
                        <div>
                            <div class="info-label">Birth Date</div>
                            <div class="info-value"><?php echo $c['birth_date']; ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-gender-ambiguous"></i></div>
                        <div>
                            <div class="info-label">Gender</div>
                            <div class="info-value">
                                <span class="badge bg-<?php echo $c['gender'] == 'male' ? 'primary' : 'danger'; ?>">
                                    <?php echo ucfirst($c['gender']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-geo-alt"></i></div>
                        <div>
                            <div class="info-label">Address</div>
                            <div class="info-value"><?php echo htmlspecialchars($c['address']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <div class="section-card mb-4">
                <div class="section-header">
                    <i class="bi bi-file-earmark-text"></i>
                    <h5>Documents (<?php echo count($documents); ?>)</h5>
                </div>
                <div class="p-4">
                    <?php if (empty($documents)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-folder2-open fs-1 mb-3 d-block"></i>
                        No documents found
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr><th>Title</th><th>Type</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($documents as $d): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($d['title']); ?></td>
                                    <td><?php echo str_replace('_', ' ', $d['type']); ?></td>
                                    <td>
                                        <span class="badge-custom bg-<?php echo $d['status'] == 'approved' ? 'success' : 'warning'; ?> text-white">
                                            <?php echo $d['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="section-card">
                <div class="section-header">
                    <i class="bi bi-inbox"></i>
                    <h5>Requests (<?php echo count($requests); ?>)</h5>
                </div>
                <div class="p-4">
                    <?php if (empty($requests)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-1 mb-3 d-block"></i>
                        No requests found
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr><th>Type</th><th>Status</th><th>Date</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $r): ?>
                                <tr>
                                    <td><?php echo str_replace('_', ' ', $r['type']); ?></td>
                                    <td>
                                        <span class="badge-custom bg-<?php echo $r['status'] == 'completed' ? 'success' : 'warning'; ?> text-white">
                                            <?php echo $r['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d', strtotime($r['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>