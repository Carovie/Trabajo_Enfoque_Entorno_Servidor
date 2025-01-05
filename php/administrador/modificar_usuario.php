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

<?php
// Mensaje de error para usuarios no autorizados (no logueados o de tipo cliente)
if ((!$usuario_logueado) || ($tipo_usuario === 'cliente')) {
    echo '<div class="alert alert-warning text-left" role="alert">Sólo los usuarios de tipo administrador pueden gestionar a los usuarios de la tienda</div>';
}

// Si el usuario es administrador
if (($usuario_logueado) && ($tipo_usuario === 'administrador')) {

    include('../conexion_bbdd.php');

    //Definimos e inicializamos variables para almacenar los valores actuales del usuario que vamos a modificar
    $usuario_id = null;
    $nombre_actual = '';
    $apellidos_actual = '';
    $email_actual = '';
    $password_actual = '';
    $tipo_usuario_actual = '';


    // Verificamos si los datos llegaron por envio POST y si el campo id del usuario que recibimos está definido, pero aun cumpliendose estas dos condiciones, tenemos dos posibles escenarios por discernir. Uno es que la carga de este fichero se produzca porque en la pagina de gestion_usuarios.php se pulsó el botón de Modificar uno de los usuarios existentes y el otro es que la carga de este fichero se produzca porque el usuario cubrió los campos del formulario de esta misma pagina para modificar alguna de las propiedades del usuario y pulsó el botón Modificar Usuario
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
        
    $usuario_id = $_POST['id']; //Almacenamos el id del usuario recibido

        /* Discernimos entre los dos escenarios comentados anteriormente. Esto es, si la carga de este fichero se produce porque se ha presionado el botón de Modificar Usuario del formulario de este mismo fichero (en el button incluimos un name="modificar" para ello), en cuyo caso sería la segunda carga del fichero. O si venimos desde gestion_usuarios.php porque el usuario pulsó el botón de Modificar de alguna de los usuarios, en cuyo caso sería la primera carga de este fichero.
        En el caso de ser la primera opción, entramos por este if*/
        if (isset($_POST['modificar'])) {

            // Obtenemos los nuevos datos para el usuario a modificar que el usuario administrador introdujo en los campos del formulario de esta misma página
            $nombre = $_POST['nombre'];
            $apellidos = $_POST['apellidos'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $tipo_usuario = $_POST['tipo_usuario'];

            // Si el input donde se introduce la contraseña está vacio, significa que no se desea modificarla, asi que  mantenemos la actual
            if (empty($password)) {

                //Realizamos la actualización de las propiedades del usuario requerido (aquel cuyo id coincida con el id recibido desde el input oculto del formulario de esta página) en la BBDD sin modificar la contraseña
                $sql_update = "UPDATE usuarios SET nombre = ?, apellidos = ?, email = ?, tipo_usuario = ? WHERE id = ?";
                $stmt = $con->prepare($sql_update);
                $stmt->bind_param("ssssi", $nombre, $apellidos, $email, $tipo_usuario, $usuario_id);

            //Si el usuario introdujo una nueva contraseña en el campo correspondiente del formulario, significa que quiere modificarla, asi que realizamos una actualización de las propiedades del usuario requerido (aquel cuyo id coincida con el id recibido desde el input oculto del formulario de esta página) en la BBDD, teniendo en cuenta que tambien quiere modificar la contraseña    
            } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql_update = "UPDATE usuarios SET nombre = ?, apellidos = ?, email = ?, password = ?, tipo_usuario = ? WHERE id = ?";
            $stmt = $con->prepare($sql_update);
            $stmt->bind_param("sssssi", $nombre, $apellidos, $email, $hashed_password, $tipo_usuario, $usuario_id);

            }
  
            //Se le muestra un alert con un mensaje indicando que todo fue correctamente
            if ($stmt->execute()) {
                echo "<script>
                  alert('Usuario modificado correctamente');
                  window.location.href = 'gestion_usuarios.php';
                  </script>";
            } else {
                echo "<script>
                  alert('Error al modificar el usuario');
                  window.location.href = 'gestion_usuarios.php';
                  </script>";
          }

        // En el segundo de los posibles escenarios, esto es, si llegamos a este fichero desde gestion_usuarios.php porque el usuario pulsó el botón de Modificar de alguno de los usuarios, sería la primera carga de este fichero y entramos por este else
        } else {

            //Recuperamos de la BBDD todas las propiedades del usuario cuyo id se corresponda con el id que hemos recibido desde el input oculto del formulario del botón Modificar de gestion_usuarios.php
            $sql = "SELECT * FROM usuarios WHERE id = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $resultado = $stmt->get_result();
    
            //Comprobamos que existe en la BBDD un registro/usuario con ese mismo id y cargamos en las variables anteriormente definidas los valores de cada una de estas propiedades del usuario, las actuales almacenadas en la BBDD, con el objetivo de mostrarlas por defecto en los campos del formulario para que desde ahi el usuario decida cual quiere modificar
            if ($resultado->num_rows > 0) {
                $usuario = $resultado->fetch_assoc();
                $nombre_actual = $usuario['nombre'];
                $apellidos_actual = $usuario['apellidos'];
                $email_actual = $usuario['email'];
                $password_actual = $usuario['password'];
                $tipo_usuario_actual = $usuario['tipo_usuario'];
            } else {
                echo "<script>
                    alert('Usuario no encontrado');
                    window.location.href = 'gestion_usuarios.php';
                    </script>";
            }
        }
    } else {
        echo "<script>
            alert('Datos no recibidos correctamente');
            window.location.href = 'gestion_usuarios.php';
            </script>";
        exit;
    }

