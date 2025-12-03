<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tictracker_v6";
$port = 3307;


$conn = mysqli_connect($servername, $username, $password, $dbname, $port);

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}
