<?php

include "conexion.php";

if($_SERVER['REQUEST_METHOD'] != "POST"){

    echo "Tu peticion ha sido rechazada";

    exit;

} else {

    if (!isset($_POST['num_documento']) || $_POST['num_documento'] == "" || !isset($_POST['contrasena']) || $_POST['contrasena'] == ""){

        echo "Hay datos errados";

        exit;

    } else {

        $num_documento = filter_var($_POST['num_documento'], FILTER_VALIDATE_INT);
        $contrasena = $_POST['contrasena'];

        unset($_POST['num_documento'], $_POST['contrasena']);

        $conexiondb = ConectarDB();

        $sentencia_buscar = "SELECT * FROM usuarios WHERE num_documento = '$num_documento' AND contrasena = '$contrasena';";

        $resultado = $conexiondb->query($sentencia_buscar);

        if ($resultado->num_rows > 0){
            $hallado = $resultado->fetch_assoc();

            echo "Bienvenido " . $hallado['nombres'] . " " . $hallado['apellidos'] . " ";
        } else {

            echo "Usted no se encuentra registrado en la base de datos. Por favor comuniquese con un administrador";
            echo '<a href="index.html"> Volver al inicio</a>';
        }
    }
}