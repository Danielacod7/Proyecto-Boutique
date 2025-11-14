<?php
require_once "conexion.php";
$conexion = new Conexion();
$enlace = $conexion->conectar();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID inválido");
}
$id = intval($_GET['id']);

$product = $conexion->obtenerProductoPorId($id);
if (!$product) die("Producto no encontrado");

$tallas = $conexion->obtenerTallasPorProducto($id);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $descripcion = $_POST['descripcion'];
    $precio = floatval($_POST['precio']);

    $conexion->actualizarProducto($id, $descripcion, $precio);

    $tallasPosibles = ['XS','S','M','L','XL'];
    foreach ($tallasPosibles as $t) {
        $checked = isset($_POST["talla_" . $t]);
        $qty = isset($_POST["qty_" . $t]) ? intval($_POST["qty_" . $t]) : 0;

        $existeId = null;
        foreach ($tallas as $row) {
            if ($row['talla'] === $t) { $existeId = $row['id']; break; }
        }

        if ($checked) {
            if ($qty > 0) {
                if ($existeId) {
                    $conexion->actualizarTallaPorId($existeId, $qty);
                } else {
                    $conexion->agregarOActualizarTalla($id, $t, $qty);
                }
            } else {
 
                if ($existeId) $conexion->eliminarTallaPorId($existeId);
            }
        } else {
   
            if ($existeId) $conexion->eliminarTallaPorId($existeId);
        }
    }


    echo "<script>alert('Producto actualizado'); window.location='admin.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Editar producto</title>
  <link rel="stylesheet" href="estilos.css">
  <style>
    .talla-row { display:flex; gap:10px; align-items:center; margin-bottom:8px; }
    .talla-row input[type="number"] { width:120px; padding:6px; border-radius:6px; border:1px solid #ccc; }
  </style>
</head>
<body>
<header><h1>Editar producto</h1></header>
<section style="max-width:800px;margin:30px auto;background:#fff;padding:20px;border-radius:10px;">
  <form method="POST">
    <div style="display:flex;gap:20px;align-items:flex-start;">
      <div style="flex:1;">
        <img src="<?= htmlspecialchars($product['imagen']) ?>" style="width:320px;height:320px;object-fit:cover;border-radius:8px;">
      </div>
      <div style="flex:2;">
        <label>Descripción:</label>
        <textarea name="descripcion" rows="4" style="width:100%;padding:8px;border-radius:6px;border:1px solid #ccc;"><?= htmlspecialchars($product['descripcion']) ?></textarea>

        <label>Precio:</label>
        <input type="number" step="0.01" name="precio" value="<?= number_format($product['precio'],2,'.','') ?>" style="width:150px;padding:8px;border-radius:6px;border:1px solid #ccc;">

        <fieldset style="margin-top:12px;padding:12px;border-radius:8px;border:1px solid #eee;">
          <legend>Tallas</legend>
          <?php
            $map = [];
            foreach ($tallas as $r) $map[$r['talla']] = $r;
            $tallasPosibles = ['XS','S','M','L','XL'];
            foreach ($tallasPosibles as $t):
              $exists = isset($map[$t]);
              $qtyVal = $exists ? intval($map[$t]['cantidad']) : 0;
          ?>
            <div class="talla-row">
              <input type="checkbox" id="talla_<?= $t ?>" name="talla_<?= $t ?>" <?= $exists ? 'checked' : '' ?>>
              <label for="talla_<?= $t ?>"><?= $t ?></label>
              <input type="number" name="qty_<?= $t ?>" min="0" value="<?= $qtyVal ?>">
              <?php if ($exists): ?>
                <small style="color:#666;">(id <?= $map[$t]['id'] ?>)</small>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </fieldset>

        <div style="margin-top:14px;">
          <button type="submit" class="btn">Guardar cambios</button>
          <a href="admin.php" class="btn" style="background:#bbb;margin-left:8px;text-decoration:none;padding:8px 12px;border-radius:6px;color:#fff;">Cancelar</a>
        </div>
      </div>
    </div>
  </form>
</section>
</body>
</html>
