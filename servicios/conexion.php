<?php

function ConectarDB() {
    $conexion = new mysqli("localhost", "root", "", "bdd_proyecto");

    if ($conexion->connect_errno) {
        die("No se ha podido conectar con la base de datos: " . $conexion->connect_error);
    }

    return $conexion;
}