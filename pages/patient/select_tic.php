<?php
session_start();
include('../../config.php');

if (!isset($_SESSION['User_ID']) || $_SESSION['Role'] !== 'Patient') {
    header("Location: ../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Choose Tic Type</title>
<link rel="stylesheet" href="../../style.css">
<style>
.container {
    max-width: 480px;
    margin: 60px auto;
    padding: 20px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
    text-align: center;
}
button {
    width: 100%;
    padding: 12px;
    margin: 8px 0;
    border-radius: 8px;
    border: none;
    font-weight: bold;
    cursor: pointer;
    font-size: 16px;
}
.btn-simple {
    background: #4CAF50;
    color: #fff;
}
.btn-complex {
    background: #0b84ff;
    color: #fff;
}
p {
    font-size: 14px;
    color: #666;
    margin-top: 12px;
}
</style>
</head>
<body>

<div class="container">
    <h2>Log a Tic</h2>
    <p>Select how you want to log your tic:</p>

    <!-- Simple Tic -->
    <form method="GET" action="ticlog.php">
        <button class="btn-simple" type="submit">Simple Tic</button>
    </form>

    <!-- Complex Tic -->
    <form method="GET" action="ticlog_complex.php">
        <button class="btn-complex" type="submit">Complex Tic (multiple tics)</button>
    </form>
</div>

</body>
</html>
