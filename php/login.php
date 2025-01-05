<?php
session_start();
require_once 'conexion_bbdd.php'; 

// Si el usuario accede a login.php estando ya logueado
if (isset($_SESSION['id'])) {
    // Redirigir al usuario a la página de panel de usuario donde se muestran sus datos y los botones de las acciones que pueden realizar según el tipo de usuario que sean
        header("Location:panel_usuario.php");
    
    exit;
}

/*Verifica si la URL incluye un parámetro llamado registro con el valor exitoso, lo cual implicaría que venimos del fichero registro.php, de que el usuario complete el registro correctamente, para asi mostrarle un mensaje personalizado*/
if (isset($_GET['registro']) && $_GET['registro'] == 'exitoso') {
    $mensaje = "Registro completado con éxito. Ahora puedes iniciar sesión.";
}

/* Procesar el formulario de inicio de sesión. Verificar si el formulario ha sido enviado, esto es, si esta carga del archivo, viene de un envio de datos del formulario tipo POST. Con este if nos aseguramos que lo que está dentro del if no se ejecute cada vez que se cargue esta página, si no solo cuando venimos de que se envie el formulario mediante un POST*/
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //Recogemos los valores de email y password que el usuario introdujo en el formulario de login
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    /*Si recibimos un email y un password en el formulario, realizamos consulta a la BBDD para obtener todos los datos del usuario registrado con ese email*/
    if (!empty($email) && !empty($password)) {
        $query = "SELECT id, nombre, apellidos, email, tipo_usuario, password FROM usuarios WHERE email = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        //Si la consulta devuelve como mínimo un resultado (existe un usuario em la BBDD registrado con ese email)
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Función de PHP que compara una contraseña sin encriptar (en texto plano): $password, con la contraseña encriptada almacenada en la base de datos: $user['password']. Si coinciden:
            if (password_verify($password, $user['password'])) {

                // Iniciar la sesión. Almacenamos los datos del usuario en el array de la sesión
                $_SESSION['id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['apellidos'] = $user['apellidos'];
                $_SESSION['tipo_usuario'] = $user['tipo_usuario'];

                /*Redirigir y almacenar mensaje de bienvenida en el array de la sesión según el tipo de usuario para que una vez se cargan los ficheros de la redirección tras el login, mostrar esos mensajes de bienvenida*/
                if ($user['tipo_usuario'] === 'cliente') {

                    $_SESSION['login_message'] = "¡Te has logueado correctamente, " . $user['nombre'] . "! Ya puedes empezar a comprar";
                    header("Location: cliente/tienda.php");

                } else if ($user['tipo_usuario'] === 'administrador') {

                    $_SESSION['login_message'] = "¡Te has logueado correctamente, " . $user['nombre'] . "! Ya puedes empezar a administrar";
                    header("Location: panel_usuario.php");
                }
                exit;
            } else {
                $error = "Contraseña incorrecta";
            }
        } else {
            $error = "No se encontró una cuenta con ese correo electrónico";
        }
    } else {
        $error = "Por favor, completa todos los campos";
    }
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

    <div class="container">
        <div class="login-container">

            <h2 class="text-center">Iniciar Sesión</h2>
            
            <!-- Si el usuario viene de registrarse correctamente en registro.php, se muestra mensaje de exito personalizado antes del formulario de login-->
            <?php if (isset($mensaje)){?>
                <div class="alert alert-success text-center" role="alert">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php } ?>

            <!-- Si se produce algun error durante el logueo del usuario, se muestra el mensaje de error correspondiente antes del formulario -->
            <?php if (isset($error)) { ?>
                <div class="alert alert-danger text-center" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php } ?>

            <!-- Formulario de login -->
            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label for="email" class="form-label"><b>Correo electrónico</b></label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Introduce tu dirección de correo electrónico" pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" title="Introduce un correo electrónico válido" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label"><b>Contraseña</b></label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Introduce tu contraseña" required>
                </div>
                <button type="submit" class="btn btn-custom w-100">Iniciar Sesión</button>
            </form>
            
            <!-- Enlace para ir a registro.php debajo del formulario -->
            <p class="mt-3 text-center">¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
           
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

</body>
</html>