<?php 
session_start();

// Verificar si la sesión está activa por si llegamos a esta pantalla de alguna manera (como por ejemplo pulsando atras una vez cerrada la sesión), para que no muestre errores en los campos y en lugar de eso nos lleve a la pantalla de login
if (!isset($_SESSION['id'])) {
    // Si no está logueado, redirigir al login
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!--Enlace para incluir las librerias de Bootstrap-->
    <link rel="stylesheet" href="../css/bootstrap/css/bootstrap.min.css">
    <!--Enlace para incluir mi hoja de estilos.css-->
    <link rel="stylesheet" href="../css/estilos.css">
    <!--Enlace para incluir los iconos de la barra de navegación-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!--Enlaces para poder incluir la funcionalidad JavaScript del navbar-toggler (menú de hamburguesa que aparece al reducir las pantallas)-->
    <script src="../css/bootstrap/js/popper.min.js"></script>
    <script src="../css/bootstrap/js/bootstrap.min.js"></script>

    <title>Pisada Firme</title>
</head>

<body>
    <header>

        <div class="logo_titulo">
            <img src="../img/logo.png" alt="Logo" class="logo">
            <h1>PISADA FIRME</h1>
            <img src="../img/logo.png" alt="Logo" class="logo">
        </div>

        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container-fluid">

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
                    aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">

                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarContent">

                    <div class="d-flex flex-column flex-lg-row ms-auto align-items-lg-center">
                        <a class="navbar-brand my-2 my-lg-0" href="../index.html"><i class="fas fa-home"></i> Inicio</a>
                        <a class="navbar-brand my-2 my-lg-0" href="cliente/tienda.php"><i class="fas fa-shop"></i> Tienda</a>
                        <a class="navbar-brand my-2 my-lg-0" href="cliente/carrito.php"><i class="fas fa-shopping-cart"></i> Carrito</a>
                    </div>
                </div>
            </div>
        </nav>

    </header>

    <main>

    <!-- Si existe un mensaje de bienvenida en el array de la sesión, lo mostramos porque será que el usuario tipo administrador se acaba de loguear, ya que se le redirigé a esta pantalla con ese mensaje almacenado en el array de sesiones. Y en cuanto se muestra el mensaje lo eliminamos para que en posteriores accesos a esta pantalla no se vuelva a mostrar el mensaje-->
    <?php if (isset($_SESSION['login_message'])) { ?>
    <div class="alert alert-success text-center mb-0" role="alert"> 
        <?php echo $_SESSION['login_message']; ?>
    </div>
        <?php unset($_SESSION['login_message']); } ?>

    <!-- Tambien redirigimos al usuario a esta pantalla cuando accede a la pantalla de login estando ya logueado. En ese caso le mostramos los datos del usuario , el botón de Ver Pedidos o Panel Administrador segun el tipo de usuario y el botón de Cerrar Sesión -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-4">
                    <h3 class="text-center mb-4">Datos del Usuario</h3>
                    <div class="mb-3">
                        <p><strong>Tipo de Usuario:</strong> <?php echo $_SESSION['tipo_usuario']; ?></p>
                        <p><strong>Nombre:</strong> <?php echo $_SESSION['nombre']; ?></p>
                        <p><strong>Apellidos:</strong> <?php echo $_SESSION['apellidos']; ?></p>
                        <p><strong>Email:</strong> <?php echo $_SESSION['email']; ?></p>
                    </div>
                    <div class="d-flex gap-3">
                        <!-- Si el usuario es de tipo cliente mostramos el botón Ver Pedidos -->
                        <?php if ($_SESSION['tipo_usuario'] === 'cliente') { ?>
                            <form method="POST" action="cliente/listado_pedidos.php" class="w-100">
                                <button type="submit" class="btn btn-custom w-100">Ver Pedidos</button>
                            </form>
                        <!-- Si el usuario es de tipo administrador mostramos los botones de Gestión de Usuarios y Zapatillas -->
                        <?php } else if ($_SESSION['tipo_usuario'] === 'administrador') { ?>
                            <form method="POST" action="administrador/gestion_usuarios.php" class="w-100">
                                <button type="submit" class="btn btn-custom w-100">Gestión Usuarios</button>
                            </form>
                            <form method="POST" action="administrador/gestion_zapatillas.php" class="w-100">
                                <button type="submit" class="btn btn-custom w-100">Gestión Zapatillas</button>
                            </form>
                        <?php } ?>
                        <!-- Les damos ids a form y a button para poder ejecutar codigo JavaSscript (implementado más abajo), evitando que se envie el formulario inmediatamente y que se pueda hacer localStorage.clear() antes, cuando pulse el botón de Cerrar Sesión -->
                        <form method="POST" action="logout.php" class="w-100" id="logoutForm">
                            <button type="submit" class="btn btn-custom w-100" id="logoutButton">Cerrar Sesión</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </main>

    <footer>
        <div class="pie">
            <div>
                <h4 id="tambien">También puedes visitarnos en:</h4>

                <a href=# target="_blank"> <img src="../img/youtube.png" alt="Youtube" width="100" height="30"></a>
                <a href=# target="_blank"> <img src="../img/instagram.png" alt="Instagram" width="100" height="30"></a>
                <a href=# target="_blank"> <img src="../img/telegram.png" alt="Telegram" width="100" height="30"></a>
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

        //Cuando el usuario pulsa el botón de Cerrar Sesión
        document.getElementById('logoutButton').addEventListener('click', function(event) {
            // Prevenir el envío del formulario inmediatamente
            event.preventDefault();

            // Borrar el LocalStorage
            localStorage.clear();

            // Ahora enviar el formulario
            document.getElementById('logoutForm').submit();
        });
    </script>

</body>
</html>