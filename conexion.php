<?php
class Conexion {
    private $servidor = "localhost";
    private $base = "boutique";
    private $usuario = "root";
    private $password = "karuto.cos.7";
    private $puerto = 3307;
    public $enlace = null;

    public function conectar() {
        $this->enlace = mysqli_connect(
            $this->servidor,
            $this->usuario,
            $this->password,
            $this->base,
            $this->puerto
        );

        if (!$this->enlace) {
            throw new Exception("Error al conectar: " . mysqli_connect_error());
        }


        mysqli_set_charset($this->enlace, "utf8mb4");

        return $this->enlace;
    }

    public function insertarProducto($imagen, $descripcion, $categoria, $precio) {
        if ($this->enlace == null) $this->conectar();

        $stmt = $this->enlace->prepare("
            INSERT INTO productos (imagen, descripcion, categoria, precio, cantidad)
            VALUES (?, ?, ?, ?, 0)
        ");
        if (!$stmt) throw new Exception("Prepare fail insertarProducto: " . $this->enlace->error);
        $stmt->bind_param("sssd", $imagen, $descripcion, $categoria, $precio);
        $ok = $stmt->execute();
        if (!$ok) throw new Exception("Execute fail insertarProducto: " . $stmt->error);
        $id = $this->enlace->insert_id;
        $stmt->close();
        return $id;
    }


    public function insertarTallas($producto_id, $tallas) {
        if ($this->enlace == null) $this->conectar();
        $stmt = $this->enlace->prepare("
            INSERT INTO producto_tallas (producto_id, talla, cantidad) VALUES (?, ?, ?)
        ");
        if (!$stmt) throw new Exception("Prepare fail insertarTallas: " . $this->enlace->error);
        foreach ($tallas as $talla => $cantidad) {
            $stmt->bind_param("isi", $producto_id, $talla, $cantidad);
            if (!$stmt->execute()) throw new Exception("Execute fail insertarTallas: " . $stmt->error);
        }
        $stmt->close();

        $this->recalcularCantidadProducto($producto_id);
        return true;
    }

  
    public function obtenerProductos() {
        if ($this->enlace == null) $this->conectar();
        $sql = "SELECT * FROM productos ORDER BY id DESC";
        $res = mysqli_query($this->enlace, $sql);
        if (!$res) throw new Exception("Error obtenerProductos: " . mysqli_error($this->enlace));
        return mysqli_fetch_all($res, MYSQLI_ASSOC);
    }
    public function filtrarProductos($categoria = "", $talla = "") {
        if ($this->enlace == null) $this->conectar();

        if ($talla !== "") {
            $stmt = $this->enlace->prepare("
                SELECT p.* 
                FROM productos p
                JOIN producto_tallas pt ON pt.producto_id = p.id
                WHERE pt.talla = ?
                " . ($categoria ? "AND p.categoria = ?" : "") . "
                GROUP BY p.id
                ORDER BY p.id DESC
            ");
            if (!$stmt) throw new Exception("Prepare fail filtrarProductos (with talla): " . $this->enlace->error);
            if ($categoria) {
                $stmt->bind_param("ss", $talla, $categoria);
            } else {
                $stmt->bind_param("s", $talla);
            }
            $stmt->execute();
            $resultado = $stmt->get_result();
            $data = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
            $stmt->close();
            return $data;
        } else {
            if ($categoria !== "") {
                $stmt = $this->enlace->prepare("SELECT * FROM productos WHERE categoria = ? ORDER BY id DESC");
                if (!$stmt) throw new Exception("Prepare fail filtrarProductos (categoria): " . $this->enlace->error);
                $stmt->bind_param("s", $categoria);
                $stmt->execute();
                $res = $stmt->get_result();
                $data = mysqli_fetch_all($res, MYSQLI_ASSOC);
                $stmt->close();
                return $data;
            } else {
                return $this->obtenerProductos();
            }
        }
    }

    public function obtenerTallasPorProducto($producto_id) {
        if ($this->enlace == null) $this->conectar();
        $stmt = $this->enlace->prepare("SELECT * FROM producto_tallas WHERE producto_id = ? ORDER BY id ASC");
        if (!$stmt) throw new Exception("Prepare fail obtenerTallasPorProducto: " . $this->enlace->error);
        $stmt->bind_param("i", $producto_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = mysqli_fetch_all($res, MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }

    public function recalcularCantidadProducto($producto_id) {
        if ($this->enlace == null) $this->conectar();
        $stmt = $this->enlace->prepare("
            SELECT SUM(cantidad) as total FROM producto_tallas WHERE producto_id = ?
        ");
        $stmt->bind_param("i", $producto_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $total = intval($row['total']) ?: 0;
        $upd = $this->enlace->prepare("UPDATE productos SET cantidad = ? WHERE id = ?");
        $upd->bind_param("ii", $total, $producto_id);
        $upd->execute();
        $upd->close();
        $stmt->close();
        return $total;
    }

    public function eliminarProducto($id) {
        if ($this->enlace == null) $this->conectar();

        $stmt = $this->enlace->prepare("SELECT imagen FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        if ($row && !empty($row['imagen']) && file_exists($row['imagen'])) {
            @unlink($row['imagen']);
        }

        $stmt2 = $this->enlace->prepare("DELETE FROM productos WHERE id = ?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $stmt2->close();
        return true;
    }

    public function obtenerProductoPorId($id) {
        if ($this->enlace == null) $this->conectar();
        $stmt = $this->enlace->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        return $row;
    }

    public function actualizarProducto($id, $descripcion, $precio) {
        if ($this->enlace == null) $this->conectar();
        $stmt = $this->enlace->prepare("UPDATE productos SET descripcion = ?, precio = ? WHERE id = ?");
        if (!$stmt) throw new Exception("Prepare fail actualizarProducto: " . $this->enlace->error);
        $stmt->bind_param("sdi", $descripcion, $precio, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function actualizarTallaPorId($id, $cantidad) {
        if ($this->enlace == null) $this->conectar();
        $stmt = $this->enlace->prepare("UPDATE producto_tallas SET cantidad = ? WHERE id = ?");
        $stmt->bind_param("ii", $cantidad, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function agregarOActualizarTalla($producto_id, $talla, $cantidad) {
        if ($this->enlace == null) $this->conectar();
   
        $stmt = $this->enlace->prepare("SELECT id FROM producto_tallas WHERE producto_id = ? AND talla = ?");
        $stmt->bind_param("is", $producto_id, $talla);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        if ($row) {
            $this->actualizarTallaPorId($row['id'], $cantidad);
        } else {
            $ins = $this->enlace->prepare("INSERT INTO producto_tallas (producto_id, talla, cantidad) VALUES (?, ?, ?)");
            $ins->bind_param("isi", $producto_id, $talla, $cantidad);
            $ins->execute();
            $ins->close();
        }

        $this->recalcularCantidadProducto($producto_id);
        return true;
    }

    public function eliminarTallaPorId($id) {
        if ($this->enlace == null) $this->conectar();
        $stmt = $this->enlace->prepare("SELECT producto_id FROM producto_tallas WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        if ($row) {
            $producto_id = $row['producto_id'];
            $del = $this->enlace->prepare("DELETE FROM producto_tallas WHERE id = ?");
            $del->bind_param("i", $id);
            $del->execute();
            $del->close();
            $this->recalcularCantidadProducto($producto_id);
        }
        return true;
    }

    public function __destruct() {
        if ($this->enlace != null) {
            mysqli_close($this->enlace);
        }
    }

    public function obtenerProductosConTallas() {
    if ($this->enlace == null) {
        $this->conectar();
    }

    $query = "SELECT * FROM productos ORDER BY id DESC";
    $result = mysqli_query($this->enlace, $query);

    $productos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['tallas'] = [];

        $q2 = "SELECT talla, cantidad FROM tallas WHERE producto_id = " . $row['id'];
        $res2 = mysqli_query($this->enlace, $q2);

        while ($t = mysqli_fetch_assoc($res2)) {
            $row['tallas'][] = $t;
        }

        $productos[] = $row;
    }

    return $productos;
}

}
?>
