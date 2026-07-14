<?php

session_start();


// If user already logged in
if(isset($_SESSION['user'])){

    header("Location: admin/dashboard.php");
    exit();

}else{

    header("Location: auth/login.php");
    exit();

}

?>