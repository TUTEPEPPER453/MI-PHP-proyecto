<?php
$host = "";   
$user = "";      
$pass = "";      
$db   = ""; 

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Conexión fallida: " . mysqli_connect_error());
}
?>
