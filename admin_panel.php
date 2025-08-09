<?php
session_start();
include 'db.php';

if (!isset($_SESSION["usuario_id"]) || $_SESSION["rol"] !== "admin") {
    die("Acceso denegado.");
}

$categoria_id = $_GET['categoria_id'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["crear_tutorial"])) {
    $titulo = trim($_POST["titulo"]);
    $contenido = trim($_POST["contenido"]);
    $categoria_id_post = intval($_POST["categoria_id"]);
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

    if ($titulo && $contenido && $categoria_id_post) {
        $stmt = $conn->prepare("INSERT INTO tutoriales (titulo, contenido, categoria_id, imagen) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $titulo, $contenido, $categoria_id_post, $imagen);
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

$categorias = $conn->query("SELECT id, nombre FROM categorias");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel Gamer</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Orbitron', sans-serif;
      background-color: #121212;
      color: #eee;
    }
    .admin-panel {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }
    .sidebar {
      background-color: #1e1e1e;
      padding: 20px;
      border-bottom: 2px solid #2c2c2c;
    }
    .sidebar h2 {
      color: #ff0055;
      margin-bottom: 10px;
    }
    .sidebar nav a {
      display: inline-block;
      margin-right: 15px;
      color: #ccc;
      text-decoration: none;
      font-weight: bold;
      cursor: pointer;
    }
    .sidebar nav a:hover {
      color: #ff0055;
    }
    .content {
      padding: 30px;
    }
    .seccion {
      display: none;
    }
    .seccion.active {
      display: block;
    }
    .card {
      background-color: #1a1a1a;
      border: 1px solid #333;
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 20px;
    }
    .card a {
      color: #ff0055;
      font-weight: bold;
      text-decoration: none;
    }
    .card p {
      margin: 8px 0;
    }
    .admin-links a {
      color: #ccc;
      text-decoration: none;
      margin-right: 10px;
      font-size: 14px;
    }
    .admin-links a:hover {
      color: #ff0055;
    }
    select, input[type="text"], textarea, input[type="file"], button {
      padding: 10px;
      font-size: 14px;
      background-color: #1a1a1a;
      color: #eee;
      border: 1px solid #333;
      border-radius: 6px;
      margin-bottom: 15px;
      width: 100%;
    }
    button {
      background-color: #ff0055;
      font-weight: bold;
      cursor: pointer;
    }
    button:hover {
      background-color: #ff3366;
    }
    .mensaje {
      color: #00ff99;
      margin-bottom: 15px;
    }
  </style>
  <script>
    function mostrarSeccion(id) {
      document.querySelectorAll('.seccion').forEach(s => s.classList.remove('active'));
      document.getElementById(id).classList.add('active');
    }
  </script>
</head>
<body>
<div class="admin-panel">
  <div class="sidebar">
    <h2>Admin</h2>
    <nav>
      <a onclick="mostrarSeccion('tutoriales')">Tutoriales</a>
      <a onclick="mostrarSeccion('comentarios')">💬 Comentarios</a>
      <a onclick="mostrarSeccion('usuarios')"> Usuarios</a>
    </nav>
  </div>

  <div class="content">
    <h1>Bienvenido, <?= htmlspecialchars($_SESSION["usuario_nombre"]) ?></h1>

    <div id="tutoriales" class="seccion active">
      <h2>📝 Crear nuevo tutorial</h2>
      <?php if (isset($mensaje)) echo "<p class='mensaje'>$mensaje</p>"; ?>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="crear_tutorial" value="1">
        <label for="titulo">Título:</label>
        <input type="text" name="titulo" id="titulo" required>

        <label for="contenido">Contenido:</label>
        <textarea name="contenido" id="contenido" rows="6" required></textarea>

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

        <label for="imagen">📷 Imagen (opcional):</label>
        <input type="file" name="imagen" id="imagen" accept="image/*">

        <button type="submit">Publicar tutorial</button>
      </form>

      <h2>Tutoriales existentes</h2>
      <form method="GET">
        <label for="categoria_id">Filtrar por categoría:</label>
        <select name="categoria_id" id="categoria_id" onchange="this.form.submit()">
          <option value="">Todas</option>
          <?php
          $categorias = $conn->query("SELECT id, nombre FROM categorias");
          while ($cat = $categorias->fetch_assoc()):
          ?>
            <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $categoria_id ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['nombre']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </form>
      <?php
      $where = $categoria_id ? "WHERE t.categoria_id = " . intval($categoria_id) : "";
      $resultado = $conn->query("SELECT t.id, t.titulo, c.nombre AS categoria
                                 FROM tutoriales t
                                 LEFT JOIN categorias c ON t.categoria_id = c.id
                                 $where
                                 ORDER BY t.id DESC");
      if ($resultado && $resultado->num_rows > 0):
        while ($fila = $resultado->fetch_assoc()):
          $categoria = $fila['categoria'] ?? 'Sin categoría';
      ?>
        <div class="card">
          <a href="ver_tutorial.php?id=<?= $fila['id'] ?>">
            <?= htmlspecialchars($fila['titulo']) ?>
          </a>
          <p><strong>Categoría:</strong> <?= htmlspecialchars($categoria) ?></p>
          <div class="admin-links">
            <a href="editar_tutorial.php?id=<?= $fila['id'] ?>">Editar</a>
            <a href="eliminar_tutorial.php?id=<?= $fila['id'] ?>" onclick="return confirm('¿Eliminar este tutorial?')">Eliminar</a>
          </div>
        </div>
      <?php
        endwhile;
      else:
        echo "<p>No hay tutoriales en esta categoría.</p>";
      endif;
      ?>
    </div>

    <div id="comentarios" class="seccion">
      <h2>Comentarios recientes</h2>
      <?php
           $comentarios = $conn->query("SELECT c.id, c.texto, u.nombre AS autor
                                   FROM comentarios c
                                   LEFT JOIN usuarios u ON c.autor_id = u.id
                                   ORDER BY c.id DESC LIMIT 10");
      if ($comentarios && $comentarios->num_rows > 0):
        while ($com = $comentarios->fetch_assoc()):
      ?>
        <div class="card">
          <p><?= htmlspecialchars($com['texto']) ?></p>
          <p><strong>Autor:</strong> <?= htmlspecialchars($com['autor']) ?></p>
          <div class="admin-links">
            <a href="eliminar_comentario.php?id=<?= $com['id'] ?>" onclick="return confirm('¿Eliminar este comentario?')">Eliminar</a>
          </div>
        </div>
      <?php
        endwhile;
      else:
        echo "<p>No hay comentarios recientes.</p>";
      endif;
      ?>
    </div>

    <div id="usuarios" class="seccion">
      <h2>Usuarios registrados</h2>
      <?php
      $usuarios = $conn->query("SELECT id, nombre, email FROM usuarios ORDER BY id DESC LIMIT 10");
      if ($usuarios && $usuarios->num_rows > 0):
        while ($u = $usuarios->fetch_assoc()):
      ?>
        <div class="card">
          <p><strong><?= htmlspecialchars($u['nombre']) ?></strong></p>
          <p><?= htmlspecialchars($u['email']) ?></p>
          <div class="admin-links">
            <a href="editar_usuario.php?id=<?= $u['id'] ?>">Editar</a>
            <a href="eliminar_usuario.php?id=<?= $u['id'] ?>" onclick="return confirm('¿Eliminar este usuario?')">Eliminar</a>
          </div>
        </div>
      <?php
        endwhile;
      else:
        echo "<p>No hay usuarios registrados.</p>";
      endif;
      $conn->close();
      ?>
    </div>
  </div>
</div>
</body>
</html>
