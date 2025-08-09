<?php
session_start();
include 'db.php';

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    die("Acceso denegado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST["titulo"]);
    $contenido = trim($_POST["contenido"]);
    $categoria_id = intval($_POST["categoria_id"]);
    $imagen = null;

    if (isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] === UPLOAD_ERR_OK) {
        $nombreArchivo = basename($_FILES["imagen"]["name"]);
        $rutaDestino = "uploads/" . uniqid() . "_" . $nombreArchivo;
        $tipoArchivo = strtolower(pathinfo($rutaDestino, PATHINFO_EXTENSION));

        if (in_array($tipoArchivo, ["jpg", "jpeg", "png", "gif"])) {
            if (!is_dir("uploads")) {
                mkdir("uploads", 0777, true);
            }
            move_uploaded_file($_FILES["imagen"]["tmp_name"], $rutaDestino);
            $imagen = $rutaDestino;
        } else {
            $mensaje = "❌ Solo se permiten imágenes JPG, PNG o GIF.";
        }
    }

    if ($titulo && $contenido && $categoria_id) {
        $stmt = $conn->prepare("INSERT INTO tutoriales (titulo, contenido, categoria_id, imagen) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $titulo, $contenido, $categoria_id, $imagen);

        if ($stmt->execute()) {
            $mensaje = "✅ Tutorial creado correctamente.";
        } else {
            $mensaje = "❌ Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $mensaje = "❌ Todos los campos son obligatorios.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Tutorial</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #121212;
            color: #ecf0f1;
            padding: 40px;
        }
        .form-container {
            max-width: 700px;
            margin: auto;
            background-color: #1e1e1e;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.6);
        }
        h2 {
            text-align: center;
            color: #00ffcc;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input[type="text"], textarea, select, input[type="file"] {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
            border: none;
            border-radius: 6px;
            background-color: #2c2c2c;
            color: #ecf0f1;
        }
        textarea {
            resize: vertical;
        }
        button {
            margin-top: 25px;
            width: 100%;
            padding: 12px;
            background-color: #00ffcc;
            color: #121212;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background-color: #00ccaa;
        }
        .mensaje {
            text-align: center;
            margin-top: 15px;
            color: #00ff99;
        }
        .nav-links {
            margin-top: 25px;
            text-align: center;
        }
        .nav-links a {
            color: #00ffcc;
            text-decoration: none;
            margin: 0 10px;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>📝 Crear nuevo tutorial</h2>
    <?php if (isset($mensaje)) echo "<p class='mensaje'>$mensaje</p>"; ?>
    <form method="POST" enctype="multipart/form-data">
        <label for="titulo">Título:</label>
        <input type="text" name="titulo" id="titulo" required>

        <label for="contenido">Contenido:</label>
        <textarea name="contenido" id="contenido" rows="8" required></textarea>

        <label for="categoria_id">Juego:</label>
        <select name="categoria_id" id="categoria_id" required>
            <option value="">-- Selecciona un juego --</option>
            <?php
            $categorias = $conn->query("SELECT id, nombre FROM categorias");
            while ($cat = $categorias->fetch_assoc()):
            ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
            <?php endwhile; ?>
        </select>

        <label for="imagen">📷 Imagen destacada (opcional):</label>
        <input type="file" name="imagen" id="imagen" accept="image/*">

        <button type="submit">Crear tutorial</button>
    </form>

    <div class="nav-links">
        <a href="admin_panel.php">🔙 Volver al panel</a> |
        <a href="index.php">🏠 Inicio</a>
    </div>
</div>

</body>
</html>