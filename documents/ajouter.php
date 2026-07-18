<?php
/**
 * ============================================
 * CMS Baladiya - Ajouter un Document
 * ============================================
 */
session_start();
require_once __DIR__ . '/../config/database.php';
$pdo = getDB();

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$errors = [];
$typeLabels = [
    'extrait_naissance' => 'Extrait de naissance',
    'certificat_residence' => 'Certificat de residence',
    'attestation_mariage' => 'Attestation de mariage',
    'certificat_deces' => 'Certificat de deces',
    'carte_identite' => 'Carte d identite',
    'autre' => 'Autre'
];

$typeIcons = [
    'extrait_naissance' => 'bi-file-earmark-person',
    'certificat_residence' => 'bi-house-door',
    'attestation_mariage' => 'bi-heart',
    'certificat_deces' => 'bi-file-earmark-x',
    'carte_identite' => 'bi-person-vcard',
    'autre' => 'bi-file-earmark'
];

// Get citizens for dropdown
$stmt = $pdo->query("SELECT id, nom, prenom, cin FROM citoyens WHERE statut = 'actif' ORDER BY nom, prenom");
$citoyens = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        die('CSRF token invalide');
    }

    $citoyen_id = (int)($_POST['citoyen_id'] ?? 0);
    $type_document = $_POST['type_document'] ?? '';
    $numero_document = trim($_POST['numero_document'] ?? '');
    $date_emission = $_POST['date_emission'] ?? '';
    $date_expiration = $_POST['date_expiration'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if ($citoyen_id <= 0) {
        $errors[] = 'Veuillez selectionner un citoyen.';
    }
    if (!array_key_exists($type_document, $typeLabels)) {
        $errors[] = 'Type de document invalide.';
    }
    if (empty($numero_document)) {
        $errors[] = 'Le numero de document est obligatoire.';
    }
    if (empty($date_emission)) {
        $errors[] = 'La date d emission est obligatoire.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO documents (citoyen_id, type_document, numero_document, date_emission, date_expiration, statut, notes, created_by, created_at)
                VALUES (:citoyen_id, :type_document, :numero_document, :date_emission, :date_expiration, 'valide', :notes, :created_by, NOW())
            ");
            $stmt->execute([
                ':citoyen_id' => $citoyen_id,
                ':type_document' => $type_document,
                ':numero_document' => $numero_document,
                ':date_emission' => $date_emission,
                ':date_expiration' => $date_expiration ?: null,
                ':notes' => $notes,
                ':created_by' => $_SESSION['user_id']
            ]);

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Document ajoute avec succes.'];
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de l ajout : ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Nouveau document';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .form-card { border-radius: 16px; overflow: hidden; }
    .form-card .card-header { background: white; border-bottom: 1px solid #f0f0f0; padding: 20px 24px; }
    .form-card .form-control, .form-card .form-select { border-radius: 12px; border: 1px solid #e9ecef; padding: 12px 16px; }
    .form-card .form-control:focus, .form-card .form-select:focus { border-color: #198754; box-shadow: 0 0 0 3px rgba(25,135,84,0.1); }
    .form-card .form-label { font-weight: 600; font-size: 0.9rem; color: #495057; }
    .btn-form-submit { border-radius: 12px; padding: 12px 28px; font-weight: 600; }
    .btn-dashboard-return { display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 12px; font-weight: 600; font-size: 0.9rem; transition: all 0.3s ease; background: linear-gradient(135deg, #1a5f2a 0%, #2d8a3e 100%); border: none; color: white; }
    .btn-dashboard-return:hover { transform: translateX(-4px); box-shadow: 0 4px 15px rgba(26,95,42,0.3); color: white; }
    .page-header-modern { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
    .page-header-modern h4 { font-weight: 700; color: #1a5f2a; }
    .breadcrumb-text { color: #6c757d; font-size: 0.85rem; }
    .doc-type-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
    .type-selector { display: flex; flex-wrap: wrap; gap: 10px; }
    .type-option { 
        flex: 1; min-width: 160px; 
        border: 2px solid #e9ecef; border-radius: 14px; padding: 16px; 
        text-align: center; cursor: pointer; transition: all 0.2s; 
        background: white;
    }
    .type-option:hover { border-color: #198754; transform: translateY(-2px); }
    .type-option.active { border-color: #198754; background: rgba(25,135,84,0.05); }
    .type-option i { font-size: 1.8rem; margin-bottom: 8px; display: block; }
    .type-option .type-name { font-size: 0.8rem; font-weight: 600; }
</style>

<div class="container-fluid py-4">

    <div class="page-header-modern">
        <div>
            <div class="breadcrumb-text mb-1">
                <i class="bi bi-house-door me-1"></i> Tableau de bord
                <i class="bi bi-chevron-right mx-2" style="font-size:0.7rem"></i>
                <a href="index.php" class="text-decoration-none text-muted">Documents</a>
                <i class="bi bi-chevron-right mx-2" style="font-size:0.7rem"></i>
                Nouveau
            </div>
            <h4 class="mb-0"><i class="bi bi-file-earmark-plus me-2"></i>Nouveau document</h4>
        </div>
        <a href="../admin/dashboard.php" class="btn btn-dashboard-return">
            <i class="bi bi-arrow-left-circle"></i>
            Retour au tableau de bord
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card form-card shadow-sm border-0">
                <div class="card-header d-flex align-items-center">
                    <div class="doc-type-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="bi bi-plus-lg"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">Creation d un document</h5>
                        <small class="text-muted">Remplissez les informations ci-dessous</small>
                    </div>
                </div>
                <div class="card-body p-4">
                    <?php if ($errors): ?>
                        <div class="alert alert-danger rounded-3">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>Veuillez corriger les erreurs suivantes :</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($errors as $e): ?>
                                    <li><?= htmlspecialchars($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-person me-1"></i>Citoyen <span class="text-danger">*</span></label>
                                <select name="citoyen_id" class="form-select" required>
                                    <option value="">— Selectionner un citoyen —</option>
                                    <?php foreach ($citoyens as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= ($_POST['citoyen_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['prenom'] . ' ' . $c['nom'] . ' (' . $c['cin'] . ')') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-file-earmark me-1"></i>Type de document <span class="text-danger">*</span></label>
                                <select name="type_document" class="form-select" required>
                                    <option value="">— Selectionner —</option>
                                    <?php foreach ($typeLabels as $k => $label): ?>
                                        <option value="<?= $k ?>" <?= ($_POST['type_document'] ?? '') === $k ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-hash me-1"></i>Numero de document <span class="text-danger">*</span></label>
                                <input type="text" name="numero_document" class="form-control" 
                                       placeholder="Ex: DOC-2026-0001" 
                                       value="<?= htmlspecialchars($_POST['numero_document'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><i class="bi bi-calendar-check me-1"></i>Date d emission <span class="text-danger">*</span></label>
                                <input type="date" name="date_emission" class="form-control" 
                                       value="<?= htmlspecialchars($_POST['date_emission'] ?? date('Y-m-d')) ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><i class="bi bi-calendar-x me-1"></i>Date d expiration</label>
                                <input type="date" name="date_expiration" class="form-control" 
                                       value="<?= htmlspecialchars($_POST['date_expiration'] ?? '') ?>">
                                <small class="text-muted">Laissez vide si sans expiration</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label"><i class="bi bi-sticky me-1"></i>Notes / Observations</label>
                                <textarea name="notes" class="form-control" rows="4" 
                                          placeholder="Informations complementaires..."></textarea>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-form-submit">
                                <i class="bi bi-check-lg me-2"></i>Enregistrer le document
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary btn-form-submit">
                                <i class="bi bi-x-lg me-2"></i>Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>