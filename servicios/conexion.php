<?php

function ConectarDB() {
    $host = "localhost";
    $user = "root";
    $db   = "arco_bdd";

    // Desactivar excepciones automáticas para controlar el error manualmente
    mysqli_report(MYSQLI_REPORT_OFF);

    // Intentos de contraseña (común en XAMPP: sin contraseña o 'root')
    $passwordAttempts = ["", "root"];

    foreach ($passwordAttempts as $pass) {
        try {
            $conexion = new mysqli($host, $user, $pass, $db);
            if ($conexion->connect_errno) {
                // Si es error de acceso denegado, intentamos con la siguiente contraseña
                if ($conexion->connect_errno === 1045) {
                    continue;
                }
                // Otros errores: detener
                die("No se ha podido conectar con la base de datos: " . $conexion->connect_error);
            }
            $conexion->set_charset("utf8mb4");
            return $conexion;
        } catch (Throwable $e) {
            // Continuar al siguiente intento en errores de conexión
            continue;
        }
    }

    // Si llegamos aquí, no se pudo conectar con ninguna contraseña probada
    error_log("Error de conexión MySQL: Acceso denegado para usuario 'root'@'localhost'. Verifique la contraseña del usuario root en MySQL/XAMPP.");
    die("Error de conexión a la base de datos. Por favor configure correctamente la contraseña de MySQL en servicios/conexion.php.");
}