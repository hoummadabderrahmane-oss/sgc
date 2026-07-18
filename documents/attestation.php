<?php
session_start();
require_once __DIR__ . '/../config/database.php';
$pdo = getDB();

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT d.*, c.nom, c.prenom, c.nom_ar, c.prenom_ar, c.cin, c.sexe,
                              c.date_naissance, c.lieu_naissance, c.adresse, c.quartier, c.etat_civil
                       FROM documents d
                       JOIN citoyens c ON c.id = d.citoyen_id
                       WHERE d.id = :id");
$stmt->execute([':id' => $id]);
$d = $stmt->fetch();

if (!$d) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Document introuvable.'];
    header('Location: index.php');
    exit;
}

function val($v): string {
    return htmlspecialchars(($v !== null && $v !== '') ? (string)$v : '—');
}
function dfr($v): string {
    return $v ? date('d/m/Y', strtotime($v)) : '—';
}

$typeLabels = [
    'extrait_naissance'    => 'Extrait de naissance',
    'certificat_residence' => 'Certificat de residence',
    'attestation_mariage'  => 'Attestation de mariage',
    'certificat_deces'     => 'Certificat de deces',
    'carte_identite'       => 'Carte d identite',
    'autre'                => 'Autre',
];
$etatCivil = ['celibataire' => 'Celibataire', 'marie' => 'Marie(e)', 'divorce' => 'Divorce(e)', 'veuf' => 'Veuf/Veuve'];

$commune   = $_SESSION['commune'] ?? '';
$adminName = trim(($_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['nom'] ?? ''));
$nomComplet = $d['nom'] . ' ' . $d['prenom'];
$sexe = $d['sexe'] === 'F' ? 'Feminin' : 'Masculin';

/* ---------- Titre + corps selon le type ---------- */
switch ($d['type_document']) {
    case 'extrait_naissance':
        $titre = 'EXTRAIT D ACTE DE NAISSANCE';
        $corps = 'Nous soussigne(e), <strong>' . val($adminName) . '</strong>, agissant au nom de la Commune de <strong>' . val($commune) . '</strong>,
                  certifions que <strong>' . val($nomComplet) . '</strong>, de sexe ' . strtolower($sexe) . ',
                  est ne(e) le <strong>' . dfr($d['date_naissance']) . '</strong> a <strong>' . val($d['lieu_naissance']) . '</strong>
                  et qu il/elle est titulaire de la CIN n <strong>' . val($d['cin']) . '</strong>,
                  conformement aux registres de l etat civil de la commune.';
        break;
    case 'certificat_residence':
        $titre = 'ATTESTATION DE RESIDENCE';
        $corps = 'Nous soussigne(e), <strong>' . val($adminName) . '</strong>, agissant au nom de la Commune de <strong>' . val($commune) . '</strong>,
                  attestons que <strong>' . val($nomComplet) . '</strong>, titulaire de la CIN n <strong>' . val($d['cin']) . '</strong>,
                  ne(e) le <strong>' . dfr($d['date_naissance']) . '</strong>,
                  reside effectivement a l adresse suivante : <strong>' . nl2br(val($d['adresse'])) . '</strong>,
                  quartier <strong>' . val($d['quartier']) . '</strong>, commune de <strong>' . val($commune) . '</strong>.';
        break;
    case 'attestation_mariage':
        $titre = 'ATTESTATION DE SITUATION MATRIMONIALE';
        $corps = 'Nous soussigne(e), <strong>' . val($adminName) . '</strong>, agissant au nom de la Commune de <strong>' . val($commune) . '</strong>,
                  attestons que <strong>' . val($nomComplet) . '</strong>, titulaire de la CIN n <strong>' . val($d['cin']) . '</strong>,
                  est de situation matrimoniale : <strong>' . ($etatCivil[$d['etat_civil']] ?? val($d['etat_civil'])) . '</strong>,
                  selon les informations enregistrees aupres de nos services.';
        break;
    case 'certificat_deces':
        $titre = 'CERTIFICAT DE DECES';
        $corps = 'Nous soussigne(e), <strong>' . val($adminName) . '</strong>, agissant au nom de la Commune de <strong>' . val($commune) . '</strong>,
                  certifions le deces de <strong>' . val($nomComplet) . '</strong>, titulaire de la CIN n <strong>' . val($d['cin']) . '</strong>,
                  ne(e) le <strong>' . dfr($d['date_naissance']) . '</strong> a <strong>' . val($d['lieu_naissance']) . '</strong>.'
                  . ($d['notes'] ? '<br>Observations : ' . nl2br(val($d['notes'])) : '');
        break;
    case 'carte_identite':
        $titre = 'ATTESTATION DE DEMANDE DE CARTE D IDENTITE';
        $corps = 'Nous soussigne(e), <strong>' . val($adminName) . '</strong>, agissant au nom de la Commune de <strong>' . val($commune) . '</strong>,
                  attestons que la demande de carte nationale d identite de <strong>' . val($nomComplet) . '</strong>,
                  titulaire de la CIN n <strong>' . val($d['cin']) . '</strong>, residant a <strong>' . val($d['adresse']) . '</strong>,
                  quartier <strong>' . val($d['quartier']) . '</strong>, a ete duement enregistree aupres de nos services.';
        break;
    default:
        $titre = 'ATTESTATION';
        $corps = 'Nous soussigne(e), <strong>' . val($adminName) . '</strong>, agissant au nom de la Commune de <strong>' . val($commune) . '</strong>,
                  delivrons la presente attestation concernant <strong>' . val($nomComplet) . '</strong>,
                  titulaire de la CIN n <strong>' . val($d['cin']) . '</strong>.'
                  . ($d['notes'] ? '<br>' . nl2br(val($d['notes'])) : '');
        break;
}

