<?php
/**
 * ============================================
 * SGC - Page de connexion
 * ============================================
 */
define('SGC_ACCESS', true);
session_start();

// Rediriger si déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: admin/dashboard.php');
    exit;
}

require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("
                SELECT id, nom, prenom, email, mot_de_passe, role, commune, statut, avatar 
                FROM utilisateurs 
                WHERE email = :email AND statut = 1
                LIMIT 1
            ");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['mot_de_passe'])) {
                // Connexion réussie
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['nom']        = $user['nom'];
                $_SESSION['prenom']     = $user['prenom'];
                $_SESSION['email']      = $user['email'];
                $_SESSION['role']       = $user['role'];
                $_SESSION['commune']    = $user['commune'];
                $_SESSION['avatar']     = $user['avatar'];
                $_SESSION['login_time'] = time();
                
                // Mettre à jour la dernière connexion
                $db->prepare("UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = :id")
                   ->execute([':id' => $user['id']]);
                
                logActivity('connexion');
                
                header('Location: admin/dashboard.php');
                exit;
            } else {
                $error = 'Email ou mot de passe incorrect.';
                logActivity('echec_connexion', null, null, "Tentative: $email");
            }
        } catch (PDOException $e) {
            $error = 'Erreur système. Veuillez réessayer.';
            error_log("Erreur login: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGC - Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a5f2a;
            --primary-light: #2d8a3e;
            --primary-dark: #0d3d16;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 50%, var(--primary-light) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 3rem;
            width: 100%;
            max-width: 420px;
            backdrop-filter: blur(10px);
        }
        
        .login-logo {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 8px 25px rgba(26, 95, 42, 0.3);
        }
        
        .login-logo i {
            font-size: 2.5rem;
            color: white;
        }
        
        .login-title {
            color: var(--primary-dark);
            font-weight: 700;
            text-align: center;
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            color: #6c757d;
            text-align: center;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }
        
        .form-control {
            border-radius: 12px;
            padding: 0.85rem 1rem 0.85rem 3rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(26, 95, 42, 0.15);
        }
        
        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            z-index: 10;
        }
        
        .input-group {
            position: relative;
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            border: none;
            border-radius: 12px;
            padding: 0.85rem;
            font-weight: 600;
            font-size: 1rem;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(26, 95, 42, 0.3);
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26, 95, 42, 0.4);
        }
        
        .footer-text {
            text-align: center;
            color: rgba(255,255,255,0.8);
            margin-top: 2rem;
            font-size: 0.85rem;
        }
        
        .alert-custom {
            border-radius: 12px;
            border: none;
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="login-logo">
                <i class="fas fa-city"></i>
            </div>
            <h2 class="login-title">SGC</h2>
            <p class="login-subtitle">Système de Gestion des Citoyens</p>
            
            <?php if ($error): ?>
                <div class="alert alert-custom alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="loginForm">
                <div class="mb-3 input-group">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" class="form-control" name="email" id="email" 
                           placeholder="Adresse email" required autofocus
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                
                <div class="mb-4 input-group">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" class="form-control" name="password" id="password" 
                           placeholder="Mot de passe" required>
                </div>
                
                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                </button>
            </form>
            
            <div class="text-center mt-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Par défaut: admin@commune.ma / password
                </small>
            </div>
        </div>
        
        <p class="footer-text">
            <i class="fas fa-code me-1"></i> SGC v1.0 - Système de Gestion des Citoyens
        </p>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animation au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const card = document.querySelector('.login-card');
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>