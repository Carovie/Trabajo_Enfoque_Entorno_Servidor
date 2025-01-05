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
    echo '<div class="alert alert-warning text-left" role="alert">Sólo los usuarios de tipo administrador pueden gestionar las zapatillas de la tienda</div>';
}

// Si el usuario es administrador
if (($usuario_logueado) && ($tipo_usuario === 'administrador')) {

    include('../conexion_bbdd.php');

    //Definimos e inicializamos variables para almacenar los valores actuales de la zapatilla que vamos a modificar
    $zapatilla_id = null;
    $nombre_actual = '';
    $descripcion_actual = '';
    $precio_actual = '';
    $imagen_actual = '';

    // Verificamos si los datos llegaron por envio POST y si el campo id de la zapatilla que recibimos está definido, pero aun cumpliendose estas dos condiciones, tenemos dos posibles escenarios por discernir. Uno es que la carga de este fichero se produzca porque en la pagina de gestion_zapatillas.php se pulsó el botón de Modificar una de las zapatillas existentes y el otro es que la carga de este fichero se produzca porque el usuario cubrió los campos del formulario de esta misma pagina para modificar alguna de las características de la zapatilla y pulsó el botón Modificar Zapatilla
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
        
    $zapatilla_id = $_POST['id']; //Almacenamos el id de la zapatilla recibida

        /* Discernimos entre los dos escenarios comentados anteriormente. Esto es, si la carga de este fichero se produce porque se ha presionado el botón de Modificar Zapatilla del formulario de este mismo fichero (en el button incluimos un name="modificar" para ello), en cuyo caso sería la segunda carga del fichero. O si venimos desde gestion_zapatillas.php porque el usuario pulsó el botón de Modificar de alguna de las zapatillas, en cuyo caso sería la primera carga de este fichero.
        En el caso de ser la primera opción, entramos por este if*/

        if (isset($_POST['modificar'])) {

            // Obtenemos los nuevos datos para la zapatilla a modificar que el usuario administrador introdujo en los campos del formulario de esta misma página
            $nombre = $_POST['nombre'];
            $descripcion = $_POST['descripcion'];
            $precio = $_POST['precio'];

            // Comprobamos si se ha subido una nueva imagen y no hubo errores en su carga. 
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
                // Si hay una nueva imagen, procesarla. Este código se utiliza para manejar un archivo que ha sido subido mediante un formulario con un campo file.               
                
                $imagen = $_FILES['imagen'];
                $imagen_nombre = $imagen['name'];
                $imagen_tmp = $imagen['tmp_name'];
                $imagen_destino = '/'.'img/' . $imagen_nombre;
                
                // Mover la imagen al directorio de destino
                move_uploaded_file($imagen_tmp, $imagen_destino);
            } else {
                // Si no se sube una nueva imagen, se mantiene la imagen actual. En este caso se utiliza $_POST en lugar de $_FILES para la imagen porque el campo imagen_actual del formulario no es un archivo nuevo que el usuario sube, sino un valor de texto que representa la ruta o el nombre de la imagen que ya existe en el servidor
                $imagen_destino = $_POST['imagen_actual'];
            }

            //Realizamos la actualización de las caracteristicas de la zapatilla requerida (aquella cuyo id coincida con el id recibido desde el input oculto del formulario de esta página) en la BBDD 
            $sql_update = "UPDATE zapatillas SET nombre = ?, descripcion = ?, precio = ?, imagen = ? WHERE id = ?";
            $stmt = $con->prepare($sql_update);
            $stmt->bind_param("ssdsi", $nombre, $descripcion, $precio, $imagen_destino, $zapatilla_id);
  
            //Se le muestra un alert con un mensaje indicando que todo fue correctamente
            if ($stmt->execute()) {
                echo "<script>
                  alert('Zapatilla modificada correctamente');
                  window.location.href = 'gestion_zapatillas.php';
                  </script>";
            } else {
                echo "<script>
                  alert('Error al modificar la zapatilla');
                  window.location.href = 'gestion_zapatillas.php';
                  </script>";
          }
        // En el segundo de los posibles escenarios, esto es, si llegamos a este fichero desde gestion_zapatillas.php porque el usuario pulsó el botón de Modificar de alguno de las zapatillas, sería la primera carga de este fichero y entramos por este else
        } else {

            //Recuperamos de la BBDD todas las caracteristicas de la zapatilla cuyo id se corresponda con el id que hemos recibido desde el input oculto del formulario del botón Modificar de gestion_zapatillas.php
            $sql = "SELECT * FROM zapatillas WHERE id = ?";
            $stmt = $con->prepare($sql);
            $stmt->bind_param("i", $zapatilla_id);
            $stmt->execute();
            $resultado = $stmt->get_result();

            //Comprobamos que existe en la BBDD un registro/zapatilla con ese mismo id y cargamos en las variables anteriormente definidas los valores de cada una de estas características de la zapatilla, las actuales almacenadas en la BBDD, con el objetivo de mostrarlas por defecto en los campos del formulario para que desde ahi el usuario decida cual quiere modificar
            if ($resultado->num_rows > 0) {
                $zapatilla = $resultado->fetch_assoc();
                $nombre_actual = $zapatilla['nombre'];
                $descripcion_actual = $zapatilla['descripcion'];
                $precio_actual = $zapatilla['precio'];
                $imagen_actual = $zapatilla['imagen'];
            } else {
                echo "<script>
                    alert('Zapatilla no encontrada');
                    window.location.href = 'gestion_zapatillas.php';
                    </script>";
            }
        }
    } else {
        echo "<script>
            alert('Datos no recibidos correctamente');
            window.location.href = 'gestion_zapatillas.php';
            </script>";
        exit;
    }
