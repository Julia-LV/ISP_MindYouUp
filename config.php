<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mindyouup_db";
$port = 3307;

$conn = mysqli_connect($servername, $username, $password, $dbname, $port);

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}
