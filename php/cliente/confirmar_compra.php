<?php
session_start();
require_once '../conexion_bbdd.php'; 

// Verificar que los datos enviados desde el formulario oculto de carrito.php (contenido del carrito y el precio total) están disponibles.
if (isset($_POST['carrito']) && isset($_POST['total'])) {

    // La función json_decode() toma una cadena JSON y la convierte en una estructura de datos que PHP puede manejar. El parámetro true convierte el JSON a un array asociativo en lugar de un objeto de PHP.
    $carrito = json_decode($_POST['carrito'], true);
    $total = $_POST['total'];

    // Verificar si el usuario está logueado, ya que necesitamos el usuario_id para el pedido
    if (isset($_SESSION['id'])) {
        $usuario_id = $_SESSION['id']; // El ID del usuario obtenido desde el array de la sesión
        
        // Insertamos el pedido en la tabla 'pedidos'
        $query = "INSERT INTO pedidos (usuario_id, precio_total) VALUES (?, ?)";
        $stmt = $con->prepare($query);
        $stmt->bind_param("id", $usuario_id, $total);  
        $stmt->execute();
        
        // Obtenemos el ID del pedido insertado. $stmt->insert_id: es un atributo de un objeto de la clase mysqli_stmt, que se utiliza cuando se hace una inserción en una BBDD. Después de ejecutar la consulta INSERT, que inserta un nuevo pedido en la tabla pedidos, el valor de $stmt->insert_id es el ID generado automáticamente para ese nuevo registro (en este caso, el id del pedido recien insertado en la tabla pedidos)
        $pedido_id = $stmt->insert_id;
        $stmt->close();

        // Insertamos las zapatillas de las que se compone el pedido y el numero del pedido asociado, en la tabla 'zapatillas_pedido'
        foreach ($carrito as $producto) {
            $zapatilla_id = $producto['id'];
            $cantidad = $producto['unidades'];
            $precio = $producto['precio'];

            $query = "INSERT INTO zapatillas_pedido (pedido_id, zapatilla_id, cantidad, precio) VALUES (?, ?, ?, ?)";
            $stmt = $con->prepare($query);
            $stmt->bind_param("iiid", $pedido_id, $zapatilla_id, $cantidad, $precio);
            $stmt->execute();
        }
        $stmt->close();

        //Mostramos en un alert confirmación de pedido realizado correctamente
        echo "<script>
                alert('Pedido recibido correctamente. Nos ponemos a prepararlo');
                // Vaciar el carrito en LocalStorage
                localStorage.removeItem('carrito');
                // Redirigir al usuario
                window.location.href = '../panel_usuario.php';
              </script>";

        $con->close();

    } else {
        echo "<script>
        alert('El usuario no está logueado');
        window.location.href = '../login.php';
        </script>";
    }
} else {
    echo "<script>
    alert('No se han recibido datos del carrito');
    window.location.href = 'carrito.php';
    </script>";
}
?>