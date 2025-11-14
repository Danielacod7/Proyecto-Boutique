<?php
require_once "conexion2.php";
$conexion = new Conexion2();
$conexion->conectar();

$compras_res = $conexion->enlace->query("SELECT a.id, a.fecha_apartado, a.estado, a.talla,
    c.nombre, c.telefono, p.descripcion, p.imagen, p.precio
    FROM apartados a
    INNER JOIN clientes c ON c.id=a.cliente_id
    INNER JOIN productos p ON p.id=a.producto_id
    WHERE a.estado='Comprado'
    ORDER BY a.id DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Compras Realizadas | Boutique Hello Girl</title>
<link rel="stylesheet" href="estilos.css">
</head>
<body>
<header>
<h1>Boutique Hello Girl</h1>
</header>

<nav>
<a href="cliente.php">Inicio</a>
<a href="#">Catálogo</a>
<a href="#">Contacto</a>
</nav>

<section class="apartados-container">
<h2>Lista de Compras Realizadas</h2>

<?php if ($compras_res->num_rows == 0): ?>
<p style="text-align:center;font-weight:bold;">No hay compras registradas.</p>
<?php else: ?>
<table>
<thead>
<tr>
<th>ID</th>
<th>Cliente</th>
<th>Teléfono</th>
<th>Producto</th>
<th>Imagen</th>
<th>Talla</th>
<th>Precio</th>
<th>Fecha Compra</th>
<th>Estado</th>
</tr>
</thead>
<tbody>
<?php while($a = $compras_res->fetch_assoc()): ?>
<tr>
<td><?= $a['id'] ?></td>
<td><?= htmlspecialchars($a['nombre']) ?></td>
<td><?= htmlspecialchars($a['telefono']) ?></td>
<td><?= htmlspecialchars($a['descripcion']) ?></td>
<td><img src="<?= $a['imagen'] ?>" alt="<?= htmlspecialchars($a['descripcion']) ?>"></td>
<td><?= $a['talla'] ?></td>
<td>$<?= number_format($a['precio'],2) ?> MXN</td>
<td><?= $a['fecha_apartado'] ?></td>
<td><span class="estado <?= $a['estado'] ?>"><?= $a['estado'] ?></span></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
<?php endif; ?>
</section>

<footer>
<p>© 2025 Boutique Hello Girl | Todos los derechos reservados</p>
</footer>
</body>
</html>
