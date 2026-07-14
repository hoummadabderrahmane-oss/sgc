<?php

require "config/database.php";


$name = "Administrator";
$email = "admin@sgc.com";
$password = "admin123";
$role = "admin";


// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);


// Check if admin exists
$check = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$check->execute([$email]);


if($check->rowCount() > 0){

    echo "Admin already exists";

}else{


    $sql = "INSERT INTO users 
            (name,email,password,role)
            VALUES (?,?,?,?)";


    $stmt = $pdo->prepare($sql);


    $stmt->execute([
        $name,
        $email,
        $hashedPassword,
        $role
    ]);


    echo "
    Admin created successfully <br><br>
    Email: admin@sgc.com <br>
    Password: admin123
    ";

}

?>