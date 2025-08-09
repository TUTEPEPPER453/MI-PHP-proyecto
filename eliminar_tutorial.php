<?php
session_start();
include 'db.php';

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    die("Acceso denegado.");
}

if (!isset($_GET["id"])) {
    die("Tutorial no especificado.");
}

$id = intval($_GET["id"]);

$stmt = $conn->prepare("DELETE FROM tutoriales WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    mysqli_query($conn, "DELETE FROM comentarios WHERE tutorial_id = $id");
    echo "Tutorial eliminado.";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>