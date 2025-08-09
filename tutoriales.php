<?php
include 'db.php';

$resultado = $conn->query("
    SELECT t.id, t.titulo, t.contenido, u.nombre AS autor, t.fecha_publicacion
    FROM tutoriales t
    JOIN usuarios u ON t.autor_id = u.id
    ORDER BY t.fecha_publicacion DESC
");

echo "<h2>Tutoriales</h2>";
while ($fila = $resultado->fetch_assoc()) {
    echo "<div style='border:1px solid #ccc; padding:10px; margin:10px 0;'>";
    echo "<h3>" . htmlspecialchars($fila["titulo"]) . "</h3>";
    echo "<p>" . nl2br(htmlspecialchars($fila["contenido"])) . "</p>";
    echo "<small>Publicado por " . htmlspecialchars($fila["autor"]) . " el " . $fila["fecha_publicacion"] . "</small>";
    echo "</div>";
}

$conn->close();
?>