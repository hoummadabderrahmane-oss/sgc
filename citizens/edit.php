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
$citizen = $stmt->fetch();

if (!$citizen) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['update'])) {
    $stmt = $pdo->prepare("UPDATE citizens SET fullname=?, cin=?, phone=?, email=?, address=?, birth_date=?, gender=? WHERE id=?");
    $stmt->execute([
        $_POST['fullname'], $_POST['cin'], $_POST['phone'], 
        $_POST['email'], $_POST['address'], $_POST['birth_date'], 
        $_POST['gender'], $id
    ]);
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Citizen - CMS Baladiya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .edit-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .edit-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .edit-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px;
            text-align: center;
        }

        .edit-header i {
            font-size: 3rem;
            margin-bottom: 10px;
        }

        .edit-header h2 {
            margin: 0;
            font-weight: 700;
        }

        .edit-body {
            padding: 40px;
        }

        .form-control, .form-select {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .btn-save {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 12px;
            padding: 14px 40px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-cancel {
            border-radius: 12px;
            padding: 14px 40px;
            font-weight: 600;
        }

        .top-nav {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
    </style>
</head>
<body>

<div class="top-nav">
    <div class="d-flex justify-content-between align-items-center">
        <a href="index.php" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-2"></i>Back to Citizens
        </a>
        <div class="user-info d-flex align-items-center gap-2">
            <div class="rounded-circle bg-gradient-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea, #764ba2);">
                <?php echo strtoupper(substr($_SESSION['user'], 0, 1)); ?>
            </div>
            <span><?php echo htmlspecialchars($_SESSION['user']); ?></span>
        </div>
    </div>
</div>

<div class="edit-container">
    <div class="edit-card animate-in">
        <div class="edit-header">
            <i class="bi bi-person-gear"></i>
            <h2>Edit Citizen</h2>
            <p class="mb-0 opacity-75">Update citizen information</p>
        </div>
        <div class="edit-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Full Name *</label>
                        <input type="text" name="fullname" class="form-control" value="<?php echo htmlspecialchars($citizen['fullname']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label">CIN</label>
                        <input type="text" name="cin" class="form-control" value="<?php echo htmlspecialchars($citizen['cin']); ?>">
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($citizen['phone']); ?>">
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($citizen['email']); ?>">
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Birth Date</label>
                        <input type="date" name="birth_date" class="form-control" value="<?php echo $citizen['birth_date']; ?>">
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="male" <?php echo $citizen['gender'] == 'male' ? 'selected' : ''; ?>>Male</option>
                            <option value="female" <?php echo $citizen['gender'] == 'female' ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                    <div class="col-12 mb-4">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($citizen['address']); ?></textarea>
                    </div>
                </div>
                <div class="d-flex gap-3">
                    <button type="submit" name="update" class="btn btn-save">
                        <i class="bi bi-check-lg me-2"></i>Update Citizen
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>