?>

<div class="container">
    <div class="login-container">
        <h2 class="text-center" style="margin-top: -15px;">Modificar Usuario</h2>

        <!--Formulario en el que en sus campos mostramos por defecto los valores actuales de las propiedades del usuario (salvo el password que lo dejamos en blanco), para que viendolos, el administrador decida modificar los que crea conveniente-->
        <form method="POST" action="modificar_usuario.php">
            <!-- Este campo oculto envía el id del usuario a través del formulario junto con el resto de valores del usuario de los campos del formulario cuando se envía con el método POST (al pulsar el botón Modificar Usuario). Al button le incluimos un atributo name="modificar", para que desde el codigo PHP de este fichero, poder comprobar si la carga de este fichero corresponde a que viene desde gestion_usuarios porque el usuario pulsó el botón Modificar de un usuario o es porque el usuario pulsó el botón Modificar Usuario de este mismo formulario-->
            <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">

            <div class="mb-3">
                <label for="tipo_usuario" class="form-label"><b>Tipo de Usuario</b></label>
                <select class="form-control form-select" id="tipo_usuario" name="tipo_usuario" required>
                    <!--Comprobación para que por defecto aparezca seleccionado en el select, el tipo de usuario que es-->
                    <option value="cliente" 
                    <?php if ($tipo_usuario_actual === 'cliente') { echo 'selected'; } ?>>Cliente</option>
                    <option value="administrador" 
                    <?php if ($tipo_usuario_actual === 'administrador') { echo 'selected'; } ?>>Administrador</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="nombre" class="form-label"><b>Nombre</b></label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $nombre_actual; ?>" required pattern="[A-Za-zÀ-ÿ\s]+" title="El nombre solo puede contener letras y espacios">
            </div>

            <div class="mb-3">
                <label for="apellidos" class="form-label"><b>Apellidos</b></label>
                <input type="text" class="form-control" id="apellidos" name="apellidos" value="<?php echo $apellidos_actual; ?>" required pattern="[A-Za-zÀ-ÿ\s]+" title="Los apellidos solo pueden contener letras y espacios">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label"><b>email</b></label>
                <input type="text" class="form-control" id="email" name="email" value="<?php echo $email_actual; ?>" required pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" title="Introduce un correo electrónico válido">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label"><b>Password</b></label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Nueva contraseña o dejar en blanco para mantener">
            </div>

            <div class="d-flex justify-content-center align-items-center gap-3">
                <a href="gestion_usuarios.php" class="btn btn-custom">Volver</a>
                <button type="submit" class="btn btn-custom" name="modificar">Modificar Usuario</button>
            </div>
        </form>
    </div>
</div>

<?php
$con->close();
    }
    
?>

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

</body>
</html>
