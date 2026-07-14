<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/session.php';

if (isLoggedIn()) {
    header('Location: /dashboard/');
    exit;
}

// Detect base path
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = dirname(dirname($scriptName));
$basePath = str_replace('\\', '/', $basePath);
if ($basePath == '/' || $basePath == '\\') $basePath = '';
$assetsPath = $basePath . '/assets';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'));
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } elseif (!isset($pdo) || $pdo === null) {
        $error = 'Database connection failed';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 'active'");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && passsword_verify($password , $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['department'] = $user['department'];
                
                $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
                setFlash('success', 'Welcome back, ' . $user['full_name'] . '!');
                header('Location: ' . $basePath . '/dashboard/');
                exit;
            } else {
                $error = 'Invalid username or password';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CMS Baladiya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= $assetsPath ?>/css/style.css">
</head>
<body>
    <div class="login-page">
        <div class="login-card">
            <div class="text-center">
                <div class="login-logo">
                    <i class="bi bi-building"></i>
                </div>
                <h2 class="fw-bold mb-1">CMS Baladiya</h2>
                <p class="text-muted mb-4">Municipality Management System</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger mb-4">
                    <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label text-muted small fw-semibold text-uppercase">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="admin" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label text-muted small fw-semibold text-uppercase">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="admin123" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-3 mb-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Sign In
                </button>
            </form>
            
            <div class="text-center">
                <small class="text-muted">Default: <span class="text-primary">admin</span> / <span class="text-primary">admin123</span></small>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $assetsPath ?>/js/main.js"></script>
</body>
</html>
