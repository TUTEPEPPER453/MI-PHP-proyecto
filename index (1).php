<?php
session_start();
include 'db.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>🐔 GelatutoTutoriales</title>
    <style>
        body {
            font-family: 'Orbitron', 'Segoe UI', sans-serif;
            background-color: #121212;
            color: #e0e0e0;
            margin: 0;
            padding: 0;
        }
        header {
            background: linear-gradient(90deg, #ff004f, #2c3e50);
            color: white;
            padding: 30px 0;
            text-align: center;
            font-size: 28px;
            letter-spacing: 2px;
            text-shadow: 1px 1px 3px black;
        }
        nav {
            background-color: #1f1f1f;
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #333;
        }
        nav a {
            color: #ff004f;
            margin: 0 20px;
            text-decoration: none;
            font-weight: bold;
            font-size: 15px;
            transition: color 0.3s;
        }
        nav a:hover {
            color: #ffffff;
        }
        .container {
            max-width: 1100px;
            margin: 40px auto;
            background-color: #1e1e1e;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(255,0,79,0.2);
        }
        h2 {
            font-size: 22px;
            margin-top: 50px;
            border-bottom: 2px solid #ff004f;
            padding-bottom: 8px;
            text-transform: uppercase;
        }
        .tutorial {
            display: flex;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid #333;
            align-items: center;
        }
        .tutorial img {
            width: 140px;
            height: 90px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(255,0,79,0.3);
        }
        .tutorial-info {
            flex-grow: 1;
        }
        .tutorial-info a {
            font-size: 20px;
            color: #00bfff;
            text-decoration: none;
            font-weight: bold;
        }
        .tutorial-info a:hover {
            color: #ffffff;
        }
        .tutorial-info p {
            margin: 6px 0;
            color: #ccc;
            font-size: 15px;
        }
        .tutorial-info .fecha {
            font-size: 13px;
            color: #888;
        }
        .admin-links {
            font-size: 13px;
            color: #aaa;
            margin-top: 8px;
        }
        .admin-links a {
            color: #ff004f;
            text-decoration: none;
            margin-right: 10px;
        }
        .admin-links a:hover {
            text-decoration: underline;
        }
        footer {
            text-align: center;
            padding: 20px;
            background-color: #1f1f1f;
            font-size: 14px;
            color: #777;
            margin-top: 60px;
            border-top: 1px solid #333;
        }
        .categoria-minecraft h2 { color: #00ff99; }
        .categoria-pvz h2 { color: #ffcc00; }
        .categoria-cod h2 { color: #ff004f; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap" rel="stylesheet">
</head>
<body>

<header>
    🐔GELATUTORIALES🐔
</header>

<nav>
    <?php if (isset($_SESSION["usuario_id"])): ?>
        <?= htmlspecialchars($_SESSION["usuario_nombre"]) ?> (<?= $_SESSION["rol"] ?>)
        | <a href="logout.php">Cerrar sesión</a>
        <?php if ($_SESSION["rol"] === "admin"): ?>
            | <a href="admin_panel.php">Panel de administración</a>
        <?php endif; ?>
    <?php else: ?>
        <a href="login.php">Iniciar sesión</a>
        <a href="registro.php">Registrarse</a>
    <?php endif; ?>
</nav>

<div class="container">
    <?php
    $categorias = $conn->query("SELECT id, nombre FROM categorias");

    while ($cat = $categorias->fetch_assoc()):
        $clase_categoria = '';
        switch ($cat['nombre']) {
            case 'Minecraft': $clase_categoria = 'categoria-minecraft'; break;
            case 'Plantas contra Zombies': $clase_categoria = 'categoria-pvz'; break;
            case 'Call of Duty: Zombies': $clase_categoria = 'categoria-cod'; break;
        }

        echo "<div class='$clase_categoria'><h2>" . htmlspecialchars($cat['nombre']) . "</h2>";

        $stmt = $conn->prepare("SELECT id, titulo, LEFT(contenido, 100) AS resumen, imagen, fecha_publicacion FROM tutoriales WHERE categoria_id = ? ORDER BY fecha_publicacion DESC");
        $stmt->bind_param("i", $cat['id']);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 0) {
            echo "<p>No hay tutoriales disponibles aún.</p>";
        }

        while ($fila = $resultado->fetch_assoc()):
    ?>
            <div class="tutorial">
                <?php if (!empty($fila['imagen'])): ?>
                    <img src="<?= htmlspecialchars($fila['imagen']) ?>" alt="Imagen del tutorial">
                <?php else: ?>
                    <img src="default.jpg" alt="Sin imagen">
                <?php endif; ?>

                <div class="tutorial-info">
                    <a href="ver_tutorial.php?id=<?= $fila['id'] ?>">
                        <?= htmlspecialchars($fila['titulo']) ?>
                    </a>
                    <p><?= htmlspecialchars($fila['resumen']) ?>...</p>
                    <p class="fecha"><?= date("d M Y", strtotime($fila['fecha_publicacion'])) ?></p>

                    <?php if (isset($_SESSION["usuario_id"]) && $_SESSION["rol"] === "admin"): ?>
                        <div class="admin-links">
                            <a href="editar_tutorial.php?id=<?= $fila['id'] ?>">Editar</a>
                            <a href="eliminar_tutorial.php?id=<?= $fila['id'] ?>" onclick="return confirm('¿Eliminar este tutorial?')">Eliminar</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
    <?php
        endwhile;
        $stmt->close();
        echo "</div>";
    endwhile;

    $conn->close();
    ?>
</div>

<footer>
    &copy; <?= date("Y") ?> Gelatutoriales. Todos los derechos reservados.
</footer>

</body>
</html>