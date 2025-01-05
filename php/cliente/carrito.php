<?php

// Iniciar la sesión para acceder a las variables del array de sesión
session_start();

// Verificamos si el usuario llega a la tienda no estando logueado o estando logueado como administrador para en estos dos casos, deshabilitar el botón de Añadir Carrito y que solo pueda hacer un pedido si el usuario está logueado y es de tipo cliente
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
        echo '<div class="alert alert-warning text-left" role="alert">Por favor, inicia sesión (o regístrate) para poder añadir zapatillas al carrito.</div>';
        //Si el usuario está logueado y es de tipo administrador le mostramos el mensaje personalizado
        } else if ($tipo_usuario === 'administrador') {
        echo '<div class="alert alert-info text-left" role="alert">Eres un usuario de tipo administrador. No puedes añadir zapatillas al carrito.</div>';
        }
        ?>
        </div>
    </div>

</div>

<?php  //Si el usuario está logueado y es de tipo cliente 
        if (($usuario_logueado)&&($tipo_usuario === 'cliente')) {?>
<div class="container my-3">

<h2 class="text-center mt-3" style="margin-top: -20px !important;">Carrito de Compras</h2>

<!--Este contenedor vacío lo vamos a utilizar para cargar aquí el contenido de la variable en la que almacenamos la tabla con todas las zapatillas del carrito, precio total y los botones-->
<div id="contenido-tabla"></div>

</div>
<?php  }?>

<!-- Formulario oculto que utilizamos para poder enviar a confirmar_compra.php el contenido del carrito y el precio total y asi poder almacenar los datos del pedido en la BBDD cuando el usuario pulse el botón de Confirmar Pedido -->
<form id="formConfirmarCompra" action="confirmar_compra.php" method="POST" style="display:none;">
    <input type="hidden" name="carrito" id="carritoData">
    <input type="hidden" name="total" id="totalData">