$dateEmission = $d['date_emission'] ?: date('Y-m-d', strtotime($d['created_at']));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $typeLabels[$d['type_document']] ?? 'Document' ?> — <?= val($d['numero_document']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%); min-height: 100vh; }
        .fiche { 
            position: relative; max-width: 800px; margin: 2rem auto; background: #fff; padding: 3rem; 
            border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .entete { text-align: center; border-bottom: 3px double #1a5f2a; padding-bottom: 1.5rem; margin-bottom: 2rem; }
        .entete h6 { margin: 3px 0; letter-spacing: 1px; font-size: 0.85rem; color: #333; }
        .entete h5 { color: #1a5f2a; font-weight: bold; margin-top: 10px; font-size: 1.1rem; }
        .titre-doc { text-align: center; margin: 2rem 0; font-weight: 700; color: #1a5f2a; text-decoration: underline; font-size: 1.3rem; }
        .corps { line-height: 2; text-align: justify; font-size: 1.05rem; }
        .watermark {
            position: absolute; top: 45%; left: 15%; transform: rotate(-28deg);
            font-size: 5rem; font-weight: 800; color: rgba(220, 53, 69, .12);
            pointer-events: none; z-index: 0;
        }
        .doc-meta-bar {
            display: flex; justify-content: space-between; align-items: center;
            background: #f8f9fa; padding: 12px 20px; border-radius: 12px; margin-bottom: 1.5rem;
        }
        .doc-meta-bar .badge { font-size: 0.85rem; padding: 8px 16px; border-radius: 20px; }
        .signature-block { text-align: right; margin-top: 3rem; }
        .signature-block .line { border-top: 1px solid #333; width: 250px; margin-left: auto; padding-top: 8px; }
        .action-bar {
            position: fixed; bottom: 0; left: 0; right: 0;
            background: white; padding: 16px 24px;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.08);
            display: flex; justify-content: center; gap: 12px;
            z-index: 1000;
        }
        .action-bar .btn { border-radius: 12px; padding: 10px 24px; font-weight: 600; }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .fiche { margin: 0; box-shadow: none; border-radius: 0; }
            .action-bar { display: none !important; }
        }
    </style>
</head>
<body>

<div class="fiche">
    <?php if ($d['statut'] !== 'valide'): ?>
        <div class="watermark"><?= $d['statut'] === 'annule' ? 'ANNULE' : 'EXPIRE' ?></div>
    <?php endif; ?>

    <?php if (!empty($_GET['new'])): ?>
        <div class="alert alert-success no-print rounded-3">
            <i class="bi bi-check-circle-fill me-2"></i>
            Document genere avec succes — n <strong><?= val($d['numero_document']) ?></strong>.
        </div>
    <?php endif; ?>

    <div class="entete">
        <h6>ROYAUME DU MAROC</h6>
        <h6>MINISTERE DE L INTERIEUR</h6>
        <h6>COMMUNE DE <?= mb_strtoupper(val($commune)) ?></h6>
        <h5>SYSTEME DE GESTION MUNICIPALE</h5>
    </div>

    <div class="doc-meta-bar no-print">
        <span><i class="bi bi-hash me-1"></i>N <strong><?= val($d['numero_document']) ?></strong></span>
        <span class="badge bg-<?= $d['statut'] === 'valide' ? 'success' : ($d['statut'] === 'expire' ? 'warning' : 'danger') ?>">
            <i class="bi bi-<?= $d['statut'] === 'valide' ? 'check-circle' : ($d['statut'] === 'expire' ? 'exclamation-triangle' : 'x-circle') ?> me-1"></i>
            <?= ucfirst($d['statut']) ?>
        </span>
    </div>

    <h5 class="titre-doc"><?= $titre ?></h5>

    <p class="corps"><?= $corps ?></p>

    <p class="corps mt-3">En foi de quoi, la presente attestation lui est delivree pour servir et valoir ce que de droit.</p>

    <div class="signature-block">
        <p class="mb-4">Fait a <?= val($commune) ?>, le <?= dfr($dateEmission) ?></p>
        <p class="mb-1"><strong>L Administrateur</strong></p>
        <div class="line"><?= val($adminName) ?></div>
    </div>
</div>

<!-- Fixed Action Bar -->
<div class="action-bar no-print">
    <a href="index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Retour a la liste
    </a>
    <a href="../admin/dashboard.php" class="btn btn-outline-primary">
        <i class="bi bi-house-door me-2"></i>Tableau de bord
    </a>
    <button onclick="window.print()" class="btn btn-success">
        <i class="bi bi-printer me-2"></i>Imprimer
    </button>
</div>

</body>
</html>