<?php
$servername = "localhost";
$username = "root";
$password = "";
<<<<<<< Updated upstream
$dbname = "tictracker_V6";
=======
$dbname = "tictracker_V8";

>>>>>>> Stashed changes

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}
