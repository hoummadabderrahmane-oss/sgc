<?php
/**
 * ============================================
 * SGC - Déconnexion
 * ============================================
 */
define('SGC_ACCESS', true);
session_start();

require_once '../config/database.php';

// Logger la déconnexion
if (isset($_SESSION['user_id'])) {
    logActivity('deconnexion');
}

// Détruire la session
$_SESSION = [];
session_unset();
session_destroy();

// Supprimer le cookie de session
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

header('Location: ../index.php');
exit;