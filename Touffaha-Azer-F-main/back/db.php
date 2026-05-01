<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'ecommerce_db';

// Connexion MySQLi 
$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("Connexion échouée : " . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8');
?>
