<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST["nombre"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirmar = $_POST["confirmar"];

    if (!$nombre || !$email || !$password || !$confirmar) {
        $error = "Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Correo electrónico inválido.";
    } elseif ($password !== $confirmar) {
        $error = "Las contraseñas no coinciden.";
    } else {
        $check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Ya existe una cuenta con ese correo.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $rol = "usuario";

            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nombre, $email, $hash, $rol);

            if ($stmt->execute()) {
                $_SESSION["usuario_id"] = $stmt->insert_id;
                $_SESSION["usuario_nombre"] = $nombre;
                $_SESSION["rol"] = $rol;
                header("Location: index.php");
                exit;
            } else {
                $error = "Error al registrar: " . $stmt->error;
            }

            $stmt->close();
        }

        $check->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #121212;
            color: #ecf0f1;
            padding: 40px;
        }
        .form-box {
            max-width: 400px;
            margin: auto;
            background-color: #1e1e1e;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
        }
        h2 {
            text-align: center;
            color: #00ff99;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input[type="text"], input[type="email"], input[type="password"] {
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
            background-color: #00ff99;
            color: #121212;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background-color: #00cc7a;
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
            color: #00ff99;
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
    <h2>📝 Registro de usuario</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST">
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" required>

        <label for="email">Correo electrónico:</label>
        <input type="email" name="email" id="email" required>

        <label for="password">Contraseña:</label>
        <input type="password" name="password" id="password" required>

        <label for="confirmar">Confirmar contraseña:</label>
        <input type="password" name="confirmar" id="confirmar" required>

        <button type="submit">Registrarse</button>
    </form>

    <div class="nav-links">
        <a href="login.php">🔐 Ya tengo cuenta</a><br>
        <a href="javascript:history.back()">⬅️ Volver</a> |
        <a href="index.php">🏠 Inicio</a>
    </div>
</div>

</body>
</html>