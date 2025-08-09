<?php
session_start();
include 'db.php';

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    die("Acceso denegado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST["titulo"]);
    $contenido = trim($_POST["contenido"]);
    $autor_id = $_SESSION["usuario_id"];

    $stmt = $conn->prepare("INSERT INTO tutoriales (titulo, contenido, autor_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $titulo, $contenido, $autor_id);

    if ($stmt->execute()) {
        $mensaje = "✅ Tutorial publicado con éxito.";
    } else {
        $mensaje = "❌ Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo tutorial</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #121212;
            color: #ecf0f1;
            padding: 40px;
        }
        .form-box {
            max-width: 600px;
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
        input[type="text"], textarea {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: none;
            border-radius: 6px;
            background-color: #2c2c2c;
            color: #ecf0f1;
            resize: vertical;
        }
        button {
            margin-top: 20px;
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

<div class="form-box">
    <h2>📝 Publicar nuevo tutorial</h2>
    <?php if (isset($mensaje)) echo "<p class='mensaje'>$mensaje</p>"; ?>
    <form method="POST">
        <input type="text" name="titulo" placeholder="Título del tutorial" required>
        <textarea name="contenido" placeholder="Contenido detallado..." rows="8" required></textarea>
        <button type="submit">Publicar</button>
    </form>

    <div class="nav-links">
        <a href="admin_panel.php">🔙 Volver al panel</a> |
        <a href="index.php">🏠 Inicio</a>
    </div>
</div>

</body>
</html>