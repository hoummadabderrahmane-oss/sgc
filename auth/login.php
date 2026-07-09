<?php
session_start();
require "../config/database.php";

$message = "";

if(isset($_POST['login'])){

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
    $stmt->execute([$email, $password]);
    echo "Email: " . $email . "<br>";
echo "Password: " . $password . "<br>";

echo "Rows found: " . $stmt->rowCount();
exit();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user){

        $_SESSION['user'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];

        header("Location: ../dashboard/index.php");
        exit();

    } else {
        $message = "Invalid Email or Password";
    }
}


?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container">

<div class="row justify-content-center mt-5">

<div class="col-md-4">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h4>CMS Baladiya Login</h4>

</div>

<div class="card-body">

<?php if($message!=""){ ?>

<div class="alert alert-danger">
<?= $message ?>
</div>

<?php } ?>

<form method="POST">

<div class="mb-3">

<label>Email</label>

<input
type="email"
name="email"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Password</label>

<input
type="password"
name="password"
class="form-control"
required>

</div>

<button
name="login"
class="btn btn-primary w-100">

Login

</button>

</form>

</div>

</div>

</div>

</div>

</div>

</body>
</html>