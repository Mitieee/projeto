<?php
$url = getenv("JAWSDB_URL");
$dbparts = parse_url($url);
$host = $dbparts['qn66usrj1lwdk1cc.cbetxkdyhwsb.us-east-1.rds.amazonaws.com'];
$user = $dbparts['bcgq92tgn5llinno'];
$pass = $dbparts['g8buzhryqff6f4m'];
$dbname = ltrim($dbparts['jl3ov1glq3jyb7au'], '/');
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}
?>
