<?php

function ConectarDB() {
    $conexion = new mysqli("localhost", "root", "", "arco_bdd");

    if ($conexion->connect_errno) {
        die("No se ha podido conectar con la base de datos: " . $conexion->connect_error);
    }

    return $conexion;
}