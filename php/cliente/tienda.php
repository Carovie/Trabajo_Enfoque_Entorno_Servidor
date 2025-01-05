<?php

// Iniciar la sesión para acceder a las variables del array de sesión
session_start();

// Incluir la conexión a la base de datos
include_once("../conexion_bbdd.php");

// Obtener las zapatillas existentes en la base de datos
$sql = "SELECT id, nombre, descripcion, precio, imagen FROM zapatillas";
$result = $con->query($sql);
$zapatillas = [];

// Verificar si hay al menos unas zapatillas. Si las hay, las pasamos a un array
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $zapatillas[] = $row;
    }
}
// Cerrar la conexión
$con->close();

// Vamos a verificar si el usuario llega a la tienda no estando logueado o estando logueado como administrador para en estos dos casos, deshabilitar el botón de Añadir Carrito y que solo pueda hacer un pedido si el usuario está logueado y es de tipo cliente
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
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--Enlace para incluir las librerias de Bootstrap-->
    <link rel="stylesheet" href="../../css/bootstrap/css/bootstrap.min.css">
    <!--Enlace para incluir mi hoja de estilos.css-->
    <link rel="stylesheet" href="../../css/estilos.css">
    <!--Enlace para incluir los iconos de la barra de navegación-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!--Enlaces para poder incluir la funcionalidad JavaScript del navbar-toggler (menú de hamburguesa que aparece al reducir las pantallas)-->
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

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
                    aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">

                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarContent">

                    <div class="d-flex flex-column flex-lg-row ms-auto align-items-lg-center">
                        <a class="navbar-brand my-2 my-lg-0" href="../../index.html"><i class="fas fa-home"></i> Inicio</a>
                        <a class="navbar-brand my-2 my-lg-0" href="carrito.php"><i class="fas fa-shopping-cart"></i> Carrito</a>
                        <a class="navbar-brand my-2 my-lg-0" href="../login.php"><i class="fas fa-user"></i> Mi Cuenta</a>
                    </div>
                </div>
            </div>
        </nav>

    </header>

    <main>

    <div class="container my-3">

        <div class="row">
            <div class="col">
                <?php  //Si el usuario no está logueado
                if (!$usuario_logueado) {
                echo '<div class="alert alert-warning text-left" role="alert">Por favor, inicia sesión (o regístrate) para poder añadir zapatillas al carrito.</div>';
                //Si el usuario está logueado y es de tipo administrador
                } else if ($tipo_usuario === 'administrador') {
                echo '<div class="alert alert-info text-left" role="alert">Eres un usuario de tipo administrador. No puedes añadir zapatillas al carrito.</div>';
                }
                ?>
            </div>
        </div>


            <!-- Si existe un mensaje de bienvenida en el array de la sesión, lo mostramos porque será que el usuario tipo cliente se acaba de loguear, ya que se le redirigé a esta pantalla con ese mensaje almacenado en el array de sesiones. Y en cuanto se muestra el mensaje lo eliminamos para que en posteriores accesos a esta pantalla no se vuelva a mostrar el mensaje -->
            <?php if (isset($_SESSION['login_message'])) { ?>
                <div class="alert alert-success text-center" role="alert">
                    <?php echo $_SESSION['login_message']; ?>
                </div>
                <?php unset($_SESSION['login_message']); // Eliminamos el mensaje del array de la sesión después de mostrarlo ?>
            <?php } ?>        

        <!--Si el array no está vacío, esto es, si se obtuvo una o más zapatillas de la BBDD-->
        <?php if (!empty($zapatillas)) { ?>
        <h2 class="text-center mb-3">Zapatillas en stock</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <!--Recorremos el array obteniendo una a una y mostrandolas con sus características-->
            <?php foreach ($zapatillas as $zapatilla) {?>
            <div class="col">
                <div class="card h-100">
                    <img src="../..<?= $zapatilla['imagen'] ?>" class="card-img-top" alt="<?= $zapatilla['nombre'] ?>"> 
                    <div class="card-body">
                        <h5 class="card-title"><?= $zapatilla['nombre'] ?></h5>
                        <p class="card-text"><?= $zapatilla['descripcion'] ?></p>
                        <p class="text-success"><?= $zapatilla['precio'] ?>€</p>
                    
                        <?php

                        // Variable para definir si el botón añadir al carrito debe estar deshabilitado
                        $boton_añadir_carrito_disabled = false;

                        // Si el usuario no está logueado o está logueado y es de tipo administrador
                        if (!$usuario_logueado || $tipo_usuario === 'administrador') {
                            $boton_añadir_carrito_disabled = true; 
                        }

                        // Si la variable que va a a definir el estado del botón de agregar al carrito es true, el usuario no está logueado o es administrador, asi que mostramos el botón como deshabilitado 
                        if ($boton_añadir_carrito_disabled) { 
                        ?>  
                        <button class="btn btn-custom btn-disabled" disabled>
                        Añadir al Carrito
                        </button>
                        <!--Si la variable que va a definir el estado del botón de agregar al carrito es false, el usuario está logueado y es de tipo cliente, asi que mostramos el botón como agregar al carrito habilitado. Añadimos un atributo data-id al botón para almacenar el id de la zapatilla de forma accesible desde JavaScript. data-id es un atributo personalizado que sigue el patrón de los atributos data-* en HTML. Los atributos data-* son una característica de HTML5 que permite agregar atributos personalizados a los elementos HTML. Cualquier atributo que empiece con data- es válido, y su propósito es almacenar información adicional que no afecta a la representación del documento. El uso de estos atributos es útil porque permiten almacenar datos de manera sencilla en los elementos HTML, y luego puedes acceder a esos datos desde JavaScript con facilidad.-->
                        <?php } else { ?>
                            <button type="button" class="btn btn-custom" data-id="<?= $zapatilla['id'] ?>"
                            data-nombre="<?= $zapatilla['nombre'] ?>" data-descripcion="<?= $zapatilla['descripcion'] ?>" data-precio="<?= $zapatilla['precio'] ?>"
                            data-imagen="<?= $zapatilla['imagen'] ?>">Añadir al Carrito</button>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        <?php } else {?> <!--Si el array está vacio, esto es, no hay zapatillas en la BBDD-->
            <div class="text-center" style="display: flex; justify-content: center; align-items: center; height: 200px; width: 100%;">
                <h2 class="mb-3" style="white-space: nowrap;">No hay zapatillas en stock en este momento</h2>
            </div>
        <?php } ?>
    </div>

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
                <a href="https://www.interior.gob.es/opencms/es/politica-de-cookies/" id="politica">Política de
                    cookies</a>
            </div>
        </div>
    </footer>

    <script>
    // Esperamos que todo el DOM esté cargado antes de ejecutar el código contenido en la función
    document.addEventListener("DOMContentLoaded", function() {
           
        // Obtenemos todos los botones "Añadir al Carrito" de todas las zapatillas
        const botones = document.querySelectorAll('.btn-custom');

        // Iteramos sobre cada uno de los botones
        botones.forEach(function(boton) {
        
            //Estamos todo el rato a la escucha y cuando se hace click sobre alguno de ellos, se ejecuta el código de la función. 
            boton.addEventListener('click', function() {

            // Obtenemos el id de la zapatilla desde el atributo data-id y el resto de sus caracteristicas definidas en los atributos data-*
            const zapatillaId = boton.getAttribute('data-id');
            const zapatillaNombre = boton.getAttribute('data-nombre');
            const zapatillaDescripcion = boton.getAttribute('data-descripcion');
            const zapatillaPrecio = boton.getAttribute('data-precio');
            const zapatillaImagen = boton.getAttribute('data-imagen');

            // Si ya existe un carrito en localStorage, lo obtenemos. Si no existe un valor almacenado con la clave 'carrito', getItem() devolverá null y con || [] lo que se hace es crear un array vacio. JSON.parse() convierte una cadena de texto que está en formato JSON en un objeto JavaScript.
            let carrito = JSON.parse(localStorage.getItem('carrito')) || [];

            // Comprobamos si la zapatilla ya está en el carrito. Para verificar si la zapatilla ya existe en el carrito, usamos findIndex, que nos da el índice del primer elemento en el array que cumpla con la condición especificada en la función de prueba. Si la zapatilla no está en el carrito, findIndex devuelve -1. 
            const index = carrito.findIndex(function(item) {
                return item.id === zapatillaId;
                });

            if (index !== -1) {
                    // Si ya está, aumentamos en 1 el numero de unidades
                    carrito[index].unidades++;
                } else {
                    // Si no está, la creamos y la añadimos al carrito con todas sus características y con 1 unidad
                    const nuevaZapatilla = {
                        id: zapatillaId,
                        nombre: zapatillaNombre,
                        descripcion: zapatillaDescripcion,
                        precio: zapatillaPrecio,
                        imagen: zapatillaImagen,
                        unidades: 1,
                    };
                    carrito.push(nuevaZapatilla);
                }

                // Guardamos el carrito actualizado en localStorage
                localStorage.setItem('carrito', JSON.stringify(carrito));

                // Mostramos un alert con el mensaje de que la zapatilla se añadió al carrito
                alert('Zapatillas modelo ' +zapatillaNombre+ ' añadidas al carrito');
            });
        });
    });
</script>

</body>
</html>