<?php
/**
 * ============================================
 * CMS Baladiya - Modifier Document
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

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
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

$stmt = $pdo->prepare("SELECT * FROM documents WHERE id = :id");
$stmt->execute([':id' => $id]);
$doc = $stmt->fetch();

if (!$doc) {
    header('Location: index.php');
    exit;
}

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
                UPDATE documents 
                SET citoyen_id = :citoyen_id, type_document = :type_document, 
                    numero_document = :numero_document, date_emission = :date_emission, 
                    date_expiration = :date_expiration, notes = :notes
                WHERE id = :id
            ");
            $stmt->execute([
                ':citoyen_id' => $citoyen_id,
                ':type_document' => $type_document,
                ':numero_document' => $numero_document,
                ':date_emission' => $date_emission,
                ':date_expiration' => $date_expiration ?: null,
                ':notes' => $notes,
                ':id' => $id
            ]);

            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Document modifie avec succes.'];
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Erreur lors de la modification : ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Modifier document';
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
</style>

<div class="container-fluid py-4">

    <div class="page-header-modern">
        <div>
            <div class="breadcrumb-text mb-1">
                <i class="bi bi-house-door me-1"></i> Tableau de bord
                <i class="bi bi-chevron-right mx-2" style="font-size:0.7rem"></i>
                <a href="index.php" class="text-decoration-none text-muted">Documents</a>
                <i class="bi bi-chevron-right mx-2" style="font-size:0.7rem"></i>
                Modifier
            </div>
            <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Modifier le document</h4>
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
                    <div class="doc-type-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">Modification du document</h5>
                        <small class="text-muted">N <?= htmlspecialchars($doc['numero_document'] ?? '') ?></small>
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
                                        <option value="<?= $c['id'] ?>" <?= ($doc['citoyen_id'] == $c['id']) ? 'selected' : '' ?>>
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
                                        <option value="<?= $k ?>" <?= ($doc['type_document'] === $k) ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-hash me-1"></i>Numero de document <span class="text-danger">*</span></label>
                                <input type="text" name="numero_document" class="form-control" 
                                       value="<?= htmlspecialchars($doc['numero_document'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><i class="bi bi-calendar-check me-1"></i>Date d emission <span class="text-danger">*</span></label>
                                <input type="date" name="date_emission" class="form-control" 
                                       value="<?= htmlspecialchars($doc['date_emission'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><i class="bi bi-calendar-x me-1"></i>Date d expiration</label>
                                <input type="date" name="date_expiration" class="form-control" 
                                       value="<?= htmlspecialchars($doc['date_expiration'] ?? '') ?>">
                                <small class="text-muted">Laissez vide si sans expiration</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label"><i class="bi bi-sticky me-1"></i>Notes / Observations</label>
                                <textarea name="notes" class="form-control" rows="4" 
                                          placeholder="Informations complementaires..."><?= htmlspecialchars($doc['notes'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-form-submit">
                                <i class="bi bi-check-lg me-2"></i>Enregistrer les modifications
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