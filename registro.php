<?php include('db.php'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro GelaTutoriales</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Registro de usuarios</h1>
    <form method="POST">
        <input type="text" name="nombre" placeholder="Tu nombre" required>
        <input type="email" name="correo" placeholder="Correo electrónico" required>
        <input type="password" name="contraseña" placeholder="Contraseña" required>
        <input type="submit" name="registrar" value="Registrarse">
    </form>

    <?php
    if (isset($_POST['registrar'])) {
        $nombre = $_POST['nombre'];
        $correo = $_POST['correo'];
        $contraseña = password_hash($_POST['contraseña'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios_codz (nombre, correo, contraseña) 
                VALUES ('$nombre', '$correo', '$contraseña')";

        $resultado = mysqli_query($conn, $sql);
        if ($resultado) {
            echo "<p style='color:limegreen;'>Registro exitoso 🎉</p>";
        } else {
            echo "<p style='color:red;'>Error al registrar. ¿El correo ya existe?</p>";
        }
    }
    ?>
</body>
</html>