?>

<div class="container">
    <div class="login-container">
        <h2 class="text-center" style="margin-top: -15px;">Modificar Zapatilla</h2>

        <!--Formulario en el que en sus campos mostramos por defecto los valores actuales de las características de la zapatilla, para que viendolos, el administrador decida modificar los que crea conveniente-->
        <form method="POST" action="modificar_zapatilla.php" enctype="multipart/form-data">
            <!-- Este campo oculto envía el id de la zapatilla a través del formulario junto con el resto de valores de la zapatilla de los campos del formulario cuando se envía con el método POST (al pulsar el botón Modificar Zapatilla). Al button le incluimos un atributo name="modificar", para que desde el codigo PHP de este fichero, poder comprobar si la carga de este fichero corresponde a que viene desde gestion_zapatillas porque el usuario pulsó el botón Modificar de una zapatilla o es porque el usuario pulsó el botón Modificar Zapatilla de este mismo formulario-->
            <input type="hidden" name="id" value="<?php echo $zapatilla['id']; ?>">

            <div class="mb-3">
                <label for="nombre" class="form-label"><b>Marca y Modelo</b></label>
                <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $nombre_actual; ?>" required>
            </div>

            <!--En un campo de tipo textarea, el atributo value no es válido. Para mostrar el valor en el área de texto, hay que colocar el contenido directamente entre las etiquetas de apertura y cierre del textarea-->
            <div class="mb-3">
                <label for="descripcion" class="form-label"><b>Descripción</b></label>
                <textarea class="form-control" id="descripcion" name="descripcion" rows="2" required><?php echo $descripcion_actual; ?></textarea>
            </div>

            <div class="mb-3">
                <label for="precio" class="form-label"><b>Precio (€)</b></label>
                <input type="number" class="form-control" id="precio" name="precio" value="<?php echo $precio_actual; ?>" required step="0.01" min="0">
            </div>

            <!--El campo oculto es necesario cuando se utiliza un formulario que incluye un campo de tipo file, porque el input type="file" solo enviará el archivo si el usuario selecciona un nuevo archivo para subir. Si no selecciona nada (es decir, si deja el campo vacío), el formulario no enviará ninguna información sobre la imagen, y como resultado, no se podrá acceder a la imagen anterior desde el $_FILES-->
            <div class="mb-3">
                <label for="imagen" class="form-label"><b>Imagen</b></label>
                <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
                <img src="../..<?php echo  $imagen_actual; ?>" alt="Zapatilla Actual" class="d-block mx-auto mt-2" style="max-width: 150px;">
                <input type="hidden" name="imagen_actual" value="<?php echo $imagen_actual; ?>">
            </div>

            <div class="d-flex justify-content-center align-items-center gap-3">
                <a href="gestion_zapatillas.php" class="btn btn-custom">Volver</a>
                <button type="submit" class="btn btn-custom" name="modificar">Modificar Zapatilla</button>
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
