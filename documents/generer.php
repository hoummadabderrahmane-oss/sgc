<?php
/**
 * ============================================
 * CMS Baladiya - Generer / Imprimer Document
 * ============================================
 */
session_start();
require_once __DIR__ . '/../config/database.php';
$pdo = getDB();

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Document invalide.'];
    header('Location: index.php');
    exit;
}

// Verify document exists
$stmt = $pdo->prepare("SELECT id FROM documents WHERE id = :id");
$stmt->execute([':id' => $id]);
if (!$stmt->fetch()) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Document introuvable.'];
    header('Location: index.php');
    exit;
}

// Redirect to attestation view for printing
header('Location: attestation.php?id=' . $id);
exit;