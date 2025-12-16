<?php
/**
 * Autenticador - Sistema ARCO
 * Maneja la autenticación de usuarios con soporte para 2FA
 * 
 * @version 2.0
 * @since 2025-12-15
 */

session_start();
require_once "conexion.php";
require_once "two_factor_auth.php";

// Verificar método de petición
if ($_SERVER['REQUEST_METHOD'] != "POST") {
    header("Location: ../login.html?error=" . urlencode("Petición no válida"));
    exit;
}

// Validar campos requeridos
if (empty($_POST['numeroDocumento']) || empty($_POST['contrasena'])) {
    header("Location: ../login.html?error=" . urlencode("Por favor complete todos los campos"));
    exit;
}

try {
    $num_documento = filter_var($_POST['numeroDocumento'], FILTER_VALIDATE_INT);
    $contrasena = $_POST['contrasena'];
    $recordarme = isset($_POST['recuerdame']);
    
    if (!$num_documento) {
        throw new Exception("Número de documento inválido");
    }
    
    $conexiondb = ConectarDB();
    
    // Buscar usuario por número de documento
    $stmt = $conexiondb->prepare("
        SELECT id_usuarios, nombre, apellido, num_doc, correo, num_telefono, 
               contrasena, rol, estado, two_factor_enabled, two_factor_method,
               intentos_fallidos, bloqueado_hasta
        FROM usuarios 
        WHERE num_doc = ?
    ");
    $stmt->bind_param("i", $num_documento);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Usuario no encontrado. Si desea registrarse, contacte al administrador.");
    }
    
    $usuario = $result->fetch_assoc();
    
    // ✅ CORRECCIÓN: Verificar si el usuario está activo (normalizado a mayúsculas)
    if (strtoupper($usuario['estado']) !== 'ACTIVO') {
        throw new Exception("Su cuenta está inactiva. Contacte al administrador.");
    }
    
    // Verificar si la cuenta está bloqueada temporalmente
    if ($usuario['bloqueado_hasta'] && strtotime($usuario['bloqueado_hasta']) > time()) {
        $minutos = ceil((strtotime($usuario['bloqueado_hasta']) - time()) / 60);
        throw new Exception("Cuenta bloqueada temporalmente. Intente nuevamente en $minutos minutos.");
    }
    
    // Verificar contraseña
    $hashBD = $usuario['contrasena'];
    $contraseñaValida = password_verify($contrasena, $hashBD) || $contrasena === $hashBD;
    
    if (!$contraseñaValida) {
        // Incrementar intentos fallidos
        $intentos = $usuario['intentos_fallidos'] + 1;
        $bloquear = $intentos >= 5;
        
        if ($bloquear) {
            $stmt = $conexiondb->prepare("
                UPDATE usuarios 
                SET intentos_fallidos = ?, bloqueado_hasta = DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                WHERE id_usuarios = ?
            ");
            $stmt->bind_param("ii", $intentos, $usuario['id_usuarios']);
            $stmt->execute();
            
            throw new Exception("Demasiados intentos fallidos. Cuenta bloqueada por 15 minutos.");
        } else {
            $stmt = $conexiondb->prepare("UPDATE usuarios SET intentos_fallidos = ? WHERE id_usuarios = ?");
            $stmt->bind_param("ii", $intentos, $usuario['id_usuarios']);
            $stmt->execute();
            
            $intentosRestantes = 5 - $intentos;
            throw new Exception("Contraseña incorrecta. Le quedan $intentosRestantes intentos.");
        }
    }
    
    // Limpiar intentos fallidos en login exitoso
    $stmt = $conexiondb->prepare("
        UPDATE usuarios 
        SET intentos_fallidos = 0, bloqueado_hasta = NULL 
        WHERE id_usuarios = ?
    ");
    $stmt->bind_param("i", $usuario['id_usuarios']);
    $stmt->execute();
    
    // Verificar si tiene 2FA habilitado
    if ($usuario['two_factor_enabled']) {
        $tfa = new TwoFactorAuth();
        
        // Verificar si el dispositivo ya es confiable
        if ($tfa->isDeviceTrusted($usuario['id_usuarios'])) {
            // Dispositivo confiable, permitir acceso directo sin 2FA
            $_SESSION['usuario_id'] = $usuario['id_usuarios'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['apellido'] = $usuario['apellido'];
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['correo'] = $usuario['correo'];
            $_SESSION['ultimo_acceso'] = date('Y-m-d H:i:s');
            
            // Actualizar último acceso
            $stmt = $conexiondb->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuarios = ?");
            $stmt->bind_param("i", $usuario['id_usuarios']);
            $stmt->execute();
            
            // Registrar en auditoría
            $stmt = $conexiondb->prepare("
                INSERT INTO auditoria (usuario_id, accion, descripcion, ip_address, user_agent, fecha_hora)
                VALUES (?, 'login', 'Inicio de sesión desde dispositivo confiable', ?, ?, NOW())
            ");
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'desconocido';
            $stmt->bind_param("iss", $usuario['id_usuarios'], $ipAddress, $userAgent);
            $stmt->execute();
            
            // Configurar cookie de recordar si está habilitado
            if ($recordarme) {
                $token = bin2hex(random_bytes(32));
                $expiracion = time() + (30 * 24 * 60 * 60); // 30 días
                
                setcookie('recordar_token', $token, $expiracion, '/', '', false, true);
                
                $stmt = $conexiondb->prepare("
                    UPDATE usuarios 
                    SET token_recordar = ?, token_recordar_expira = FROM_UNIXTIME(?)
                    WHERE id_usuarios = ?
                ");
                $stmt->bind_param("sii", $token, $expiracion, $usuario['id_usuarios']);
                $stmt->execute();
            }
            
            // Redirigir al dashboard
            header("Location: ../vistas/dashboard.php");
            exit;
        }
        
        // Dispositivo nuevo, solicitar 2FA
        // Guardar datos temporales para 2FA
        $_SESSION['temp_user_id'] = $usuario['id_usuarios'];
        $_SESSION['temp_user_data'] = $usuario;
        $_SESSION['temp_recordarme'] = $recordarme;
        
        // Generar y enviar código 2FA
        $metodo = $usuario['two_factor_method'] ?? 'email';
        $codigo = $tfa->generateVerificationCode();
        
        if ($tfa->saveVerificationCode($usuario['id_usuarios'], $codigo, $metodo)) {
            if ($metodo === 'email') {
                $tfa->sendEmailCode($usuario['correo'], $codigo, $usuario['nombre']);
            } else {
                $tfa->sendSMSCode($usuario['num_telefono'], $codigo);
            }
            
            // Redirigir a página de verificación 2FA
            header("Location: verificacion-2fa.php");
            exit;
        } else {
            throw new Exception("Error al generar código de verificación. Intente nuevamente.");
        }
    }
    
    // Login directo sin 2FA
    $_SESSION['usuario_id'] = $usuario['id_usuarios'];
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['apellido'] = $usuario['apellido'];
    $_SESSION['rol'] = $usuario['rol'];
    $_SESSION['correo'] = $usuario['correo'];
    $_SESSION['ultimo_acceso'] = date('Y-m-d H:i:s');
    
    // Actualizar último acceso
    $stmt = $conexiondb->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuarios = ?");
    $stmt->bind_param("i", $usuario['id_usuarios']);
    $stmt->execute();
    
    // Registrar en auditoría
    $stmt = $conexiondb->prepare("
        INSERT INTO auditoria (usuario_id, accion, descripcion, ip_address, user_agent, fecha_hora)
        VALUES (?, 'login', 'Inicio de sesión exitoso', ?, ?, NOW())
    ");
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'desconocido';
    $stmt->bind_param("iss", $usuario['id_usuarios'], $ipAddress, $userAgent);
    $stmt->execute();
    
    // Configurar cookie de recordar si está habilitado
    if ($recordarme) {
        $token = bin2hex(random_bytes(32));
        $expiracion = time() + (30 * 24 * 60 * 60); // 30 días
        
        setcookie('recordar_token', $token, $expiracion, '/', '', false, true);
        
        $stmt = $conexiondb->prepare("
            UPDATE usuarios 
            SET token_recordar = ?, token_recordar_expira = FROM_UNIXTIME(?)
            WHERE id_usuarios = ?
        ");
        $stmt->bind_param("sii", $token, $expiracion, $usuario['id_usuarios']);
        $stmt->execute();
    }
    
    // Redirigir al dashboard
    header("Location: ../vistas/dashboard.php");
    exit;
    
} catch (Exception $e) {
    error_log("Error en autenticación: " . $e->getMessage());
    header("Location: ../login.html?error=" . urlencode($e->getMessage()));
    exit;
}