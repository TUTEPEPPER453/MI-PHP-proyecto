<?php
$host = "";   
$user = "i;      
$pass = ";      
$db   = "i"; 

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Conexión fallida: " . mysqli_connect_error());
}
?>
