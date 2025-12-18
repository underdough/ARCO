<?php
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['rol'] = 'administrador';
$_GET['pagina'] = 1;
$_GET['limite'] = 10;

include 'listar_categorias.php';
