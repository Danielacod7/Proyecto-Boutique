<?php
require_once "conexion2.php";
$conexion = new Conexion2();
$conexion->conectar();

if(isset($_GET['comprar_id']) && is_numeric($_GET['comprar_id'])){
    $id = intval($_GET['comprar_id']);
    $conexion->enlace->query("UPDATE apartados SET estado='Comprado' WHERE id=$id");
    header("Location: apartados.php");
    exit;
}

$apartados_res = $conexion->enlace->query("SELECT a.id, a.fecha_apartado, a.estado, a.talla,
    c.nombre, c.telefono, p.descripcion, p.imagen, p.precio
    FROM apartados a
    INNER JOIN clientes c ON c.id=a.cliente_id
    INNER JOIN productos p ON p.id=a.producto_id
    WHERE a.estado='Pendiente'
    ORDER BY a.id DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Productos Apartados | Boutique Hello Girl</title>
<link rel="stylesheet" href="estilos.css">
</head>
<body>
<header>
<h1>Boutique Hello Girl</h1>
</header>

<nav>
<a href="admin.php">Inicio</a>
<a href="cliente.php">Catálogo</a>
<a href="apartados.php">Apartados</a>
<a href="compras.php">Compras</a>
</nav>

<section class="apartados-container">
<h2>Lista de Productos Apartados</h2>

<?php if ($apartados_res->num_rows == 0): ?>
<p style="text-align:center;font-weight:bold;">No hay apartados pendientes.</p>
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
<th>Fecha Apartado</th>
<th>Estado</th>
<th>Acción</th>
</tr>
</thead>
<tbody>
<?php while($a = $apartados_res->fetch_assoc()): ?>
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
<td>
<?php if($a['estado']=='Pendiente'): ?>
<a href="apartados.php?comprar_id=<?= $a['id'] ?>" class="btn-estado btn-comprado">Comprado</a>
<?php endif; ?>
</td>
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
