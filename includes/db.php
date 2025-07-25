<?php
$host = 'localhost';
$db   = 'fbp';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
// Set charset
mysqli_set_charset($conn, $charset); 