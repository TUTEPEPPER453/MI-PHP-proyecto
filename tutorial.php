<?php
session_start();
include 'db.php';

if (!isset($_GET["id"])) {
    die("Tutorial no especificado.");
}

$tutorial_id = intval($_GET["id"]);

$stmt = $conn->prepare("
    SELECT t.titulo, t.contenido, u.nombre AS autor, t.fecha_publicacion
    FROM tutoriales t
    JOIN usuarios u ON t.autor_id = u.id
    WHERE t.id = ?
");
$stmt->bind_param("i", $tutorial_id);
$stmt->execute();
$stmt->bind_result($titulo, $contenido, $autor, $fecha);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($titulo) ?></title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f4f4;
            padding: 30px;
        }
        .contenido {
            max-width: 800px;
            margin: auto;
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #2c3e50;
        }
        .comentario {
            border: 1px solid #eee;
            padding: 8px;
            margin: 10px 0;
            border-radius: 6px;
            background-color: #fafafa;
        }
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            resize: vertical;
        }
        button {
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #2980b9;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #3498db;
        }
        .volver {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #2980b9;
        }
    </style>
</head>
<body>

<div class="contenido">
    <h2><?= htmlspecialchars($titulo) ?></h2>
    <p><?= nl2br(htmlspecialchars($contenido)) ?></p>
    <small>Publicado por <?= htmlspecialchars($autor) ?> el <?= $fecha ?></small>
    <hr>

    <h3>🗨️ Comentarios</h3>
    <?php
    $result = $conn->prepare("
        SELECT c.texto, u.nombre, c.fecha_comentario
        FROM comentarios c
        JOIN usuarios u ON c.autor_id = u.id
        WHERE c.tutorial_id = ?
        ORDER BY c.fecha_comentario DESC
    ");
    $result->bind_param("i", $tutorial_id);
    $result->execute();
    $result->store_result();
    $result->bind_result($texto, $nombre_comentador, $fecha_comentario);

    if ($result->num_rows > 0) {
        while ($result->fetch()) {
            echo "<div class='comentario'>";
            echo "<strong>" . htmlspecialchars($nombre_comentador) . "</strong> dijo:<br>";
            echo "<p>" . nl2br(htmlspecialchars($texto)) . "</p>";
            echo "<small>" . $fecha_comentario . "</small>";
            echo "</div>";
        }
    } else {
        echo "<p>No hay comentarios aún.</p>";
    }
    $result->close();
    ?>

    <hr>
    <?php if (!empty($_SESSION["usuario_id"])): ?>
        <h4>✍️ Agregar comentario</h4>
        <form method="POST" action="comentar.php">
            <input type="hidden" name="tutorial_id" value="<?= $tutorial_id ?>">
            <textarea name="texto" rows="4" placeholder="Escribe tu comentario..." required></textarea>
            <button type="submit">Comentar</button>
        </form>
    <?php else: ?>
        <p><a href="login.php">🔒 Inicia sesión</a> para comentar.</p>
    <?php endif; ?>

    <a href="index.php" class="volver">⬅️ Volver al inicio</a>
</div>

</body>
</html>

<?php $conn->close(); ?>