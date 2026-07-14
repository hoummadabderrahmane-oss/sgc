<?php
require_once "../auth/auth_check.php";
?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Dashboard | SGC</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<style>

body{
background:#f4f6f9;
}

.sidebar{

width:250px;

height:100vh;

background:#0B6E4F;

position:fixed;

left:0;

top:0;

color:white;

}

.sidebar h4{

padding:20px;

text-align:center;

}

.sidebar a{

display:block;

padding:15px 20px;

color:white;

text-decoration:none;

}

.sidebar a:hover{

background:#09553d;

}

.content{

margin-left:250px;

padding:30px;

}

.card{

border:none;

border-radius:15px;

box-shadow:0 4px 15px rgba(0,0,0,.08);

}

</style>

</head>

<body>

<div class="sidebar">

<h4>SGC</h4>

<a href="#">
<i class="fa fa-house"></i>
 Dashboard
 </a>

 <a href="#">
 <i class="fa fa-users"></i>
  Citoyens
  </a>

  <a href="#">
  <i class="fa fa-file-lines"></i>
   Documents
   </a>

   <a href="#">
   <i class="fa fa-user-gear"></i>
    Utilisateurs
    </a>

    <a href="../auth/logout.php">
    <i class="fa fa-right-from-bracket"></i>
     Déconnexion
     </a>

     </div>

     <div class="content">

     <div class="d-flex justify-content-between align-items-center">

     <div>

     <h2>Bienvenue,
     <?= htmlspecialchars($_SESSION['full_name']) ?>

     </h2>

     <p class="text-muted">

     Système de Gestion Communale

     </p>

     </div>

     </div>

     <div class="row mt-4">

     <div class="col-md-3">

     <div class="card">

     <div class="card-body">

     <h5>👥 Citoyens</h5>

     <h2>0</h2>

     </div>

     </div>

     </div>

     <div class="col-md-3">

     <div class="card">

     <div class="card-body">

     <h5>📄 Documents</h5>

     <h2>0</h2>

     </div>

     </div>

     </div>

     <div class="col-md-3">

     <div class="card">

     <div class="card-body">

     <h5>👤 Utilisateurs</h5>

     <h2>1</h2>

     </div>

     </div>

     </div>

     <div class="col-md-3">

     <div class="card">

     <div class="card-body">

     <h5>📝 Activités</h5>

     <h2>0</h2>

     </div>

     </div>

     </div>

     </div>

     </div>

     </body>

     </html>