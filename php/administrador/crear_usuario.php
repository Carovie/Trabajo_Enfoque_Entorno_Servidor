<?php

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
echo '<div class="alert alert-warning text-left" role="alert">Sólo los usuarios de tipo administrador pueden gestionar a los usuarios de la tienda</div>';
    };

    //Si el usuario está logueado y es de tipo administrador, establecemos conexión con la BBDD y recuperamos los valores de los datos del usuario a crear introducidos por el usuario administrador y pasados desde el formulario que se implementa en este mismo fichero mas abajo 
    if (($usuario_logueado)&&($tipo_usuario === 'administrador')) {

    include('../conexion_bbdd.php');

        // Comprobar si se llega a este fichero con el envio de alguna acción a través de POST (implicaría que es el envío del formulario de más abajo con los valores para el usuario a crear). 
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            // Si en todos los campos del formulario se recogieron valores, recogemos los valores de los datos del usuario a crear introducidos en el formulario
            if (isset($_POST['tipo_usuario']) && isset($_POST['nombre']) && isset($_POST['apellidos']) && isset ($_POST['email'])  && isset ($_POST['password'])) {

                $tipo_usuario = $_POST['tipo_usuario'];
                $nombre = $_POST['nombre'];
                $apellidos = $_POST['apellidos'];
                $email = $_POST['email'];
                $password = $_POST['password'];

                //Comprobar si ya existe registrado en la BBDD un usuario con el mismo email
                $check_sql = "SELECT id, tipo_usuario FROM usuarios WHERE email = ?";
    
                if ($stmt_check = $con->prepare($check_sql)) {

                    $stmt_check->bind_param("s", $email);
                    $stmt_check->execute();
                    $stmt_check->store_result();

                    // Si el usuario ya existe registrado en la BBDD
                    if ($stmt_check->num_rows > 0) {

                        $stmt_check->bind_result($id, $tipo_usuario_encontrado); 
                        $stmt_check->fetch(); 
                        $stmt_check->close();

                        echo "
                        <script>
                        alert('Un usuario con esta dirección de correo electrónico ya está registrado en la tienda');
                        window.location.href = 'gestion_usuarios.php';
                        </script>";
                        
                    // Si el usuario no existe registrado en la BBDD
                    } else {

                        $stmt_check->close();

                        // Hashear la contraseña para mayor seguridad. Convierte la contraseña en un valor "hash" (un código único y seguro) utilizando un algoritmo de hashing y asi se almacena el password de forma segura en la BBDD.
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                        $sql = "INSERT INTO usuarios (tipo_usuario, nombre, apellidos, email, password) 
                        VALUES (?, ?, ?, ?, ?)";

                        if ($stmt = $con->prepare($sql)) {
                            $stmt->bind_param("sssss", $tipo_usuario, $nombre, $apellidos, $email, $hashed_password);
    
                            //Insertanmos el nuevo usuario en la BBDD, mostramos mensaje de confirmación y redirigimos a gestion_usuarios.php
                            if ($stmt->execute()) {
                        
                                echo "
                                <script>
                                alert('Usuario creado con éxito');
                                window.location.href = 'gestion_usuarios.php';
                                </script>";
                            } else {

                                echo "
                                <script>
                                alert('Error al registrar el usuario');
                                window.location.href = 'gestion_usuarios.php';
                                </script>";
                            }
    
                        } else {
    
                            echo "
                            <script>
                            alert('Error al preparar la consulta');
                            window.location.href = 'gestion_usuarios.php';
                            </script>";
                            }
                            }   
                } else {
                    $error_message = "Error al preparar la consulta de comprobación de si el usuario existe";
                        }

            }
        }

        $con->close();
        
?>

<!--Formulario para creación de nuevo usuario, que redirige/envia los valores a este mismo fichero-->
<div class="container">
    <div class="login-container">
    <h2 class="text-center" style="margin-top: -15px;">Crear Nuevo Usuario</h2>

    <form method="POST" action="crear_usuario.php">
            <div class="mb-3">
                <label for="tipo_usuario" class="form-label"><b>Tipo de Usuario</b></label>
                <select class="form-control form-select" id="tipo_usuario" name="tipo_usuario" required>
                    <option value="cliente">Cliente</option>
                    <option value="administrador">Administrador</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="nombre" class="form-label"><b>Nombre</b></label>
                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Introduce nombre del usuario" required pattern="[A-Za-zÀ-ÿ\s]+" title="El nombre solo puede contener letras y espacios">
            </div>

            <div class="mb-3">
                <label for="apellidos" class="form-label"><b>Apellidos</b></label>
                <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="Introduce apellidos del usuario" required pattern="[A-Za-zÀ-ÿ\s]+" title="Los apellidos solo pueden contener letras y espacios">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label"><b>Correo Electrónico</b></label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Introduce  correo electrónico del usuario" required pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" title="Introduce un correo electrónico válido">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label"><b>Contraseña</b></label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Introduce contraseña del usuario" required>
            </div>

            <div class="d-flex justify-content-center align-items-center gap-3">
            <a href="gestion_usuarios.php" class="btn btn-custom">Volver</a>
            <button type="submit" name="crear" class="btn btn-custom">Crear Usuario</button>
            </div>
        </form>
        
    </div>
</div>

<?php
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

<script>

</script>

</body>
</html>