<?php
// Configuración de conexión a MySQL
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'pisada_firme'; // Nombre de la base de datos

// Conexión al servidor MySQL (aun no a la BBDD)
$con = new mysqli($host, $username, $password);

// Verificar la conexión
if ($con->connect_error) {
    //El comando die() en PHP es una función que detiene la ejecución del script y muestra un mensaje opcional
    die("Conexión fallida: " . $con->connect_error);
}

echo "Conexión exitosa.<br>";

// Crear la base de datos si no existe
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";

//Verifica si la consulta SQL fue exitosa
if ($con->query($sql) === TRUE) {
    echo "Base de datos '$dbname' creada correctamente.<br>";
} else {
    echo "Error al crear la base de datos: " . $con->error;
}

// Seleccionar la base de datos
$con->select_db($dbname);

// Crear la tabla `usuarios`
$sql = "
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,     
    nombre VARCHAR(50) NOT NULL,             
    apellidos VARCHAR(100) NOT NULL,           
    email VARCHAR(100) NOT NULL,     
    password VARCHAR(255) UNIQUE NOT NULL,         
    tipo_usuario ENUM('cliente', 'administrador') NOT NULL 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
/*ENGINE=InnoDB: Define el motor de almacenamiento (o "storage engine") que MySQL usará para gestionar la tabla. InnoDB es el motor de almacenamiento más popular y utilizado en MySQL*/

if ($con->query($sql) === TRUE) {
    echo "Tabla 'usuarios' creada correctamente.<br>";
} else {
    echo "Error al crear la tabla: 'usuarios': " . $con->error;
}


//Insertar dos cliente de prueba en la tabla usuarios
$sql = "INSERT INTO usuarios (nombre, apellidos, email, password, tipo_usuario) 
                VALUES ('Carlos', 'Gonzalez Garcia', 'administrador@pisada-firme.com', 'Admin1234', 'administrador')";

if ($con->query($sql) === TRUE) {
    echo "Usuario de prueba insertado correctamente.<br>";
} else {
    echo "Error al insertar el usuario: " . $con->error;
}

$sql = "INSERT INTO usuarios (nombre, apellidos, email, password, tipo_usuario) 
                VALUES ('Pedro', 'Ramos Fernandez', 'cliente@pisada-firme.com', 'Client4321', 'cliente')";

if ($con->query($sql) === TRUE) {
    echo "Usuario de prueba insertado correctamente.<br>";
} else {
    echo "Error al insertar el usuario: " . $con->error;
}


// Crear la tabla `zapatillas`
$sql = "
CREATE TABLE IF NOT EXISTS zapatillas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    imagen VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if ($con->query($sql) === TRUE) {
    echo "Tabla 'zapatillas' creada correctamente.<br>";
} else {
    echo "Error al crear la tabla 'zapatillas': " . $con->error;
}

// Insertar varias zapatillas en la tabla para tener algunas por defecto en la tienda 
$sql = "INSERT INTO zapatillas (nombre, descripcion, precio, imagen) 
                 VALUES ('Adidas Adizero', 'Zapatillas de running adidas Adizero Adios Pro Evo 1', 79.99, '/img/Playeros1.png'),
                 ('Asics METASPEED', 'Zapatillas de running Asics METASPEED SKY PARIS', 99.99, '/img/Playeros2.png'),
                 ('New Balance FuelCell', 'Zapatillas de running New Balance FuelCell Rebel v4', 49.99, '/img/Playeros3.png'),
                 ('Asics SUPERBLAST 2', 'Zapatillas de running Asics SUPERBLAST 2', 89.99, '/img/Playeros4.png'),
                 ('On Running Cloudboom', 'Zapatillas de On Running Cloudboom Strike', 59.99, '/img/Playeros5.png')";

if ($con->query($sql) === TRUE) {
    echo "Playero de prueba insertado correctamente.<br>";
} else {
    echo "Error al insertar el playero: " . $con->error;
}


// Crear la tabla `pedidos`
$sql = "
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,            -- Relación con la tabla de usuarios
    precio_total DECIMAL(10, 2) NOT NULL, -- Suma del precio de todas las zapatillas
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE -- Relación con la tabla usuarios
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if ($con->query($sql) === TRUE) {
    echo "Tabla 'pedidos' creada correctamente.<br>";
} else {
    echo "Error al crear la tabla 'pedidos': " . $con->error;
}


// Crear la tabla `zapatillas_pedido`
$sql = "
CREATE TABLE IF NOT EXISTS zapatillas_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    zapatilla_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,    -- Precio de esta zapatilla en el pedido
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,  -- Relación con la tabla pedidos
    FOREIGN KEY (zapatilla_id) REFERENCES zapatillas(id) ON DELETE CASCADE -- Relación con la tabla de zapatillass
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 ";

 if ($con->query($sql) === TRUE) {
    echo "Tabla 'zapatillas_pedido' creada correctamente.<br>";
} else {
    echo "Error al crear la tabla 'zapatillas_pedido': " . $con->error;
}

/*
//Para probar a insertar en las tablas pedidos y zapatillas_pedido. Es necesario tener en la tabla usuarios de la BBDD un usuario con id=1 y en la tabla zapatillas dos zapatillas con id=1 e id=2
$usuario_id = 1;
$precio_total = 299.95; // Precio total del pedido

// Insertar un pedido en la tabla `pedidos`
$sql = "INSERT INTO pedidos (usuario_id, precio_total) VALUES ($usuario_id, $precio_total)";

if ($con->query($sql) === TRUE) {
    echo "Pedido insertado correctamente.<br>";

    // Obtener el ID del último pedido insertado. La propiedad $con->insert_id se utiliza para obtener el ID del último registro insertado en la base de datos, cuando se utiliza una consulta INSERT INTO. Esta propiedad te da acceso al valor del campo autoincremental (como el campo id en la tabla pedidos que acabas de crear) que fue generado automáticamente al insertar un nuevo registro.
    $pedido_id = $con->insert_id;
    echo "ID del pedido: " . $pedido_id . "<br>";

    // Ahora insertamos las zapatillas relacionadas con este pedido. Suponemos que el pedido tiene dos zapatillas con id=1 e id=2, y con cantidades de 2 y 1 respectivamente

    // Zapatilla 1 (id=1, cantidad=2, precio=79.99)
    $zapatilla_id = 1;
    $cantidad = 2;
    $precio = 79.99;

    $sql_zapatillas = "INSERT INTO zapatillas_pedido (pedido_id, zapatilla_id, cantidad, precio) 
                       VALUES ($pedido_id, $zapatilla_id, $cantidad, $precio)";

    if ($con->query($sql_zapatillas) === TRUE) {
        echo "Zapatilla 1 insertada correctamente.<br>";
    } else {
        echo "Error al insertar zapatilla 1: " . $con->error . "<br>";
    }

    // Zapatilla 2 (id=2, cantidad=1, precio=99.99)
    $zapatilla_id = 2;
    $cantidad = 1;
    $precio = 99.99;

    $sql_zapatillas = "INSERT INTO zapatillas_pedido (pedido_id, zapatilla_id, cantidad, precio) 
                       VALUES ($pedido_id, $zapatilla_id, $cantidad, $precio)";

    if ($con->query($sql_zapatillas) === TRUE) {
        echo "Zapatilla 2 insertada correctamente.<br>";
    } else {
        echo "Error al insertar zapatilla 2: " . $con->error . "<br>";
    }

} else {
    echo "Error al insertar el pedido: " . $con->error . "<br>";
}
*/

// Cerrar la conexión
$con->close();
?>