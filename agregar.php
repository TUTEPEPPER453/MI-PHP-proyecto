<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include('db.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Tutorial</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Agregar nuevo tutorial</h1>
    <form method="POST">
        <input type="text" name="titulo" placeholder="Título del Easter Egg" required>
        <input type="text" name="mapa" placeholder="Mapa">
        <textarea name="descripcion" placeholder="Descripción paso a paso"></textarea>
        <input type="text" name="video_url" placeholder="URL del video (opcional)">
        <input type="submit" name="guardar" value="Guardar Tutorial">
    </form>

    <?php
    if (isset($_POST['guardar'])) {
        $titulo = $_POST['titulo'];
        $mapa = $_POST['mapa'];
        $descripcion = $_POST['descripcion'];
        $video = $_POST['video_url'];

        $sql = "INSERT INTO tutoriales_codz (titulo, mapa, descripcion, video_url)
                VALUES ('$titulo', '$mapa', '$descripcion', '$video')";
        $resultado = mysqli_query($conn, $sql);
        <?php if ($resultado): ?>
            <div class="mensaje exito">Tutorial guardado con éxito</div>
        <?php else: ?>
            <div class="mensaje error">Error al guardar. Inténtalo de nuevo</div>
        <?php endif; ?>
    }
    ?>
</body>
</html>
