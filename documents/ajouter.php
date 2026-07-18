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

<!-- Select2 CSS for Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

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

    /* Select2 Custom Styling to match Bootstrap 5 */
    .select2-container--bootstrap-5 .select2-selection {
        border-radius: 12px !important;
        border: 1px solid #e9ecef !important;
        min-height: 46px !important;
        padding: 6px 12px !important;
    }
    .select2-container--bootstrap-5 .select2-selection:focus,
    .select2-container--bootstrap-5.select2-container--focus .select2-selection,
    .select2-container--bootstrap-5.select2-container--open .select2-selection {
        border-color: #198754 !important;
        box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.1) !important;
    }
    .select2-container--bootstrap-5 .select2-dropdown {
        border-radius: 12px !important;
        border: 1px solid #e9ecef !important;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
    }
    .select2-container--bootstrap-5 .select2-results__option {
        padding: 10px 14px !important;
        border-radius: 8px !important;
        margin: 2px 6px !important;
    }
    .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background-color: rgba(25, 135, 84, 0.1) !important;
        color: #198754 !important;
    }
    .select2-container--bootstrap-5 .select2-results__option--selected {
        background-color: #198754 !important;
        color: white !important;
    }
    .select2-container--bootstrap-5 .select2-search__field {
        border-radius: 8px !important;
        padding: 8px 12px !important;
    }
    .select2-container--bootstrap-5 .select2-search__field:focus {
        border-color: #198754 !important;
        box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.1) !important;
    }
    .citizen-option {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .citizen-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1a5f2a 0%, #2d8a3e 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .citizen-info {
        display: flex;
        flex-direction: column;
    }
    .citizen-name {
        font-weight: 600;
        font-size: 0.9rem;
    }
    .citizen-cin {
        font-size: 0.75rem;
        color: #6c757d;
    }
    .select2-selection__rendered .citizen-option {
        padding: 2px 0;
    }
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
                                <select name="citoyen_id" id="citoyen-select" class="form-select" required>
                                    <option value="">— Rechercher et selectionner un citoyen —</option>
                                    <?php foreach ($citoyens as $c): 
                                        $initials = strtoupper(substr($c['prenom'] ?? '', 0, 1) . substr($c['nom'] ?? '', 0, 1));
                                        $fullName = htmlspecialchars($c['prenom'] . ' ' . $c['nom']);
                                        $cin = htmlspecialchars($c['cin'] ?? '');
                                        $selected = ($_POST['citoyen_id'] ?? '') == $c['id'] ? 'selected' : '';
                                    ?>
                                        <option value="<?= $c['id'] ?>" data-avatar="<?= $initials ?>" data-cin="<?= $cin ?>" <?= $selected ?>>
                                            <?= $fullName ?> — CIN: <?= $cin ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Tapez pour rechercher par nom ou CIN</small>
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

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Custom template for rendering citizen options
    function formatCitizen(option) {
        if (!option.id) {
            return option.text;
        }
        var $option = $(option.element);
        var avatar = $option.data('avatar') || '??';
        var cin = $option.data('cin') || '';

        return $(`
            <div class="citizen-option">
                <div class="citizen-avatar">${avatar}</div>
                <div class="citizen-info">
                    <span class="citizen-name">${option.text.split(' — ')[0]}</span>
                    <span class="citizen-cin"><i class="bi bi-credit-card me-1"></i>${cin}</span>
                </div>
            </div>
        `);
    }

    function formatCitizenSelection(option) {
        if (!option.id) {
            return option.text;
        }
        var $option = $(option.element);
        var avatar = $option.data('avatar') || '??';
        var cin = $option.data('cin') || '';

        return $(`
            <div class="citizen-option">
                <div class="citizen-avatar">${avatar}</div>
                <div class="citizen-info">
                    <span class="citizen-name">${option.text.split(' — ')[0]}</span>
                    <span class="citizen-cin">${cin}</span>
                </div>
            </div>
        `);
    }

    $('#citoyen-select').select2({
        theme: 'bootstrap-5',
        placeholder: 'Rechercher un citoyen...',
        allowClear: true,
        width: '100%',
        language: {
            noResults: function() {
                return 'Aucun citoyen trouve';
            },
            searching: function() {
                return 'Recherche en cours...';
            }
        },
        templateResult: formatCitizen,
        templateSelection: formatCitizenSelection,
        escapeMarkup: function(markup) {
            return markup;
        }
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>