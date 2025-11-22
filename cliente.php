<?php
require_once "conexion.php";
$conexion = new Conexion();
$enlace = $conexion->conectar();

$filtroCat   = isset($_GET["categoria"]) ? $_GET["categoria"] : "";
$filtroTalla = isset($_GET["filtro"]) ? $_GET["filtro"] : "";

$productos = $conexion->filtrarProductos($filtroCat, $filtroTalla);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Boutique Hello Girl | Catálogo</title>
  <link rel="stylesheet" href="estilos.css">
  <style>
    header img { height:60px; margin-right:15px; }
    header { display:flex; align-items:center; padding:10px; }

    .producto { position: relative; cursor:pointer; padding:15px; border-radius:12px; background:#fff; box-shadow:0 2px 6px rgba(0,0,0,0.15); }
    .filtros-list { font-size: 0.95em; margin-top: 8px; color: #333; }
    .filtros-list span { display:inline-block; margin-right:8px; background:#f5f5f5; padding:4px 8px; border-radius:6px; }
    .producto img { width:100%; height:250px; object-fit:cover; border-radius:10px; }

    .precio { font-size:1.8em; font-weight:bold; color:#e91e63; margin:10px 0; }

    .filtros { display:flex; gap:12px; justify-content:flex-start; margin-bottom:18px; }
    .filtros select { padding:8px 12px; border-radius:8px; border:1px solid #bbb; }

    
    footer a { color:#e91e63; }
  </style>
</head>
<body>
<header>
  <img src="image/logo.png" alt="Logo">
  <h1>Boutique Hello Girl</h1>
</header>

<nav>
  <a href="cliente.php">Inicio</a>
  <a href="contacto.php">Contacto</a>
</nav>

<section class="catalogo">
  <h2>Productos en el catálogo</h2>

  <form method="GET" class="filtros" id="formFiltros">
  
    <div>
      <label>Filtro:</label>
      <select name="filtro" onchange="document.getElementById('formFiltros').submit()">
        <option value="">Todas</option>
        <option value="XS" <?= $filtroTalla=='XS' ? 'selected' : '' ?>>XS</option>
        <option value="S" <?= $filtroTalla=='S' ? 'selected' : '' ?>>S</option>
        <option value="M" <?= $filtroTalla=='M' ? 'selected' : '' ?>>M</option>
        <option value="L" <?= $filtroTalla=='L' ? 'selected' : '' ?>>L</option>
        <option value="XL" <?= $filtroTalla=='XL' ? 'selected' : '' ?>>XL</option>
      </select>
    </div>
  </form>

  <div class="productos" id="listaProductos">
    <?php if (empty($productos)): ?>
      <p>No hay productos disponibles.</p>
    <?php endif; ?>

    <?php foreach ($productos as $p): ?>
      <?php $tallas = $conexion->obtenerTallasPorProducto($p['id']); ?>
      <?php 
        $sinStock = true;
        foreach ($tallas as $t) { if ($t['cantidad'] > 0) { $sinStock = false; break; } }
      ?>
      <div class="producto" data-id="<?= $p['id'] ?>">
        <img src="<?= $p['imagen'] ?>" alt="<?= $p['descripcion'] ?>">
        <h3><?= $p['descripcion'] ?></h3>
        <p class="precio">$<?= number_format($p['precio'],2) ?> MXN</p>
        <div class="filtros-list">
          <?php if ($sinStock): ?>
            <em style="color:red;font-weight:bold;">Poca disponibilidad</em>
          <?php else: ?>
            <?php foreach ($tallas as $t): ?>
              <span><?= $t['talla'] ?>: <?= $t['cantidad'] ?></span>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<script>
const productos = document.querySelectorAll('.producto');
productos.forEach(prod => {
  prod.addEventListener('click', () => {
    const id = prod.dataset.id;

    fetch('telemetria.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'evento=vista&producto_id=' + id
    });

    window.location.href = 'apartar.php?id=' + id;
  });
});
</script>

<footer>
  <p>© 2025 Boutique Hello Girl | Todos los derechos reservados</p>
  <p><a href="politicas.php">Políticas de privacidad</a> | <a href="terminos.php">Términos y condiciones</a></p>
</footer>
</body>
</html>
