<?php
require_once 'conexion.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $conexion = ConectarDB();
    
    $sql = "SELECT id_categorias, nombre_cat, subcategoria FROM categorias";
    $resultado = $conexion->query($sql);
    
    if ($resultado) {
        $categorias = [];
        
        while ($fila = $resultado->fetch_assoc()) {
            $categorias[] = [
                'id_categorias' => $fila['id_categorias'],
                'nombre_cat' => $fila['nombre_cat'],
                'subcategoria' => $fila['subcategoria']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $categorias,
            'total' => count($categorias)
        ]);
    } else {
        throw new Exception('Error en la consulta: ' . $conexion->error);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conexion->close();
?>
