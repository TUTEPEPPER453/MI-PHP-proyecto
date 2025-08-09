<?php
session_start();
include 'db.php';

if (!isset($_GET['id'])) {
    die("Tutorial no especificado.");
}

$id = intval($_GET['id']);
$resultado = $conn->query("SELECT t.titulo, t.contenido, t.imagen, t.imagenes, c.nombre AS categoria
                           FROM tutoriales t
                           LEFT JOIN categorias c ON t.categoria_id = c.id
                           WHERE t.id = $id");

if ($resultado->num_rows === 0) {
    die("Tutorial no encontrado.");
}

$tutorial = $resultado->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($tutorial['titulo']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
    <style>
        body {
            font-family: 'Orbitron', 'Segoe UI', sans-serif;
            background-color: #0f0f0f;
            margin: 0;
            padding: 40px 20px;
            color: #e0e0e0;
        }
        .contenido {
            max-width: 900px;
            margin: auto;
            background-color: #1a1a1a;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 25px rgba(255,0,79,0.2);
        }
        h1 {
            font-size: 32px;
            color: #00bfff;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        h3 {
            color: #ff004f;
            margin-top: 40px;
        }
        .categoria {
            font-size: 14px;
            color: #999;
            margin-bottom: 20px;
        }
        .volver {
            margin-top: 40px;
            display: inline-block;
            padding: 12px 20px;
            background-color: #ff004f;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .volver:hover {
            background-color: #ff3366;
        }
        img {
            max-width: 100%;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(255,0,79,0.3);
            margin-bottom: 30px;
        }
        .contenido-texto {
            font-size: 16px;
            line-height: 1.6;
            color: #ccc;
        }
        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #444;
            background-color: #1a1a1a;
            color: #eee;
            resize: vertical;
        }
        input[type="file"] {
            margin-top: 10px;
            color: #ccc;
        }
        button {
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #ff004f;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background-color: #ff3366;
        }
        .comentario {
            margin-top: 20px;
            padding: 15px;
            background-color: #1f1f1f;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(255,0,79,0.1);
        }
        .comentario strong {
            color: #ff004f;
        }
        .comentario p {
            color: #ccc;
        }
        .comentario small {
            color: #777;
        }
    </style>
</head>
<body>

<div class="contenido">
    <h1><?= htmlspecialchars($tutorial['titulo']) ?></h1>
    <p class="categoria">Categoría: <?= htmlspecialchars($tutorial['categoria'] ?? 'Sin categoría') ?></p>
    <hr>

    <?php if (!empty($tutorial['imagen'])): ?>
        <a href="<?= htmlspecialchars($tutorial['imagen']) ?>" data-lightbox="galeria" data-title="Imagen principal">
            <img src="<?= htmlspecialchars($tutorial['imagen']) ?>" alt="Imagen del tutorial">
        </a>
    <?php endif; ?>

    <?php
    $imagenes_extra = json_decode($tutorial['imagenes'] ?? '[]', true);
    if (!empty($imagenes_extra)):
    ?>
        <h3>Imágenes adicionales</h3>
        <div style="display:flex; flex-wrap:wrap; gap:15px; margin-bottom:30px;">
            <?php foreach ($imagenes_extra as $img): ?>
                <a href="<?= htmlspecialchars($img) ?>" data-lightbox="galeria" data-title="Imagen adicional">
                    <img src="<?= htmlspecialchars($img) ?>" alt="Imagen adicional" style="max-width:200px; border:2px solid #00ffcc; border-radius:10px;">
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="contenido-texto">
        <?= nl2br(htmlspecialchars($tutorial['contenido'])) ?>
    </div>

    <?php if (isset($_SESSION["usuario_id"])): ?>
        <form method="POST" action="comentar.php" enctype="multipart/form-data" style="margin-top:40px;">
            <input type="hidden" name="tutorial_id" value="<?= $id ?>">
            <textarea name="texto" rows="4" placeholder="Escribe tu comentario..." required></textarea>
            <input type="file" name="imagen" accept="image/*">
            <button type="submit">Comentar</button>
        </form>
    <?php else: ?>
        <p style="margin-top:40px;">Inicia sesión para comentar.</p>
    <?php endif; ?>

    <?php
    $comentarios = $conn->prepare("
        SELECT c.id, c.texto, u.nombre, c.fecha_comentario, c.respuesta_a, c.imagen
        FROM comentarios c
        JOIN usuarios u ON c.autor_id = u.id
        WHERE c.tutorial_id = ?
        ORDER BY c.fecha_comentario ASC
    ");
    $comentarios->bind_param("i", $id);
    $comentarios->execute();
    $comentarios->store_result();
    $comentarios->bind_result($comentario_id, $texto, $nombre, $fecha, $respuesta_a, $imagen_comentario);

    $comentarios_array = [];
    while ($comentarios->fetch()) {
        $comentarios_array[] = [
            'id' => $comentario_id,
            'texto' => $texto,
            'nombre' => $nombre,
            'fecha' => $fecha,
            'respuesta_a' => $respuesta_a,
            'imagen' => $imagen_comentario
        ];
    }
    $comentarios->close();

    function mostrar_comentarios($comentarios, $padre = null, $nivel = 0) {
        global $id;
        foreach ($comentarios as $c) {
            if ($c['respuesta_a'] == $padre) {
                echo "<div class='comentario' style='margin-left:" . ($nivel * 30) . "px'>";
                echo "<strong>" . htmlspecialchars($c['nombre']) . "</strong><br>";
                echo "<p>" . nl2br(htmlspecialchars($c['texto'])) . "</p>";
                echo "<small>" . $c['fecha'] . "</small>";
                if (!empty($c['imagen'])) {
                    echo "<br><a href='" . htmlspecialchars($c['imagen']) . "' data-lightbox='comentarios' data-title='Imagen del comentario'>";
                    echo "<img src='" . htmlspecialchars($c['imagen']) . "' alt='Imagen del comentario' style='max-width:300px; margin-top:10px; border:2px solid #ff004f; border-radius:8px;'>";
                    echo "</a>";
                }
                if (isset($_SESSION["usuario_id"])) {
                    echo "<form method='POST' action='comentar.php' enctype='multipart/form-data' style='margin-top:10px;'>";
                    echo "<input type='hidden' name='tutorial_id' value='" . $id . "'>";
                    echo "<input type='hidden' name='respuesta_a' value='" . $c['id'] . "'>";
                    echo "<textarea name='texto' rows='2' placeholder='Responder...' required></textarea>";
                    echo "<input type='file' name='imagen' accept='image/*'>";
                    echo "<button type='submit'>Responder</button>";
                    echo "</form>";
                }
                mostrar_comentarios($comentarios, $c['id'], $nivel + 1);
                echo "</div>";
            }
        }
    }

    echo "<div style='margin-top:50px;'><h2 style='color:#00bfff;'>Comentarios</h2>";
    mostrar_comentarios($comentarios_array);
    echo "</div>";
    ?>

    <a href="index.php" class="volver">Volver al inicio</a>
</div>

</body>
</html>