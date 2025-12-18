<?php
/**
 * Guardar información de empresa con carga de logo
 */

session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit;
}

require_once 'conexion.php';
$conexion = ConectarDB();

header('Content-Type: application/json');

try {
    // Validar datos
    $nombre = isset($_POST['companyName']) ? trim($_POST['companyName']) : '';
    $nif = isset($_POST['companyTaxId']) ? trim($_POST['companyTaxId']) : '';
    $direccion = isset($_POST['companyAddress']) ? trim($_POST['companyAddress']) : '';
    $ciudad = isset($_POST['companyCity']) ? trim($_POST['companyCity']) : '';
    $telefono = isset($_POST['companyPhone']) ? trim($_POST['companyPhone']) : '';
    $email = isset($_POST['companyEmail']) ? trim($_POST['companyEmail']) : '';
    
    if (empty($nombre)) {
        throw new Exception('El nombre de la empresa es requerido');
    }
    
    // Procesar logo si se cargó
    $logo_path = null;
    if (isset($_FILES['companyLogo']) && $_FILES['companyLogo']['error'] === UPLOAD_ERR_OK) {
        $logo_path = procesarLogo($_FILES['companyLogo']);
    }
    
    // Actualizar empresa
    $query = "UPDATE empresa SET 
              nombre = ?,
              nif = ?,
              direccion = ?,
              ciudad = ?,
              telefono = ?,
              email = ?";
    
    $params = [$nombre, $nif, $direccion, $ciudad, $telefono, $email];
    $types = 'ssssss';
    
    if ($logo_path) {
        $query .= ", logo = ?";
        $params[] = $logo_path;
        $types .= 's';
    }
    
    $query .= " WHERE id = 2";
    
    $stmt = $conexion->prepare($query);
    if (!$stmt) {
        throw new Exception("Error preparando consulta: " . $conexion->error);
    }
    
    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        throw new Exception("Error actualizando empresa: " . $stmt->error);
    }
    
    // Registrar en auditoría (si la tabla existe)
    try {
        $usuario_id = $_SESSION['usuario_id'];
        $accion = "Actualización de información de empresa";
        $detalles = "Nombre: $nombre, NIF: $nif";
        if ($logo_path) {
            $detalles .= ", Logo actualizado";
        }
        
        $auditQuery = "INSERT INTO auditoria (usuario_id, accion, detalles, fecha) VALUES (?, ?, ?, NOW())";
        $auditStmt = $conexion->prepare($auditQuery);
        if ($auditStmt) {
            $auditStmt->bind_param('iss', $usuario_id, $accion, $detalles);
            $auditStmt->execute();
        }
    } catch (Exception $auditError) {
        // Ignorar errores de auditoría, no es crítico
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Información de empresa guardada correctamente',
        'logo_path' => $logo_path
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conexion->close();

/**
 * Procesa y valida el logo de la empresa
 */
function procesarLogo($file) {
    // Validaciones
    $max_size = 5 * 1024 * 1024; // 5MB
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // Validar tamaño
    if ($file['size'] > $max_size) {
        throw new Exception('El archivo es demasiado grande. Máximo 5MB.');
    }
    
    // Validar tipo MIME
    $mime_type = $file['type']; // Usar tipo MIME del cliente como fallback
    
    // Intentar obtener tipo MIME real si finfo está disponible
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        }
    }
    
    if (!in_array($mime_type, $allowed_types)) {
        throw new Exception('Tipo de archivo no permitido. Solo JPG, PNG, GIF o WebP.');
    }
    
    // Validar extensión
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_extensions)) {
        throw new Exception('Extensión de archivo no permitida.');
    }
    
    // Crear directorio si no existe
    $upload_dir = __DIR__ . '/../recursos/logos/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            throw new Exception('No se pudo crear el directorio de logos.');
        }
    }
    
    // Generar nombre único
    $filename = 'empresa_logo_' . time() . '.' . $file_ext;
    $filepath = $upload_dir . $filename;
    
    // Mover archivo
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Error al subir el archivo.');
    }
    
    // Optimizar imagen (si falla, continuar de todas formas)
    try {
        optimizarImagen($filepath, $mime_type);
    } catch (Exception $e) {
        // Ignorar errores de optimización, la imagen ya está subida
    }
    
    // Retornar ruta relativa desde la raíz del proyecto
    return '/ARCO/recursos/logos/' . $filename;
}

/**
 * Optimiza la imagen (redimensiona si es necesario)
 */
function optimizarImagen($filepath, $mime_type) {
    // Validar que GD esté disponible
    if (!extension_loaded('gd')) {
        return; // Si no está disponible, no optimizar
    }
    
    // Validar que el archivo existe
    if (!file_exists($filepath)) {
        return;
    }
    
    // Cargar imagen según tipo
    $image = null;
    switch ($mime_type) {
        case 'image/jpeg':
            $image = @imagecreatefromjpeg($filepath);
            break;
        case 'image/png':
            $image = @imagecreatefrompng($filepath);
            break;
        case 'image/gif':
            $image = @imagecreatefromgif($filepath);
            break;
        case 'image/webp':
            if (function_exists('imagecreatefromwebp')) {
                $image = @imagecreatefromwebp($filepath);
            }
            break;
    }
    
    if (!$image) {
        return; // Si no se puede cargar, dejar la imagen original
    }
    
    try {
        // Obtener dimensiones
        $width = imagesx($image);
        $height = imagesy($image);
        
        // Redimensionar si es muy grande (máximo 500x500)
        $max_dimension = 500;
        if ($width > $max_dimension || $height > $max_dimension) {
            $ratio = min($max_dimension / $width, $max_dimension / $height);
            $new_width = (int)($width * $ratio);
            $new_height = (int)($height * $ratio);
            
            $resized = imagecreatetruecolor($new_width, $new_height);
            if ($resized) {
                imagecopyresampled($resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                
                // Guardar imagen optimizada
                switch ($mime_type) {
                    case 'image/jpeg':
                        @imagejpeg($resized, $filepath, 85);
                        break;
                    case 'image/png':
                        @imagepng($resized, $filepath, 8);
                        break;
                    case 'image/gif':
                        @imagegif($resized, $filepath);
                        break;
                    case 'image/webp':
                        if (function_exists('imagewebp')) {
                            @imagewebp($resized, $filepath, 85);
                        }
                        break;
                }
                
                imagedestroy($resized);
            }
        } else {
            // Guardar con compresión aunque no se redimensione
            switch ($mime_type) {
                case 'image/jpeg':
                    @imagejpeg($image, $filepath, 85);
                    break;
                case 'image/png':
                    @imagepng($image, $filepath, 8);
                    break;
            }
        }
    } finally {
        if ($image) {
            imagedestroy($image);
        }
    }
}
