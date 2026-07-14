```php
<?php
/**
 * ==========================================================
 * SGC v1.0
 * create_admin.php
 * Exécuter UNE seule fois puis supprimer le fichier.
 * ==========================================================
 */

require_once "config/database.php";

// معلومات الأدمن
$fullname = "Administrateur";
$email    = "admin@baladiya.com";
$password = "admin123"; // غيّرها قبل التشغيل إذا بغيت
$role     = "admin";
$status   = "active";

// واش الإيميل موجود؟
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->fetch()) {
    die("❌ L'administrateur existe déjà.");
}

// تشفير كلمة المرور
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// إضافة المستخدم
$stmt = $pdo->prepare("
    INSERT INTO users (
        fullname,
        email,
        password,
        role,
        status
    ) VALUES (?, ?, ?, ?, ?)
");

$success = $stmt->execute([
    $fullname,
    $email,
    $hashedPassword,
    $role,
    $status
]);

if ($success) {

    echo "<h2 style='color:green'>
            ✅ Administrateur créé avec succès.
          </h2>";

    echo "<p>Email : <strong>$email</strong></p>";
    echo "<p>Mot de passe : <strong>$password</strong></p>";

    echo "<hr>";
    echo "<b>⚠️ Supprimez maintenant le fichier create_admin.php pour des raisons de sécurité.</b>";

} else {

    echo "Erreur lors de la création.";

}
?>
```
