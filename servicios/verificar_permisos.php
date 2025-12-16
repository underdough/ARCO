<?php
/**
 * Sistema de Verificación de Permisos
 * Verifica si un usuario tiene permiso para realizar una acción específica
 */

require_once 'conexion.php';

class SistemaPermisos {
    private $conn;
    private $cache_permisos = [];
    
    public function __construct() {
        $this->conn = ConectarDB();
    }
    
    /**
     * Verificar si el usuario actual tiene un permiso específico
     * @param string $codigo_permiso Código del permiso (ej: 'productos.crear')
     * @return bool
     */
    public function tienePermiso($codigo_permiso) {
        if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['rol'])) {
            return false;
        }
        
        $rol = $_SESSION['rol'];
        
        // Administrador siempre tiene todos los permisos
        if ($rol === 'administrador') {
            return true;
        }
        
        // Verificar en caché
        $cache_key = $rol . '_' . $codigo_permiso;
        if (isset($this->cache_permisos[$cache_key])) {
            return $this->cache_permisos[$cache_key];
        }
        
        // Consultar en base de datos
        $sql = "SELECT COUNT(*) as tiene 
                FROM roles_permisos rp
                INNER JOIN permisos p ON rp.id_permiso = p.id_permiso
                WHERE rp.rol = ? 
                AND p.codigo_permiso = ?
                AND p.activo = 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $rol, $codigo_permiso);
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        $tiene = $resultado['tiene'] > 0;
        
        // Guardar en caché
        $this->cache_permisos[$cache_key] = $tiene;
        
        return $tiene;
    }
    
    /**
     * Obtener todos los permisos de un rol
     * @param string $rol Nombre del rol
     * @return array
     */
    public function obtenerPermisosRol($rol) {
        $sql = "SELECT p.* 
                FROM roles_permisos rp
                INNER JOIN permisos p ON rp.id_permiso = p.id_permiso
                WHERE rp.rol = ? AND p.activo = 1
                ORDER BY p.modulo, p.accion";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $rol);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $permisos = [];
        while ($fila = $resultado->fetch_assoc()) {
            $permisos[] = $fila;
        }
        
        $stmt->close();
        return $permisos;
    }
    
    /**
     * Obtener permisos agrupados por módulo
     * @param string $rol Nombre del rol
     * @return array
     */
    public function obtenerPermisosAgrupadosPorModulo($rol) {
        $permisos = $this->obtenerPermisosRol($rol);
        $agrupados = [];
        
        foreach ($permisos as $permiso) {
            $modulo = $permiso['modulo'];
            if (!isset($agrupados[$modulo])) {
                $agrupados[$modulo] = [];
            }
            $agrupados[$modulo][] = $permiso;
        }
        
        return $agrupados;
    }
    
    /**
     * Verificar acceso a un módulo completo
     * @param string $modulo Nombre del módulo
     * @return bool
     */
    public function tieneAccesoModulo($modulo) {
        if (!isset($_SESSION['rol'])) {
            return false;
        }
        
        $rol = $_SESSION['rol'];
        
        if ($rol === 'administrador') {
            return true;
        }
        
        $sql = "SELECT COUNT(*) as tiene 
                FROM roles_permisos rp
                INNER JOIN permisos p ON rp.id_permiso = p.id_permiso
                WHERE rp.rol = ? 
                AND p.modulo = ?
                AND p.activo = 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $rol, $modulo);
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $resultado['tiene'] > 0;
    }
    
    /**
     * Redirigir si no tiene permiso
     * @param string $codigo_permiso Código del permiso requerido
     * @param string $redirect_url URL de redirección (opcional)
     */
    public function requierePermiso($codigo_permiso, $redirect_url = 'dashboard.php') {
        if (!$this->tienePermiso($codigo_permiso)) {
            header("Location: $redirect_url?error=No tiene permisos para acceder a esta función");
            exit;
        }
    }
    
    /**
     * Obtener descripción del rol
     * @param string $rol Nombre del rol
     * @return string
     */
    public function obtenerDescripcionRol($rol) {
        $descripciones = [
            'administrador' => 'Acceso completo al sistema',
            'gerente' => 'Gestión completa excepto permisos de sistema',
            'supervisor' => 'Supervisión y aprobación de operaciones',
            'almacenista' => 'Gestión de inventario y movimientos',
            'recepcionista' => 'Registro de entradas y salidas',
            'usuario' => 'Consulta básica del sistema'
        ];
        
        return $descripciones[$rol] ?? 'Sin descripción';
    }
}

/**
 * Función helper para verificar permisos rápidamente
 * @param string $codigo_permiso
 * @return bool
 */
function tiene_permiso($codigo_permiso) {
    static $sistema = null;
    if ($sistema === null) {
        $sistema = new SistemaPermisos();
    }
    return $sistema->tienePermiso($codigo_permiso);
}

/**
 * Función helper para requerir permiso
 * @param string $codigo_permiso
 */
function requiere_permiso($codigo_permiso) {
    static $sistema = null;
    if ($sistema === null) {
        $sistema = new SistemaPermisos();
    }
    $sistema->requierePermiso($codigo_permiso);
}
?>
