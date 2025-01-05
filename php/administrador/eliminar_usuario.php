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

//Si el usuario no está logueado o está logueado y es de tipo cliente, le mostramos el mensaje
if ((!$usuario_logueado) || ($tipo_usuario === 'cliente')) {
    echo "
    <script>
        alert('Sólo los usuarios de tipo administrador pueden gestionar los usuarios de la tienda');
        window.location.href = '../login.php';
    </script>";
    
        };

     //Si el usuario está logueado y es de tipo administrador y se ha enviado alguna acción de tipo POST como llegada a esta pagina, establecemos conexión con la BBDD para implementar la funcionalidad asociada a la eliminación del usuario indicado desde la página gestion_usuarios.php
    if (($usuario_logueado)&&($tipo_usuario === 'administrador')&&($_SERVER['REQUEST_METHOD'] == 'POST')) {

    include('../conexion_bbdd.php');

        $id = $_POST['id']; //Obtenemos el id del usuario a borrar recibido desde el formulario de gestion_usuarios.php al pulsar el botón de Eliminar de alguno de los usuarios 

        //Eliminamos el usuario de la BBDD
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $id);

        //Generamos codigo JavaScript para mostrar mensaje con alert y redirigir de vuelta a gestion_usuarios
        if ($stmt->execute()) {
            echo "
            <script>
                alert('Usuario eliminado con éxito');
                window.location.href = 'gestion_usuarios.php';
            </script>";
            exit();
        } else {
            echo "
            <script>
                alert('Error al eliminar el usuario');
                window.location.href = 'gestion_usuarios.php';
            </script>";
            exit();
        }
        $con->close();
}

?>

