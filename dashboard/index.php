<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<title>Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<nav class="navbar navbar-dark bg-dark">
<div class="container-fluid">

<span class="navbar-brand">
CMS Baladiya
</span>

<a href="../auth/logout.php" class="btn btn-danger">
Logout
</a>

</div>
</nav>

<div class="container mt-5">

<h2>Dashboard</h2>

<hr>

<div class="row">

<div class="col-md-3">
<div class="card">
<div class="card-body text-center">

<h4>Citizens</h4>

<a href="../citizens/" class="btn btn-primary">
Open
</a>

</div>
</div>
</div>

<div class="col-md-3">
<div class="card">
<div class="card-body text-center">

<h4>Documents</h4>

<a href="../documents/" class="btn btn-success">
Open
</a>

</div>
</div>
</div>

<div class="col-md-3">
<div class="card">
<div class="card-body text-center">

<h4>Requests</h4>

<a href="../requests/" class="btn btn-warning">
Open
</a>

</div>
</div>
</div>

<div class="col-md-3">
<div class="card">
<div class="card-body text-center">

<h4>Reports</h4>

<a href="../reports/" class="btn btn-info">
Open
</a>

</div>
</div>
</div>

</div>

</div>

</body>
</html>