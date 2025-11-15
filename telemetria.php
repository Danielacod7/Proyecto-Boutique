<?php
require_once "conexion.php";
$conexion = new Conexion();
$enlace = $conexion->conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $evento = $_POST['evento'] ?? '';
    $producto_id = intval($_POST['producto_id'] ?? 0);

    if ($producto_id && $evento) {
        $sql = "INSERT INTO telemetria (producto_id, evento) VALUES (?, ?)";
        $stmt = $enlace->prepare($sql);
        $stmt->bind_param("is", $producto_id, $evento);
        $stmt->execute();
        $stmt->close();
    }
    exit; 
}

//vista administrativa
$result = $enlace->query("
    SELECT p.descripcion, t.evento, COUNT(*) as total
    FROM telemetria t
    JOIN productos p ON p.id = t.producto_id
    GROUP BY p.id, t.evento
    ORDER BY total DESC
");
$datos = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Vistas | Boutique</title>
<link rel="stylesheet" href="estilos.css">
</head>
<body>
<header><h1>Vistas en los Productos</h1></header>
<nav>
  <a href="admin.php">Inicio</a>
  <a href="cliente.php">Catalogo</a>
  <a href="apartados.php">Apartados</a>
  <a href="compras.php">Compras</a>
  <a href="telemetria.php">Vistas a la página</a>
  <a href="#" id="btnAbrirModal">Subir producto</a>
</nav>


<section class="telemetria-container">
    <table class="telemetria-table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Evento</th>
                <th>Conteo</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($datos as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['descripcion']) ?></td>
                <td class="evento"><?= htmlspecialchars($d['evento']) ?></td>
                <td class="total"><?= intval($d['total']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

</body>
</html>
