<?php
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['rol'] = 'administrador';
$_GET['pagina'] = 1;
$_GET['limite'] = 10;
$_GET['orden'] = 'nombre';
$_GET['direccion'] = 'ASC';
$_GET['busqueda'] = '';

include 'listar_productos.php';
