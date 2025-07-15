<?php include('db.php'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tutoriales COD Zombies</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Tutoriales de Call of Duty Zombies 🧟</h1>
    <?php
    $consulta = "SELECT * FROM tutoriales_codz ORDER BY id DESC";
    $resultado = mysqli_query($conn, $consulta);

    if (mysqli_num_rows($resultado) > 0) {
        while ($fila = mysqli_fetch_assoc($resultado)) {
            ?>
            <div>
                <h2><?php echo htmlspecialchars($fila['titulo']); ?></h2>
                <p><strong>Mapa:</strong> <?php echo htmlspecialchars($fila['mapa']); ?></p>
                <p><?php echo nl2br(htmlspecialchars($fila['descripcion'])); ?></p>
                <?php if (!empty($fila['video_url'])): ?>
                    <p><a href="<?php echo htmlspecialchars($fila['video_url']); ?>" target="_blank">🎥 Ver video</a></p>
                <?php endif; ?>

                <h3>Comentarios</h3>
                <?php
                $tutorial_id = $fila['id'];
                $comentarios = mysqli_query($conn, "SELECT * FROM comentarios_codz WHERE tutorial_id = $tutorial_id ORDER BY fecha DESC");
                while ($com = mysqli_fetch_assoc($comentarios)) {
                    echo "<p><strong>" . htmlspecialchars($com['nombre']) . ":</strong> " . htmlspecialchars($com['comentario']) . "</p>";
                }
                ?>

                <form method="POST">
                    <input type="hidden" name="tutorial_id" value="<?php echo $fila['id']; ?>">
                    <input type="text" name="nombre" placeholder="Tu nombre" required>
                    <textarea name="comentario" placeholder="Escribe tu comentario aquí..." required></textarea>
                    <input type="submit" name="comentar" value="Comentar">
                </form>
            </div>
            <?php
        }
    } else {
        echo "<p>No hay tutoriales aún. ¡Agrega el primero!</p>";
    }

    if (isset($_POST['comentar'])) {
        $nombre = $_POST['nombre'];
        $comentario = $_POST['comentario'];
        $tutorial_id = $_POST['tutorial_id'];

        $sqlComentario = "INSERT INTO comentarios_codz (tutorial_id, nombre, comentario)
                          VALUES ('$tutorial_id', '$nombre', '$comentario')";
        mysqli_query($conn, $sqlComentario);
        header("Location: ver.php");
        exit();
    }
    ?>
</body>
</html>