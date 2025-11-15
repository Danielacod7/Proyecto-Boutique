<?php
require_once "conexion.php";
$conexion = new Conexion();
$enlace = $conexion->conectar();

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $idDel = intval($_GET['delete']);
    $conexion->eliminarProducto($idDel);
    header("Location: admin.php");
    exit;
}

$filtroCat   = isset($_GET["categoria"]) ? $_GET["categoria"] : "";
$filtroTalla = isset($_GET["talla"]) ? $_GET["talla"] : "";


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["imagen"])) {

    $descripcion = $_POST["descripcion"];
    $categoria   = $_POST["categoria"];
    $precio      = floatval($_POST["precio"]);

    $nombreImg = time() . "_" . basename($_FILES["imagen"]["name"]);
    $rutaDestino = "image/" . $nombreImg;

    if (!is_dir('image')) mkdir('image', 0777, true);

    if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $rutaDestino)) {
       
        $producto_id = $conexion->insertarProducto($rutaDestino, $descripcion, $categoria, $precio);

       
        $tallasPosibles = ['XS','S','M','L','XL'];
        $tallasArray = [];
        foreach ($tallasPosibles as $t) {
    
            if (isset($_POST["talla_" . $t])) {
                $qty = isset($_POST["qty_" . $t]) ? intval($_POST["qty_" . $t]) : 0;
                if ($qty > 0) $tallasArray[$t] = $qty;
            }
        }

        if (!empty($tallasArray)) {
            $conexion->insertarTallas($producto_id, $tallasArray);
        } else {
         
            $conexion->recalcularCantidadProducto($producto_id);
        }

        echo "<script>alert('Producto agregado correctamente'); window.location='admin.php';</script>";
        exit;
    } else {
        echo "<script>alert('Error al subir la imagen');</script>";
    }
}

$productos = $conexion->filtrarProductos($filtroCat, $filtroTalla);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Panel de Administración | Boutique</title>
  <link rel="stylesheet" href="estilos.css">
  <style>
  
    .producto { position: relative; }
    .tallas-list { font-size: 0.95em; margin-top: 8px; color: #333; }
    .tallas-list span { display:inline-block; margin-right:8px; background:#f5f5f5; padding:4px 8px; border-radius:6px; }
    .context-menu { display:none; position:absolute; background:#fff; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.15); z-index:2000; width:160px; }
    .context-menu ul { list-style:none; margin:0; padding:6px 0; }
    .context-menu li { padding:10px 14px; cursor:pointer; border-bottom:1px solid #eee; }
    .context-menu li:last-child{ border-bottom:none; }
    .context-menu li:hover{ background:#f2f2f2; }
    .producto img { width:100%; height:250px; object-fit:cover; border-radius:10px; }

    .filtros { display:flex; gap:12px; justify-content:flex-start; margin-bottom:18px; }
    .filtros select { padding:8px 12px; border-radius:8px; border:1px solid #bbb; }
  </style>
</head>
<body>
<header><h1>Panel de Administración</h1></header>
<nav>
  <a href="admin.php">Inicio</a>
  <a href="cliente.php">Catalogo</a>
  <a href="apartados.php">Apartados</a>
  <a href="compras.php">Compras</a>
  <a href="telemetria.php">Vistas a la página</a>
  <a href="#" id="btnAbrirModal">Subir producto</a>
</nav>


<section class="admin-contenido">
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
      <p>No hay productos.</p>
    <?php endif; ?>

    <?php foreach ($productos as $p): ?>
      <?php $tallas = $conexion->obtenerTallasPorProducto($p['id']); ?>
      <div class="producto" data-id="<?= $p['id'] ?>">
        <img src="<?= htmlspecialchars($p['imagen']) ?>" alt="Producto">
        <h3><?= htmlspecialchars($p['descripcion']) ?></h3>
        <p class="categoria">Categoría: <?= htmlspecialchars($p['categoria']) ?></p>
        <p class="precio">$<?= number_format($p['precio'],2) ?> MXN</p>
        <p class="cantidad">Total: <?= intval($p['cantidad']) ?></p>

        <div class="tallas-list">
          <?php if (empty($tallas)): ?>
            <em>Sin tallas registradas</em>
          <?php else: ?>
            <?php foreach ($tallas as $t): ?>
              <span><?= htmlspecialchars($t['talla']) ?>: <?= intval($t['cantidad']) ?></span>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<div id="modalProducto" class="modal">
  <div class="modal-contenido">
    <span class="cerrar">&times;</span>
    <h2>Subir nuevo producto</h2>
    <form method="POST" enctype="multipart/form-data">
      <label>Imagen:</label>
      <input type="file" name="imagen" required>

      <label>Descripción:</label>
      <textarea name="descripcion" rows="3" required></textarea>

      <label>Categoría:</label>
      <select name="categoria" required>
        <option value="">Seleccionar...</option>
        <option value="Mujer">Mujer</option>
        <option value="Hombre">Hombre</option>
        <option value="Niña">Niña</option>
        <option value="Niño">Niño</option>
      </select>

      <fieldset style="border:1px solid #eee;padding:10px;border-radius:8px;">
        <legend>Tallas (marca y pon cantidad)</legend>
        <?php foreach (['XS','S','M','L','XL'] as $t): ?>
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
            <input type="checkbox" name="talla_<?= $t ?>" id="talla_<?= $t ?>">
            <label for="talla_<?= $t ?>"><?= $t ?></label>
            <input type="number" name="qty_<?= $t ?>" min="0" placeholder="cantidad" style="width:100px;padding:6px;border-radius:6px;border:1px solid #ccc;">
          </div>
        <?php endforeach; ?>
      </fieldset>

      <label>Precio:</label>
      <input type="number" step="0.01" name="precio" required>

      <button type="submit" class="btn">Agregar al catálogo</button>
    </form>
  </div>
</div>

<div id="menuContextual" class="context-menu">
  <ul>
    <li id="btnEditar">✏ Editar</li>
    <li id="btnEliminar">🗑 Eliminar</li>
  </ul>
</div>

<script>
const modal = document.getElementById('modalProducto');
const abrir = document.getElementById('btnAbrirModal');
const cerrar = document.querySelector('.cerrar');

abrir.onclick = () => modal.style.display = 'flex';
cerrar.onclick = () => modal.style.display = 'none';
window.onclick = e => { if (e.target === modal) modal.style.display = 'none'; };

const menu = document.getElementById('menuContextual');
let idSeleccionado = null;

document.querySelectorAll('.producto').forEach(prod => {
  prod.addEventListener('contextmenu', function(e) {
    e.preventDefault();
    idSeleccionado = this.dataset.id;
    menu.style.left = e.pageX + 'px';
    menu.style.top = e.pageY + 'px';
    menu.style.display = 'block';
  });
});

document.addEventListener('click', function(e) {
  if (menu.style.display === 'block' && !menu.contains(e.target)) menu.style.display = 'none';
});

document.getElementById('btnEliminar').onclick = function() {
  if (confirm('¿Seguro que deseas eliminar este producto?')) {
    window.location = 'admin.php?delete=' + idSeleccionado;
  }
};

document.getElementById('btnEditar').onclick = function() {
  window.location = 'editar.php?id=' + idSeleccionado;
};
</script>

<footer><p>© 2025 Boutique Elegance | Administración</p></footer>
</body>
</html>
