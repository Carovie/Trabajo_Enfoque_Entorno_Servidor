<?php

// Iniciar la sesión para acceder a las variables del array de sesión
session_start();

// Vamos a verificar si el usuario llega a esta pantalla no estando logueado o estando logueado como administrador para en estos dos casos, mostrarles mensajes personalizados
$usuario_logueado = false;
$tipo_usuario = null;

//Si el usuario está logueado (está el id almacenado en la variable sesión)
if (isset($_SESSION['id'])) {
    $usuario_logueado = true;
    
    //Una vez que comprobamos que el usuario está logueado, almacenamos el tipo de usuario que es
    if (isset($_SESSION['tipo_usuario'])) {
        $tipo_usuario = $_SESSION['tipo_usuario'];
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="../../css/bootstrap/js/popper.min.js"></script>
    <script src="../../css/bootstrap/js/bootstrap.min.js"></script>
    <title>Pisada Firme</title>
</head>
<body>

<header>
    <div class="logo_titulo">
        <img src="../../img/logo.png" alt="Logo" class="logo">
        <h1>PISADA FIRME</h1>
        <img src="../../img/logo.png" alt="Logo" class="logo">
    </div>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <div class="d-flex flex-column flex-lg-row ms-auto align-items-lg-center">
                    <a class="navbar-brand my-2 my-lg-0" href="../../index.html"><i class="fas fa-home"></i> Inicio</a>
                    <a class="navbar-brand my-2 my-lg-0" href="tienda.php"><i class="fas fa-shop"></i> Tienda</a>
                    <a class="navbar-brand my-2 my-lg-0" href="../login.php"><i class="fas fa-user"></i> Mi Cuenta</a>
                </div>
            </div>
        </div>
    </nav>
</header>

<main>

<div class="container my-3 mb-0">

    <div class="row">
        <div class="col">
        <?php  //Si el usuario no está logueado le mostramos el mensaje personalizado
        if (!$usuario_logueado) {
        echo '<div class="alert alert-warning text-left" role="alert">Por favor, inicia sesión (o regístrate) para poder añadir zapatillas al carrito</div>';
        //Si el usuario está logueado y es de tipo administrador le mostramos el mensaje personalizado
        } else if ($tipo_usuario === 'administrador') {
        echo '<div class="alert alert-info text-left" role="alert">Eres un usuario de tipo administrador. No puedes añadir zapatillas al carrito y por tanto no puedes hacer pedidos</div>';
        }
        ?>
        </div>
    </div>

</div>

<?php  //Si el usuario está logueado y es de tipo cliente, establecemos conexión con la BBDD y recuperamos todos los pedidos que ha realizado para mostrárselos
    if (($usuario_logueado)&&($tipo_usuario === 'cliente')) {
           
        include('../conexion_bbdd.php');


        $usuario_id = $_SESSION['id'];  // Obtenemos el ID del usuario desde la sesión

        /* Consulta para obtener todos los pedidos realizados por el usuario y las zapatillas asociadas a esos pedidos con sus caracteristicas. La consulta utiliza JOINs para combinar datos de tres tablas diferentes que se requieren: pedidos, zapatillas_pedido y zapatillas. El propósito de estos JOINs es relacionar los datos de estas tablas de manera que se pueda obtener la información completa sobre los pedidos y las zapatillas asociadas a esos pedidos.*/
        $sql = "
        SELECT p.id, z.imagen, z.nombre, z.descripcion, z.precio, zp.cantidad, p.precio_total
        FROM pedidos p
        JOIN zapatillas_pedido zp ON p.id = zp.pedido_id
        JOIN zapatillas z ON zp.zapatilla_id = z.id
        WHERE p.usuario_id = ? 
        ORDER BY p.id DESC"; // Ordenar los pedidos por ID de manera descendente

        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result(); 
                
        //Si hay algun pedido realizado por ese usuario en la BBDD
        if ($result->num_rows > 0) {
            $pedido_actual = null; // Para rastrear el ID del pedido actual
            ?>
            <div class="container my-3" style="max-width: 960px; width: 100%;"> 
                <h2 class="text-center mt-3" style="margin-top: -30px !important;">Pedidos Realizados</h2>
            <?php            
            //$result: Es una variable que contiene el resultado de una consulta SQL. fetch_assoc(): Es un método que obtiene una fila del conjunto de resultados como un array asociativo. Esto significa que cada fila de datos se devuelve como un array donde las claves son los nombres de las columnas de la tabla. $row = $result->fetch_assoc(): Esto intenta obtener la siguiente fila del conjunto de resultados en cada iteración del bucle. La variable $row almacenará la fila actual en forma de un array asociativo, y cada vez que el bucle while se ejecuta, obtendrá una nueva fila del conjunto de resultados
            while ($row = $result->fetch_assoc()) {
                //Aquí se está comprobando si el ID del pedido actual ($pedido_actual) es diferente del ID del pedido que viene en la fila ($row['id']). Si son diferentes, significa que hemos pasado a un nuevo pedido y necesitamos empezar a mostrar una nueva tabla para ese pedido.
                if ($pedido_actual !== $row['id']) {
                    // Si estamos cambiando de pedido, cerramos la tabla anterior (excepto la primera vez). Asegura que no se intente cerrar una tabla antes de la primera, ya que al principio no hay ningún pedido en $pedido_actual (es null). Si estamos cambiando de pedido (es decir, ya se ha mostrado al menos una tabla antes), se cierra la tabla anterior y se muestra el total acumulado de ese pedido ($precio_total). Esto es necesario para que, al cambiar de pedido, se cierre la tabla correspondiente y se prepare para mostrar la siguiente tabla del nuevo pedido.
                    if ($pedido_actual !== null) {
                        ?>
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">TOTAL:</td>
                                    <td class="fw-bold align-middle"><?php echo $precio_total; ?>€</td>
                                </tr>
                            </tbody>
                        </table>
                        <?php
                    }
                    // Iniciamos un nuevo pedido creando una nueva tabla para el
                    $pedido_actual = $row['id'];
                    $precio_total = $row['precio_total'];
                    ?>
                    <table class="table table-striped table-hover table-bordered rounded text-center mb-4">
                        <thead class="table-dark">
                            <tr>
                                <th>Pedido</th>
                                <th>Imagen</th>
                                <th>Marca y Modelo</th>
                                <th>Descripción</th>
                                <th>Precio</th>
                                <th>Unidades</th>
                            </tr>
                        </thead>
                        <tbody>
                    <?php
                }
                ?>
                <tr>
                    <td class="align-middle"><?php echo $pedido_actual; ?></td>
                    <td class="align-middle"><img src="../..<?php echo $row['imagen']; ?>" alt="<?php echo $row['nombre']; ?>" class="img-fluid" style="width: 50px;"></td>
                    <td class="align-middle"><?php echo $row['nombre']; ?></td>
                    <td class="align-middle"><?php echo $row['descripcion']; ?></td>
                    <td class="align-middle"><?php echo $row['precio']; ?>€</td>
                    <td class="align-middle"><?php echo $row['cantidad']; ?></td>
                </tr>
                <?php
            }
            // Cerramos la tabla del último pedido, ya fuera del bucle while
            ?>
                    <tr>
                        <td colspan="4" class="text-end fw-bold">TOTAL:</td>
                        <td class="fw-bold align-middle"><?php echo $precio_total; ?>€</td>
                    </tr>
                </tbody>
            </table>
            </div>

            <div class="text-center mt-4 mb-4">
            <a href="../panel_usuario.php" class="btn btn-custom"> Volver</a>
            </div>

        <?php  } else {?>

            <h2 class="text-center mb-3">No has realizado ningun pedido</h2>

        <?php  } ?>


    <?php  }?>

</main>

<footer>
    <div class="pie">
        <div>
            <h4 id="tambien">También puedes visitarnos en:</h4>
            <a href=# target="_blank"> <img src="../../img/youtube.png" alt="Youtube" width="100" height="30"></a>
            <a href=# target="_blank"> <img src="../../img/instagram.png" alt="Instagram" width="100" height="30"></a>
            <a href=# target="_blank"> <img src="../../img/telegram.png" alt="Telegram" width="100" height="30"></a>
        </div>
        <div>
            <h4 class="contacto"><a href=#>Contacta con nosotros</a></h4>
            <h4 class="contacto">Avisos legales</h4>
            <a href="https://www.interior.gob.es/opencms/es/politica-de-cookies/" id="politica">Política de cookies</a>
        </div>
    </div>
</footer>

<script>

</script>

</body>
</html>