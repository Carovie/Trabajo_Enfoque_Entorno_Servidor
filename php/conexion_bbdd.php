<?php

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'pisada_firme';

$con = new mysqli($host, $username, $password, $dbname);

if ($con->connect_error) {
    die("Conexión fallida: " . $con->connect_error);
}
?>