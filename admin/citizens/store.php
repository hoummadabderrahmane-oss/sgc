<?php
/**
 * ============================================
  * SGC - Traitement Ajout Citoyen
   * ============================================
    */
    define('SGC_ACCESS', true);
    require_once '../auth/auth_check.php';
    require_once '../config/database.php';

    global $currentUser;

    // Vérifier que c'est bien un POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ajouter.php');
            exit;
            }

            // Récupérer les données
            $cin = trim($_POST['cin'] ?? '');
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $nom_ar = trim($_POST['nom_ar'] ?? '');
            $prenom_ar = trim($_POST['prenom_ar'] ?? '');
            $date_naissance = !empty($_POST['date_naissance']) ? $_POST['date_naissance'] : null;
            $lieu_naissance = trim($_POST['lieu_naissance'] ?? '');
            $sexe = $_POST['sexe'] ?? '';
            $etat_civil = $_POST['etat_civil'] ?? 'celibataire';
            $adresse = trim($_POST['adresse'] ?? '');
            $quartier = trim($_POST['quartier'] ?? '');
            $telephone = trim($_POST['telephone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $profession = trim($_POST['profession'] ?? '');
            $niveau_etude = trim($_POST['niveau_etude'] ?? '');
            $situation_sociale = $_POST['situation_sociale'] ?? 'normal';
            $nombre_enfants = (int)($_POST['nombre_enfants'] ?? 0);
            $notes = trim($_POST['notes'] ?? '');

            // Validation
            $errors = [];
            if (empty($cin)) $errors[] = "Le CIN est obligatoire";
            if (empty($nom)) $errors[] = "Le nom est obligatoire";
            if (empty($prenom)) $errors[] = "Le prénom est obligatoire";
            if (empty($sexe)) $errors[] = "Le sexe est obligatoire";

            if (!empty($errors)) {
                $_SESSION['error'] = implode("<br>", $errors);
                    header('Location: ajouter.php');
                        exit;
                        }

                        try {
                            $db = getDB();
                                
                                    // Vérifier CIN unique
                                        $stmt = $db->prepare("SELECT id FROM citoyens WHERE cin = ?");
                                            $stmt->execute([$cin]);
                                                if ($stmt->fetch()) {
                                                        $_SESSION['error'] = "Ce CIN existe déjà dans le système";
                                                                header('Location: ajouter.php');
                                                                        exit;
                                                                            }
                                                                                
                                                                                    $stmt = $db->prepare("
                                                                                            INSERT INTO citoyens (
                                                                                                        cin, nom, prenom, nom_ar, prenom_ar, date_naissance, lieu_naissance,
                                                                                                                    sexe, etat_civil, adresse, quartier, telephone, email, profession,
                                                                                                                                niveau_etude, situation_sociale, nombre_enfants, notes, created_by
                                                                                                                                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                                                                                                                                            ");
                                                                                                                                                
                                                                                                                                                    $stmt->execute([
                                                                                                                                                            $cin, $nom, $prenom, $nom_ar, $prenom_ar, $date_naissance, $lieu_naissance,
                                                                                                                                                                    $sexe, $etat_civil, $adresse, $quartier, $telephone, $email, $profession,
                                                                                                                                                                            $niveau_etude, $situation_sociale, $nombre_enfants, $notes, $currentUser['id']
                                                                                                                                                                                ]);
                                                                                                                                                                                    
                                                                                                                                                                                        $newId = $db->lastInsertId();
                                                                                                                                                                                            logActivity('ajout_citoyen', 'citoyens', $newId, "Citoyen: $prenom $nom");
                                                                                                                                                                                                
                                                                                                                                                                                                    $_SESSION['success'] = "Citoyen ajouté avec succès!";
                                                                                                                                                                                                        header('Location: index.php');
                                                                                                                                                                                                            exit;
                                                                                                                                                                                                                
                                                                                                                                                                                                                } catch (PDOException $e) {
                                                                                                                                                                                                                    $_SESSION['error'] = "Erreur lors de l'ajout: " . $e->getMessage();
                                                                                                                                                                                                                        error_log("Erreur ajout citoyen: " . $e->getMessage());
                                                                                                                                                                                                                            header('Location: ajouter.php');
                                                                                                                                                                                                                                exit;
                                                                                                                                                                                                                                }
                                                                                                                                                                                                                                