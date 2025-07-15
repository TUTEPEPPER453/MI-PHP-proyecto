<?php
$host = "sql301.infinityfree.com";   
$user = "if0_38941618";      
$pass = "GravityCraft102";      
$db   = "if0_38941618_dt_gelatino"; 

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Conexión fallida: " . mysqli_connect_error());
}
?>