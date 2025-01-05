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
echo '<div class="alert alert-warning text-left" role="alert">Sólo los usuarios de tipo administrador pueden gestionar las zapatillas</div>';
    };

    //Si el usuario está logueado y es de tipo administrador, establecemos conexión con la BBDD y recuperamos los valores de la zapatilla a crear introducidos por el usuario administrador y pasados desde el formulario que se implementa en este mismo fichero mas abajo 
    if (($usuario_logueado)&&($tipo_usuario === 'administrador')) {

        include('../conexion_bbdd.php');

        // Comprobar si se llega a este fichero con el envio de alguna acción a través de POST (implicaría que es el envío del formulario de más abajo con los valores para la zapatilla a crear). Cuando se envían archivos con un formulario (enctype="multipart/form-data"), los datos de los archivos no están disponibles en $_POST, sino en $_FILES. Por lo tanto, el campo imagen debe manejarse con $_FILES, y aparte hay que hacer más cosas para poder gestionar las imagenes que carga el usuario
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Si en todos los campos del formulario se recogieron valores, recogemos los datos del formulario
            if (isset($_POST['nombre']) && isset($_POST['descripcion']) && isset($_POST['precio']) && isset ($_FILES['imagen'])) {
                $nombre = $_POST['nombre'];
                $descripcion = $_POST['descripcion'];
                $precio = $_POST['precio'];

                $imagen = $_FILES['imagen'];

                 // Definir el directorio de destino para las imágenes
                $directorioDestino = '../../img/';
                $nombreImagen = basename($imagen['name']);  // Obtener el nombre del archivo
                $rutaDestino = $directorioDestino . $nombreImagen;

                // Verificar si el archivo es una imagen válida
                $imagenTipo = mime_content_type($imagen['tmp_name']);
                if (strpos($imagenTipo, 'image') !== false) {
                    // Mover la imagen al directorio de destino
                    if (move_uploaded_file($imagen['tmp_name'], $rutaDestino)) {

                        //Añado manualmente la cadena /img/ antes del nombre para que en el nombre siga el mismo formato que las imagenes de zapatillas que tenemos almacenadas en la BBDD por defecto y no haya problemas al recuperarlas
                        $nombreImagen = '/img'.'/'.$nombreImagen;

                        //Insertanmos la nueva zapatilla en la BBDD, mostramos mensaje de confirmación y redirigimos a gestion_zapatillas.php
                        $sql = "INSERT INTO zapatillas (nombre, descripcion, precio, imagen) VALUES (?, ?, ?, ?)";

                        $stmt = $con->prepare($sql);
                        $stmt->bind_param("ssds", $nombre, $descripcion, $precio, $nombreImagen);

                        if ($stmt->execute()) {
                            echo "
                            <script>
                            alert('Zapatilla creada con éxito');
                            window.location.href = 'gestion_zapatillas.php';
                            </script>";
                        } else {
                            echo "
                            <script>
                            alert('Error al crear la zapatilla');
                            window.location.href = 'gestion_zapatillas.php';
                            </script>";
                        }
                    } else {
                        echo "
                        <script>
                        alert('Error al mover la imagen');
                        window.location.href = 'gestion_zapatillas.php';
                        </script>";
                    }
                } else {
                    echo "
                    <script>
                    alert('El archivo no es una imagen valida');
                    window.location.href = 'gestion_zapatillas.php';
                    </script>";
                }
            } else {
                echo "
                <script>
                alert('Faltan datos para crear la zapatilla');
                window.location.href = 'gestion_zapatillas.php';
                </script>";
            }
    
        }
        $con->close();
?>

<!--Formulario para creación de nueva zapatilla, que redirige/envia los valores a este mismo fichero-->
<div class="container">
    <div class="login-container">
    <h2 class="text-center" style="margin-top: -15px;">Crear Nueva Zapatilla</h2>

<!--El atributo enctype="multipart/form-data" se utiliza en formularios HTML cuando necesitas enviar datos que incluyen archivos (por ejemplo, imágenes, documentos...) al servidor. Cuando un formulario contiene un campo de tipo file (como <input type="file">), se necesita usar enctype="multipart/form-data" para asegurar que el archivo se envíe correctamente-->
        <form method="POST" action="crear_zapatilla.php" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nombre" class="form-label"><b>Marca y Modelo</b></label>
                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Introduce marca y modelo de la zapatilla" required>
            </div>

            <div class="mb-3">
                <label for="descripcion" class="form-label"><b>Descripción</b></label>
                <textarea class="form-control" id="descripcion" name="descripcion" placeholder="Introduce una descripción de la zapatilla" required rows="2"></textarea>
            </div>

            <div class="mb-3">
                <label for="precio" class="form-label"><b>Precio (€)</b></label>
                <input type="number" class="form-control" id="precio" name="precio" placeholder="Introduce el precio (usa punto (.) para decimales)" required step="0.01" min="0" title="Por favor, utiliza un punto (.) como separador de decimales.">
            </div>

            <div class="mb-3">
                <label for="imagen" class="form-label"><b>Imagen</b></label>
                <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*" required>
            </div>

            <div class="d-flex justify-content-center align-items-center gap-3">
            <a href="gestion_zapatillas.php" class="btn btn-custom">Volver</a>
            <button type="submit" name="crear" class="btn btn-custom">Crear Zapatilla</button>
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