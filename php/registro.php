<?php
// Incluir archivo de conexión a la base de datos
include 'conexion_bbdd.php'; 

// Inicializar variable para mensaje de error si el usuario ya existe
$error_message = "";

// Verificar si el formulario ha sido enviado, esto es, si esta carga del archivo, viene de un envio de datos del formulario tipo POST. Con este if nos aseguramos que lo que está dentro del if no se ejecute cada vez que se cargue esta página, si no solo cuando venimos de que se envie el formulario mediante un POST. 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger los valores del formulario
    $tipo_usuario = $_POST['tipo_usuario'];
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $email = $_POST['email'];
    $password = $_POST['password'];

   //Comprobar si ya existe un usuario con el mismo email
    $check_sql = "SELECT id, tipo_usuario FROM usuarios WHERE email = ?";
    
    if ($stmt_check = $con->prepare($check_sql)) {

        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        // Si la consulta devuelve 1 o más resultados es que un usuario con ese email ya existe registrado en la BBDD
        if ($stmt_check->num_rows > 0) {

        //El método bind_result permite asociar los resultados de las columnas de la consulta con variables PHP. Así, después de ejecutar fetch(), las variables contendrán los valores de cada columna en la fila actual. 
        $stmt_check->bind_result($id, $tipo_usuario_encontrado); 

        $stmt_check->fetch(); 

        $error_message = "Un usuario con esta dirección de correo electrónico ya está registrado en la tienda como $tipo_usuario_encontrado";
        $stmt_check->close();
        } else {
            $stmt_check->close();

    // Hashear la contraseña para mayor seguridad. Convierte la contraseña en un valor "hash" (un código único y seguro) utilizando un algoritmo de hashing y asi se almacena el password de forma segura en la BBDD.
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
  
    //Consulta para insertar el nuevo usuario en la BBDD
    $sql = "INSERT INTO usuarios (tipo_usuario, nombre, apellidos, email, password) 
            VALUES (?, ?, ?, ?, ?)";

    if ($stmt = $con->prepare($sql)) {
        $stmt->bind_param("sssss", $tipo_usuario, $nombre, $apellidos, $email, $hashed_password);

        if ($stmt->execute()) {
            // Si la inserción fue exitosa, redirigir al usuario a la página de login. El parámetro en la URL (?registro=exitoso) será leído por la página de login.php para que así sepa que tiene que mostrar el mensaje de que el registro ha sido correcto y que el usuario puede loguearse
            header("Location: login.php?registro=exitoso");
            exit();
        } else {
            $error_message ="Error al registrar el usuario: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        $error_message ="Error al preparar la consulta: " . $con->error;
    }
        }
    } else {
            $error_message = "Error al preparar la consulta de comprobación de si el usuario existe: " . $con->error;
            }

$con->close();
}
?>

<!DOCTYPE html>
<html lang>

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
        <h2 class="text-center">Registro de Usuario</h2>

        <!-- Mostrar mensajes de error aquí -->
        <?php if (!empty($error_message)) {
        echo "<div class='alert alert-danger mt-3' role='alert'>$error_message</div>";
        } ?>

        <!--Formulario de registro. Metodo de envio POST y se redirige a este mismo archivo php donde vamos a recibir los datos del usuario. Las validaciones de los campos se hacen sobre los mismos en HTML utilizando pattern-->
        <form method="POST" action="registro.php">
            <div class="mb-3">
                <label for="tipo_usuario" class="form-label"><b>Tipo de Usuario</b></label>
                <select class="form-control form-select" id="tipo_usuario" name="tipo_usuario" required>
                    <option value="cliente">Cliente</option>
                    <option value="administrador">Administrador</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="nombre" class="form-label"><b>Nombre</b></label>
                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Introduce tu nombre" required pattern="[A-Za-zÀ-ÿ\s]+" title="El nombre solo puede contener letras y espacios">
            </div>

            <div class="mb-3">
                <label for="apellidos" class="form-label"><b>Apellidos</b></label>
                <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="Introduce tus apellidos" required pattern="[A-Za-zÀ-ÿ\s]+" title="Los apellidos solo pueden contener letras y espacios">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label"><b>Correo Electrónico</b></label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Introduce tu correo electrónico" required pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" title="Introduce un correo electrónico válido">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label"><b>Contraseña</b></label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Introduce tu contraseña" required>
            </div>

            <button type="submit" class="btn btn-custom w-100">Registrar</button>
        </form>

        <!-- Enlace para ir a login.php a iniciar sesión -->
        <p class="mt-3 text-center">¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
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