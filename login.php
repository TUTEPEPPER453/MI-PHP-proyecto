<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, nombre, password, rol FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($id, $nombre, $hash, $rol);

    if ($stmt->fetch() && password_verify($password, $hash)) {
        $_SESSION["usuario_id"] = $id;
        $_SESSION["usuario_nombre"] = $nombre;
        $_SESSION["rol"] = $rol;

        if ($rol === "admin") {
            header("Location: admin_panel.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        $error = "❌ Credenciales incorrectas.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #121212;
            color: #ecf0f1;
            padding: 40px;
        }
        .login-box {
            max-width: 400px;
            margin: auto;
            background-color: #1e1e1e;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
        }
        h2 {
            text-align: center;
            color: #00ffff;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: none;
            border-radius: 5px;
            background-color: #2c2c2c;
            color: #ecf0f1;
        }
        button {
            margin-top: 20px;
            width: 100%;
            padding: 10px;
            background-color: #00ffff;
            color: #121212;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background-color: #00bfbf;
        }
        .error {
            color: #e74c3c;
            text-align: center;
            margin-top: 10px;
        }
        .nav-links {
            margin-top: 20px;
            text-align: center;
        }
        .nav-links a {
            color: #00ffff;
            text-decoration: none;
            margin: 0 10px;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>🔐 Iniciar sesión</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <label for="email">Correo electrónico:</label>
        <input type="email" name="email" id="email" required>

        <label for="password">Contraseña:</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">Entrar</button>
    </form>

    <div class="nav-links">
        <a href="registro.php">¿No tienes cuenta? Regístrate aquí</a><br>
        <a href="javascript:history.back()">⬅️ Volver</a> |
        <a href="index.php">🏠 Inicio</a>
    </div>
</div>

</body>
</html>