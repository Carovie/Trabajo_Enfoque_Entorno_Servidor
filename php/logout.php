<?php
session_start();
session_unset();
session_destroy(); 
/*Redirigir a la pantalla de bienvenida después de que el usuario pulse el botón de  Cerrar Sesión. Incluimos en la URL el parametro logout=1 para que al cargar la pantalla index.html detecte que viene de un cierre de sesión del usuario*/
header("Location: ../index.html?logout=1"); 
exit;
?>

