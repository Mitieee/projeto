<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "emergencias";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Falha" . $conn->connect_error);
}
?>