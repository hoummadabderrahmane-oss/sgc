if(isset($_POST['login'])){

    $email = $_POST['email'];
    $password = hash('sha256', $_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
    $stmt->execute([$email, $password]);

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