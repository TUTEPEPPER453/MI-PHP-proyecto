<?php
session_start();
include 'db.php';

if (!isset($_SESSION["usuario_id"])) {
    die("Acceso denegado.");
}

$autor_id = $_SESSION["usuario_id"];
$tutorial_id = intval($_POST["tutorial_id"] ?? 0);
$respuesta_a = intval($_POST["respuesta_a"] ?? 0);
$texto = trim($_POST["texto"] ?? "");

if ($tutorial_id <= 0 || empty($texto)) {
    die("Datos inválidos.");
}

$ruta_imagen = null;
if (!empty($_FILES["imagen"]["name"])) {
    $nombreArchivo = basename($_FILES["imagen"]["name"]);
    $rutaDestino = "uploads/comentarios/" . uniqid() . "_" . $nombreArchivo;
    $tipoArchivo = strtolower(pathinfo($rutaDestino, PATHINFO_EXTENSION));

    if (in_array($tipoArchivo, ["jpg", "jpeg", "png", "gif"])) {
        if (!is_dir("uploads/comentarios")) {
            mkdir("uploads/comentarios", 0777, true);
        }
        move_uploaded_file($_FILES["imagen"]["tmp_name"], $rutaDestino);
        $ruta_imagen = $rutaDestino;
    }
}

$stmt = $conn->prepare("INSERT INTO comentarios (texto, autor_id, tutorial_id, respuesta_a, imagen) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("siiis", $texto, $autor_id, $tutorial_id, $respuesta_a, $ruta_imagen);
$stmt->execute();

header("Location: ver_tutorial.php?id=" . $tutorial_id);
exit;