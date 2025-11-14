<?php
class Conexion2 {

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
            die("Error al conectar con MySQL: " . mysqli_connect_error());
        }

        mysqli_set_charset($this->enlace, "utf8mb4");
    }


    public function insertarCliente($nombre, $telefono) {
        $stmt = $this->enlace->prepare("
            INSERT INTO clientes (nombre, telefono)
            VALUES (?, ?)
        ");
        $stmt->bind_param("ss", $nombre, $telefono);
        $stmt->execute();
        $id = $this->enlace->insert_id;
        $stmt->close();
        return $id;
    }

 
    public function crearApartado($cliente_id, $producto_id, $talla) {

        $stmt = $this->enlace->prepare("
            INSERT INTO apartados (cliente_id, producto_id, talla, fecha_apartado, estado)
            VALUES (?, ?, ?, NOW(), 'Pendiente')
        ");

        $stmt->bind_param("iis", $cliente_id, $producto_id, $talla);
        $stmt->execute();
        $id = $this->enlace->insert_id;
        $stmt->close();

        $this->reducirStock($producto_id, $talla);

        return $id;
    }


    public function reducirStock($producto_id, $talla) {
        $stmt = $this->enlace->prepare("
            UPDATE producto_tallas 
            SET cantidad = cantidad - 1 
            WHERE producto_id = ? AND talla = ? AND cantidad > 0
        ");
        $stmt->bind_param("is", $producto_id, $talla);
        $stmt->execute();
        $stmt->close();
    }


    public function obtenerProductosConTallas() {
        return $this->enlace->query("SELECT * FROM productos");
    }


    public function obtenerTallasProducto($producto_id) {
        $stmt = $this->enlace->prepare("
            SELECT talla, cantidad FROM producto_tallas WHERE producto_id = ?
        ");
        $stmt->bind_param("i", $producto_id);
        $stmt->execute();
        return $stmt->get_result();
    }


    public function obtenerApartados() {
        $sql = "
            SELECT 
                a.id,
                a.fecha_apartado,
                a.estado,
                a.talla,
                c.nombre,
                c.telefono,
                p.descripcion,
                p.imagen,
                p.precio
            FROM apartados a
            INNER JOIN clientes c ON c.id = a.cliente_id
            INNER JOIN productos p ON p.id = a.producto_id
            ORDER BY a.id DESC
        ";
        return $this->enlace->query($sql);
    }

 
    public function obtenerClientePorTelefono($telefono) {
        $stmt = $this->enlace->prepare("SELECT * FROM clientes WHERE telefono = ? LIMIT 1");
        $stmt->bind_param("s", $telefono);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

  
    public function obtenerCantidadTalla($producto_id, $talla) {
        $stmt = $this->enlace->prepare("
            SELECT cantidad FROM producto_tallas 
            WHERE producto_id = ? AND talla = ?
        ");
        $stmt->bind_param("is", $producto_id, $talla);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result ? intval($result['cantidad']) : 0;
    }


    public function actualizarCantidadTalla($producto_id, $talla, $cantidad) {
        $stmt = $this->enlace->prepare("
            UPDATE producto_tallas 
            SET cantidad = ? 
            WHERE producto_id = ? AND talla = ?
        ");
        $stmt->bind_param("iis", $cantidad, $producto_id, $talla);
        $stmt->execute();
        $stmt->close();
    }
}
?>
