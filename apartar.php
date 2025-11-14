<?php
require_once "conexion2.php";
$conexion = new Conexion2();
$conexion->conectar();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die("Producto no válido.");
$producto_id = intval($_GET['id']);


$res = $conexion->enlace->query("SELECT * FROM productos WHERE id = $producto_id");
$producto = $res->fetch_assoc();
if (!$producto) die("Producto no encontrado.");


$tallas_res = $conexion->obtenerTallasProducto($producto_id);
$tallas = [];
while($t = $tallas_res->fetch_assoc()) $tallas[] = $t;

$mensaje = "";
$apartadoExitoso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $talla = $_POST['talla'];

    if (!$nombre || !$telefono || !$talla) {
        $mensaje = "Completa todos los campos.";
    } else {

        $res_cli = $conexion->enlace->query("SELECT * FROM clientes WHERE telefono = '$telefono' LIMIT 1");
        if ($res_cli->num_rows > 0) {
            $cliente = $res_cli->fetch_assoc();
            $cliente_id = $cliente['id'];
        } else {
            $cliente_id = $conexion->insertarCliente($nombre,$telefono);
        }


        $conexion->crearApartado($cliente_id, $producto_id, $talla);
        $apartadoExitoso = true; 
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Apartar Producto | Boutique Hello Girl</title>
<link rel="stylesheet" href="estilos.css">
<script>

<?php if($apartadoExitoso): ?>
    alert("¡Apartado realizado! Tienes 2 días para recogerlo.");
    window.location.href = "cliente.php";
<?php endif; ?>
</script>
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

<section class="apartar" style="padding:50px 20px;text-align:center;">
<h2>Apartar producto</h2>

<?php if ($mensaje): ?>
<p style="color:red;font-weight:bold;margin-bottom:20px;"><?= $mensaje ?></p>
<?php endif; ?>

<div class="producto-detalle" style="display:inline-block;background:var(--color-blanco);padding:20px;border-radius:10px;box-shadow:0 4px 15px rgba(0,0,0,0.15);margin-bottom:30px;">
<img src="<?= $producto['imagen'] ?>" alt="<?= $producto['descripcion'] ?>" style="width:300px;border-radius:10px;">
<h3 style="color:var(--color-secundario);margin:15px 0 10px;"><?= $producto['descripcion'] ?></h3>
<p style="margin:5px 0;">Categoría: <?= $producto['categoria'] ?></p>
<p style="margin:5px 0;">Precio: $<?= number_format($producto['precio'],2) ?> MXN</p>
</div>

<form method="POST" style="max-width:400px;margin:0 auto;display:flex;flex-direction:column;gap:15px;background:var(--color-blanco);padding:20px;border-radius:10px;box-shadow:0 4px 15px rgba(0,0,0,0.15);">
<label for="talla">Talla:</label>
<select name="talla" id="talla" required>
<option value="">-- Elige talla --</option>
<?php foreach ($tallas as $t): ?>
<?php if($t['cantidad']>0): ?>
<option value="<?= $t['talla'] ?>"><?= $t['talla'] ?> (<?= $t['cantidad'] ?> disponibles)</option>
<?php endif; ?>
<?php endforeach; ?>
</select>

<label for="nombre">Nombre:</label>
<input type="text" name="nombre" id="nombre" required placeholder="Tu nombre">

<label for="telefono">Teléfono:</label>
<input type="text" name="telefono" id="telefono" required placeholder="Tu teléfono">

<button type="submit" class="btn">Apartar producto</button>
</form>
</section>

<footer>
<p>© 2025 Boutique Hello Girl | Todos los derechos reservados</p>
</footer>
</body>
</html>
