<?php
$url = getenv("JAWSDB_URL");
$dbparts = parse_url($url);
$host = $dbparts['host'];
$user = $dbparts['user'];
$pass = $dbparts['pass'];
$dbname = ltrim($dbparts['path'], '/');
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}
date_default_timezone_set('America/Sao_Paulo'); 
$conn->query("SET time_zone = 'America/Sao_Paulo'");
?>