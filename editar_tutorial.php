<?php
session_start();
include "db.php";

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    die("Acceso denegado.");
}

$id = intval($_GET["id"] ?? 0);
if (!$id) {
    echo "ID no válido.";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM tutoriales WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$tutorial = $resultado->fetch_assoc();

if (!$tutorial) {
    echo "Tutorial no encontrado.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["eliminar_imagen"])) {
    $index = intval($_POST["eliminar_imagen"]);
    $imagenes_extra = json_decode($tutorial["imagenes"] ?? '[]', true);

    if (isset($imagenes_extra[$index])) {
        $ruta_eliminar = $imagenes_extra[$index];
        unset($imagenes_extra[$index]);

        if (file_exists($ruta_eliminar)) {
            unlink($ruta_eliminar);
        }

        $imagenes_extra = array_values($imagenes_extra);
        $imagenes_serializadas = json_encode($imagenes_extra);

        $stmt = $conn->prepare("UPDATE tutoriales SET imagenes = ? WHERE id = ?");
        $stmt->bind_param("si", $imagenes_serializadas, $id);
        $stmt->execute();

        header("Location: editar_tutorial.php?id=$id");
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST["eliminar_imagen"])) {
    $nuevo_titulo = $_POST["titulo"];
    $nuevo_contenido = $_POST["contenido"];
    $nueva_imagen = $tutorial["imagen"];
    $imagenes_extra = json_decode($tutorial["imagenes"] ?? '[]', true);

    if (!empty($_FILES["imagen"]["name"])) {
        $nombreArchivo = basename($_FILES["imagen"]["name"]);
        $rutaDestino = "uploads/" . uniqid() . "_" . $nombreArchivo;
        $tipoArchivo = strtolower(pathinfo($rutaDestino, PATHINFO_EXTENSION));

        if (in_array($tipoArchivo, ["jpg", "jpeg", "png", "gif"])) {
            if (!is_dir("uploads")) {
                mkdir("uploads", 0777, true);
            }
            move_uploaded_file($_FILES["imagen"]["tmp_name"], $rutaDestino);
            $nueva_imagen = $rutaDestino;
        }
    }

    if (!empty($_FILES["imagenes"]["name"][0])) {
        foreach ($_FILES["imagenes"]["tmp_name"] as $index => $tmpName) {
            if ($_FILES["imagenes"]["error"][$index] === UPLOAD_ERR_OK) {
                $nombreArchivo = basename($_FILES["imagenes"]["name"][$index]);
                $rutaDestino = "uploads/" . uniqid() . "_" . $nombreArchivo;
                $tipoArchivo = strtolower(pathinfo($rutaDestino, PATHINFO_EXTENSION));

                if (in_array($tipoArchivo, ["jpg", "jpeg", "png", "gif"])) {
                    move_uploaded_file($tmpName, $rutaDestino);
                    $imagenes_extra[] = $rutaDestino;
                }
            }
        }
    }

    $imagenes_serializadas = json_encode($imagenes_extra);

    $stmt = $conn->prepare("UPDATE tutoriales SET titulo = ?, contenido = ?, imagen = ?, imagenes = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $nuevo_titulo, $nuevo_contenido, $nueva_imagen, $imagenes_serializadas, $id);
    $stmt->execute();

    header("Location: admin_panel.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Tutorial</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #eee;
            font-family: 'Orbitron', sans-serif;
            padding: 20px;
        }
        h1 {
            color: #ff0055;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            background-color: #1e1e1e;
            color: #fff;
            border: 1px solid #333;
            border-radius: 5px;
        }
        input[type="file"] {
            margin-top: 10px;
        }
        button {
            margin-top: 20px;
            background-color: #ff0055;
            color: #000;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }
        img {
            max-width: 200px;
            margin: 10px 5px;
            border: 2px solid #ff0055;
            border-radius: 5px;
        }
        .img-container {
            display: inline-block;
            position: relative;
        }
        .img-container form {
            position: absolute;
            top: 5px;
            right: 5px;
        }
        .img-container button {
            background: #ff0055;
            color: #fff;
            border: none;
            border-radius: 3px;
            padding: 2px 6px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>✏️ Editar Tutorial</h1>
    <form method="POST" enctype="multipart/form-data">
        <label for="titulo">Título:</label>
        <input type="text" name="titulo" id="titulo" value="<?= htmlspecialchars($tutorial["titulo"]) ?>" required>

        <label for="contenido">Contenido:</label>
        <textarea name="contenido" id="contenido" rows="8" required><?= htmlspecialchars($tutorial["contenido"]) ?></textarea>

        <label for="imagen">Imagen principal:</label>
        <input type="file" name="imagen" id="imagen" accept="image/*">
        <?php if (!empty($tutorial["imagen"])): ?>
            <img src="<?= htmlspecialchars($tutorial["imagen"]) ?>" alt="Imagen actual">
        <?php endif; ?>

        <label for="imagenes">Imágenes adicionales:</label>
        <input type="file" name="imagenes[]" id="imagenes" accept="image/*" multiple>
        <div>
        <?php
        $imagenes_extra = json_decode($tutorial["imagenes"] ?? '[]', true);
        foreach ($imagenes_extra as $i => $img): ?>
            <div class="img-container">
                <img src="<?= htmlspecialchars($img) ?>" alt="Imagen adicional">
                <form method="POST">
                    <input type="hidden" name="eliminar_imagen" value="<?= $i ?>">
                    <button type="submit">❌</button>
                </form>
            </div>
        <?php endforeach; ?>
        </div>

        <button type="submit">💾 Guardar Cambios</button>
    </form>
</body>
</html>