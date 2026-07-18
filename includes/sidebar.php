<?php
/**
 * ============================================
 * CMS Baladiya - Sidebar (Self-Contained)
 * Works on ANY page without auth_check.php
 * ============================================
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Build currentUser from session (works even without auth_check.php)
$currentUser = [
    'id'      => $_SESSION['user_id'] ?? null,
    'nom'     => $_SESSION['nom'] ?? '',
    'prenom'  => $_SESSION['prenom'] ?? '',
    'email'   => $_SESSION['email'] ?? '',
    'role'    => $_SESSION['role'] ?? '',
    'commune' => $_SESSION['commune'] ?? '',
    'avatar'  => $_SESSION['avatar'] ?? 'default.png'
];

// Permission helpers - only define if not already defined by auth_check.php
if (!function_exists('hasRole')) {
    function hasRole(string $role): bool {
        global $currentUser;
        return $currentUser['role'] === $role || $currentUser['role'] === 'super_admin';
    }
}

if (!function_exists('isSuperAdmin')) {
    function isSuperAdmin(): bool {
        global $currentUser;
        return $currentUser['role'] === 'super_admin';
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin(): bool {
        global $currentUser;
        return $currentUser['role'] === 'admin' || $currentUser['role'] === 'super_admin';
    }
}

// Navigation state
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

// Detect depth level for correct relative paths
$depth = substr_count($_SERVER['PHP_SELF'], '/') - 2;
$basePath = str_repeat('../', max(0, $depth - 1));
if (empty($basePath)) $basePath = '../';
?>
<nav class="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-landmark"></i>
        <h4>CMS Baladiya</h4>
        <small>Système de Gestion Municipale</small>
    </div>

    <div class="sidebar-menu">
        <a href="<?= $basePath ?>admin/dashboard.php" class="nav-link <?= ($currentPage == 'dashboard.php') ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Tableau de bord</span>
        </a>

        <a href="<?= $basePath ?>citizens/index.php" class="nav-link <?= ($currentDir == 'citizens') ? 'active' : '' ?>">
            <i class="fas fa-users"></i>
            <span>Gestion des Citoyens</span>
        </a>

        <a href="<?= $basePath ?>documents/index.php" class="nav-link <?= ($currentDir == 'documents') ? 'active' : '' ?>">
            <i class="fas fa-file-alt"></i>
            <span>Documents</span>
        </a>

        <a href="<?= $basePath ?>statistics/index.php" class="nav-link <?= ($currentDir == 'statistics') ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i>
            <span>Statistiques</span>
        </a>

        <?php if (isAdmin()): ?>
        <a href="#" class="nav-link">
            <i class="fas fa-user-shield"></i>
            <span>Utilisateurs</span>
        </a>

        <a href="#" class="nav-link">
            <i class="fas fa-history"></i>
            <span>Journal d activites</span>
        </a>

        <a href="#" class="nav-link">
            <i class="fas fa-cog"></i>
            <span>Parametres</span>
        </a>
        <?php endif; ?>
    </div>

    <div class="sidebar-footer">
        <i class="fas fa-code"></i> CMS Baladiya v1.0<br>
        <?= htmlspecialchars($currentUser['commune'] ?: 'Commune') ?>
    </div>
</nav>