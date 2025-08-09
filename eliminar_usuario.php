<?php
include 'db.php';
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    die("Acceso denegado.");
}

$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    $conn->query("DELETE FROM usuarios WHERE id = $id");
}
header("Location: admin_panel.php");
exit();