</form>

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
    //Manejador de eventos en JavaScript que asegura que el código dentro de la función se ejecutará únicamente cuando el documento HTML esté completamente cargado
    document.addEventListener("DOMContentLoaded", function() {

    //Definimos una variable en JS en la que vamos a asignar true o false en función del valor de la variable $tipo_usuario de PHP, será true si el usuario está logueado y es de tipo cliente 
    let tipo_usuarioJS_cliente;

    <?php 
    if ($tipo_usuario === 'cliente') {
       echo "tipo_usuarioJS_cliente = true;";
    }
    ?>

    //Si el usuario está logueado y es de tipo cliente
    if (tipo_usuarioJS_cliente){
    // Recuperar el carrito almacenado en localStorage. Si no hay nada almacenado en el localStorage bajo la clave 'carrito', se asigna un array vacío como valor predeterminado mediante el operador || [].
    let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
       
    // Si hay artículos en el carrito, creamos una tabla, recorremos las zapatillas del carrito y vamos mostrando (almacenando en una cadena/tabla que luego mostraremos) sus caracteristicas en las filas de la tabla junto con un botón Eliminar al final de cada fila de cada zapatilla. Al final de la tabla mostramos también el total del precio y los botones de Vaciar Carrito y Confirmar Cpmpra
    if (carrito.length > 0) {        
        let contenidoTabla = `
            <table class="table table-striped table-hover table-bordered rounded text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Imagen</th>
                        <th>Marca y Modelo</th>
                        <th>Descripción</th>
                        <th>Precio</th>
                        <th>Unidades</th>
                        <th style="border-style: hidden; background-color: transparent;"></th>
                    </tr>
                </thead>
                <tbody>
        `;
        //Variable utilizada para calcular el precio total de los productos del carrito
        let total = 0;

        carrito.forEach(function(zapatilla, index) {
            total += parseFloat(zapatilla.precio) * zapatilla.unidades;
            contenidoTabla += `
                <tr>
                    <td class="align-middle"><img src="../../${zapatilla.imagen}" alt="${zapatilla.nombre}" class="img-thumbnail" style="width: 100px; height: auto;"></td>
                    <td class="align-middle">${zapatilla.nombre}</td>
                    <td class="align-middle">${zapatilla.descripcion}</td>
                    <td class="align-middle">${parseFloat(zapatilla.precio).toFixed(2)}€</td>
                    <td class="align-middle">${zapatilla.unidades}</td>
                    <td class="align-middle">
                        <button class="btn btn-danger eliminar" data-index="${index}">Eliminar</button>
                    </td>
                </tr>
            `;
        });//data-index: es un atributo de datos personalizado en HTML. Los atributos que comienzan con data- son utilizados para almacenar información adicional en los elementos HTML, que no afecta la visualización de la página ni es procesada directamente por el navegador. Lo necesitamos más abajo para la funcionalidad del botón Eliminar

        contenidoTabla += `
            <tr>
                <td></td>
                <td></td>
                <td class="text-end"><strong>TOTAL:</strong></td>
                <td class="align-middle"><strong>${total.toFixed(2)}€</strong></td>
                
            </tr>
        `;
        contenidoTabla += `
                </tbody>
            </table>
            <tr>
                <td colspan="6" class="text-center">
                    <div class="d-flex justify-content-center gap-3">
                        <button id="vaciarCarrito" class="btn btn-warning">Vaciar Carrito</button>
                        <button id="confirmarCompra" class="btn btn-success">Confirmar Compra</button>
                    </div>
                </td>
            </tr>
        `;

        //Renderiza en el <div id="contenido-tabla"> del main, la tabla con todo el contenido que hemos generado y almacenado en la variable de tipo cadena contenidoTabla
        document.getElementById("contenido-tabla").innerHTML = contenidoTabla;

        // Selecciona todos los elementos con la clase eliminar de la página, esto es, todos los botones eliminar de cada una de las zapatillas. Iteramos sobre ellos y estamos a la escucha y cuando se pulsa alguno de estos botones eliminar, se les da la funcionalidad de que se elimine esa zapatilla del carrito y se actualice el carrito en el localStorage
        document.querySelectorAll('.eliminar').forEach(function(btnEliminar) {
            btnEliminar.addEventListener('click', function() {
                let index = btnEliminar.getAttribute('data-index');
                carrito.splice(index, 1);
                localStorage.setItem('carrito', JSON.stringify(carrito));                
                location.reload();
                
            });
        });

          // Funcionalidad al hacer click en el botón Vaciar carrito. Se elimina el carrito al completo y se actualiza en el localStorage
          document.getElementById('vaciarCarrito').addEventListener('click', function() {
            
            localStorage.removeItem('carrito');
            location.reload();
        });

        // Funcionalidad al hacer click en el botón Confirmar Compra
        document.getElementById('confirmarCompra').addEventListener('click', function() {

        // Al hacer click en Confirmar Compra, agregar los datos del carrito al formulario oculto que citamos arriba y enviar el formulario. Usamos JSON.stringify(carrito) porque como carrito es un array (tambien se utiliza con objetos), esta función convierte ese objeto o array en una cadena de texto en formato JSON, lo que permite mantener la estructura del dato original cuando lo enviamos en un formulario
        document.getElementById('carritoData').value = JSON.stringify(carrito);
        document.getElementById('totalData').value = total.toFixed(2);
        document.getElementById('formConfirmarCompra').submit();

        });    

        //Si el carrito no tiene zapatillas, mostramos mensaje personalizado
    } else {
        document.getElementById("contenido-tabla").innerHTML = `<h2 class="text-center mb-3" style="margin-top: -20px;">No hay productos en el carrito</h2>`;
        
        // Y Ocultamos el título "Carrito de Compras"
        document.querySelector("h2").style.display = 'none';
        }
    }
});
</script>

</body>
</html>