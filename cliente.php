<?php
require_once "conexion.php";
$conexion = new Conexion();
$enlace = $conexion->conectar();

$filtroCat   = isset($_GET["categoria"]) ? $_GET["categoria"] : "";
$filtroTalla = isset($_GET["talla"]) ? $_GET["talla"] : "";

$productos = $conexion->filtrarProductos($filtroCat, $filtroTalla);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Boutique Hello Girl | Catálogo</title>
  <link rel="stylesheet" href="estilos.css">
  <style>
    .producto { position: relative; cursor:pointer; }
    .tallas-list { font-size: 0.95em; margin-top: 8px; color: #333; }
    .tallas-list span { display:inline-block; margin-right:8px; background:#f5f5f5; padding:4px 8px; border-radius:6px; }
    .producto img { width:100%; height:250px; object-fit:cover; border-radius:10px; }
    .filtros { display:flex; gap:12px; justify-content:flex-start; margin-bottom:18px; }
    .filtros select { padding:8px 12px; border-radius:8px; border:1px solid #bbb; }
  </style>
</head>
<body>
<header><h1>Boutique Hello Girl</h1></header>
<nav>
  <a href="cliente.php">Inicio</a>
  <a href="contacto.php">Contacto</a>
</nav>

<section class="catalogo">
  <h2>Productos en el catálogo</h2>

  <form method="GET" class="filtros" id="formFiltros">
    <div>
      <label>Categoría:</label>
      <select name="categoria" onchange="document.getElementById('formFiltros').submit()">
        <option value="">Todas</option>
        <option value="Mujer" <?= $filtroCat=='Mujer' ? 'selected' : '' ?>>Mujer</option>
        <option value="Hombre" <?= $filtroCat=='Hombre' ? 'selected' : '' ?>>Hombre</option>
        <option value="Niña" <?= $filtroCat=='Niña' ? 'selected' : '' ?>>Niña</option>
        <option value="Niño" <?= $filtroCat=='Niño' ? 'selected' : '' ?>>Niño</option>
      </select>
    </div>
    <div>
      <label>Talla:</label>
      <select name="talla" onchange="document.getElementById('formFiltros').submit()">
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
      <div class="producto" data-id="<?= $p['id'] ?>">
        <img src="<?= $p['imagen'] ?>" alt="<?= $p['descripcion'] ?>">
        <h3><?= $p['descripcion'] ?></h3>
        <p class="categoria">Categoría: <?= $p['categoria'] ?></p>
        <p class="precio">$<?= number_format($p['precio'],2) ?> MXN</p>
        <div class="tallas-list">
          <?php if (empty($tallas)): ?>
            <em>Sin tallas disponibles</em>
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
    
    // Guardar telemetría
    fetch('telemetria.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'evento=vista&producto_id=' + id
    });

    window.location.href = 'apartar.php?id=' + id;
  });
});
</script>


<footer><p>© 2025 Boutique Hello Girl | Todos los derechos reservados</p></footer>
</body>
</html>
