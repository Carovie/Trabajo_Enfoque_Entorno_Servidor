<?php

// Iniciar la sesión para acceder a las variables del array de sesión
session_start();

// Vamos a verificar si el usuario llega a esta pantalla no estando logueado o estando logueado como cliente para en estos dos casos, mostrarles mensajes personalizados
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
                    <a class="navbar-brand my-2 my-lg-0" href="../cliente/tienda.php"><i class="fas fa-shop"></i> Tienda</a>
                    <a class="navbar-brand my-2 my-lg-0" href="../login.php"><i class="fas fa-user"></i> Mi Cuenta</a>
                </div>
            </div>
        </div>
    </nav>
</header>

<main>

<div class="container my-3">

    <?php  //Si el usuario no está logueado o está logueado y es de tipo cliente, le mostramos el mensaje
    if ((!$usuario_logueado) || ($tipo_usuario === 'cliente')) {
        echo '<div class="alert alert-warning text-left" role="alert">Sólo los usuarios de tipo administrador pueden acceder al panel de administración de la tienda</div>';
    };?>      

    <?php  //Si el usuario está logueado y es de tipo administrador, establecemos conexión con la BBDD y recuperamos y mostramos todos los usuarios existentes en la aplicación, con sus datos
    if (($usuario_logueado)&&($tipo_usuario === 'administrador')) {
            
        include('../conexion_bbdd.php');

        //Recuperamos todas los usuarios existentes en la BBDD/registrados en la aplicación
        $sql = "SELECT id, nombre, apellidos, email, password, tipo_usuario FROM usuarios";
        $result = $con->query($sql);
        $usuarios = [];


        //Si existe al menos un usuario registrado en la aplicación, los recuperamos y los pasamos a un array
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
            $usuarios[] = $row;
            }
        }
        $con->close();
    
        //Si el array de usuarios que acabamos de crear tiene al menos un usuario, mostramos en una fila de una tabla cada uno de los usuarios con sus propiedades y junto a ellas, un botón para Modificar y otro para Eliminar el usuario
        if (!empty($usuarios)) { ?>
            <h2 class="text-center mb-3">Gestión de Usuarios</h2>

            <table class="table table-striped table-hover table-bordered text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>email</th>
                        <th>Tipo de Usuario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario) { ?>
                    <tr>
                        <!--htmlspecialchars() es una función de PHP que convierte caracteres especiales en su equivalente en entidades HTML. Su propósito principal es prevenir ataques de tipo Cross-Site Scripting (XSS) y garantizar que el contenido generado no sea interpretado de manera errónea por el navegador, como HTML o JavaScript. Esta función convierte ciertos caracteres especiales (como <, >, ", etc.) en secuencias de texto que el navegador mostrará literalmente, en lugar de interpretarlos como elementos HTML. -->
                        <td class="align-middle"><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                        <td class="align-middle"><?php echo htmlspecialchars($usuario['apellidos']); ?></td>
                        <td class="align-middle"><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td class="align-middle"><?php echo htmlspecialchars($usuario['tipo_usuario']); ?></td>
                        <td class="align-middle">
                            <!--Metemos los botones de Eliminar y Modificar, cada uno en un formulario, el botón de Crear hace no falta porque no es necesario pasarle el id desde el formulario al fichero que va a implementar la funcionalidad correspondiente, en cambio en el caso de los botones de Modificar y Eliminar, sí que es necesario. -->
                            <form method="POST" action="modificar_usuario.php" class="d-inline">
                                <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                                <button type="submit" class="btn btn-primary">Modificar</button>
                            </form>
                            <form method="POST" action="eliminar_usuario.php" class="d-inline">
                                <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                                <button type="submit" class="btn btn-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    <?php } ?>
                    <tr><!--En la fila inferior de la tabla establecemos los botones Volver (a panel_usuario.php) y Crear Usuario que redirige a crear_usuario.php-->
                        <td style="background-color:transparent;"></td>
                        <td class="align-middle text-center" style="background-color:transparent;">
                            <a href="../panel_usuario.php" class="btn btn-success">Volver</a>
                        </td>
                        <td colspan="2" style="background-color:transparent;"></td>
                        <td class="align-middle text-center" style="background-color:transparent;">
                            <a href="crear_usuario.php" class="btn btn-success">Crear Usuario</a>
                        </td>
                    </tr>
                </tbody>
            </table>
            <!--Si no existen usuarios recuperados de la BBDD-->
            <?php  } else { ?>
                <div class="text-center" style="display: flex; justify-content: center; align-items: center; height: 200px; width: 100%;">
                <h2 class="mb-3" style="white-space: nowrap;">No hay usuarios disponibles para gestionar</h2>
                </div>
            <?php  } 
    }?>        

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
            <a href="https://www.interior.gob.es/opencms/es/politica-de-cookies/" id="politica">Política de cookies</a>
        </div>
    </div>
</footer>

<script>

</script>

</body>
</html>