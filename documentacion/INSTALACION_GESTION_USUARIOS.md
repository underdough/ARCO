# Instalación del Sistema de Gestión Avanzada de Usuarios

## Requisitos Previos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)
- Sistema ARCO base instalado

## Pasos de Instalación

### 1. Actualizar la Base de Datos

Ejecutar el script SQL para actualizar la estructura de la base de datos:

```bash
# Opción 1: Desde phpMyAdmin
# - Abrir phpMyAdmin
# - Seleccionar la base de datos 'arco_bdd'
# - Ir a la pestaña "SQL"
# - Copiar y pegar el contenido de: base-datos/mejora_gestion_roles.sql
# - Hacer clic en "Continuar"

# Opción 2: Desde línea de comandos
mysql -u root -p arco_bdd < base-datos/mejora_gestion_roles.sql
```

**Este script realizará:**
- Agregar nuevos roles (gerente)
- Modificar el campo estado a ENUM
- Agregar campos de auditoría
- Crear tabla de auditoría de usuarios
- Agregar índices para mejorar el rendimiento

### 2. Verificar Archivos Creados

Asegurarse de que los siguientes archivos estén en su lugar:

#### Backend (Servicios PHP)
```
servicios/
├── listar_usuarios_mejorado.php
├── registro_mejorado.php
├── actualizar_usuario_mejorado.php
├── cambiar_estado_usuario.php
└── eliminar_usuario_mejorado.php
```

#### Frontend (Vistas)
```
vistas/
└── gestion_usuarios.php
```

#### JavaScript
```
componentes/
└── gestion_usuarios.js
```

#### Base de Datos
```
base-datos/
└── mejora_gestion_roles.sql
```

#### Documentación
```
documentacion/
├── GUIA_GESTION_USUARIOS.md
└── INSTALACION_GESTION_USUARIOS.md
```

### 3. Configurar Permisos

Asegurarse de que el servidor web tenga permisos de lectura en todos los archivos:

```bash
# En Linux/Mac
chmod 644 servicios/*.php
chmod 644 vistas/*.php
chmod 644 componentes/*.js

# En Windows (no es necesario, pero verificar que IIS/Apache tenga acceso)
```

### 4. Verificar Conexión a Base de Datos

Asegurarse de que el archivo `servicios/conexion.php` esté correctamente configurado:

```php
<?php
function ConectarDB() {
    $host = 'localhost';
    $usuario = 'root';
    $password = '';
    $base_datos = 'arco_bdd';
    
    $conexion = new mysqli($host, $usuario, $password, $base_datos);
    
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }
    
    $conexion->set_charset("utf8mb4");
    return $conexion;
}
?>
```

### 5. Actualizar Usuario Administrador

Asegurarse de que existe al menos un usuario con rol de administrador:

```sql
-- Verificar usuarios administradores
SELECT * FROM usuarios WHERE rol = 'administrador';

-- Si no existe, actualizar un usuario existente
UPDATE usuarios 
SET rol = 'administrador' 
WHERE id_usuarios = 1;

-- O crear un nuevo administrador
INSERT INTO usuarios (num_doc, nombre, apellido, rol, cargos, correo, contrasena, num_telefono, fecha_creacion, estado)
VALUES (
    100000001,
    'Admin',
    'Sistema',
    'administrador',
    'Administrador General',
    'admin@arco.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    '0000000000',
    NOW(),
    'ACTIVO'
);
```

### 6. Probar la Instalación

#### Paso 1: Acceder al Sistema
1. Abrir navegador
2. Ir a: `http://localhost/ARCO/login.html`
3. Iniciar sesión con credenciales de administrador

#### Paso 2: Acceder a Gestión de Usuarios
1. En el menú lateral, hacer clic en "Usuarios"
2. Debería cargar la nueva interfaz de gestión de usuarios
3. Verificar que se muestren las estadísticas
4. Verificar que se muestre la tabla de usuarios

#### Paso 3: Probar Funcionalidades

**Crear Usuario:**
1. Clic en "Nuevo Usuario"
2. Completar formulario
3. Verificar que se cree correctamente

**Buscar Usuario:**
1. Escribir en el campo de búsqueda
2. Verificar que filtre en tiempo real

**Filtrar por Rol:**
1. Seleccionar un rol en el filtro
2. Verificar que muestre solo usuarios de ese rol

**Filtrar por Estado:**
1. Seleccionar un estado
2. Verificar que muestre solo usuarios con ese estado

**Editar Usuario:**
1. Clic en botón de editar
2. Modificar información
3. Guardar cambios
4. Verificar que se actualice

**Cambiar Estado:**
1. Clic en botón de cambiar estado
2. Confirmar cambio
3. Verificar que el estado cambie

**Eliminar Usuario:**
1. Clic en botón de eliminar
2. Confirmar eliminación
3. Verificar que se elimine

### 7. Verificar Auditoría

Comprobar que se están registrando las acciones:

```sql
-- Ver registros de auditoría
SELECT * FROM auditoria_usuarios ORDER BY fecha_accion DESC LIMIT 10;

-- Ver acciones de un usuario específico
SELECT * FROM auditoria_usuarios WHERE usuario_id = 1;

-- Ver acciones realizadas por un administrador
SELECT * FROM auditoria_usuarios WHERE realizado_por = 1;
```

## Migración desde Sistema Anterior

Si ya tiene usuarios en el sistema antiguo:

### 1. Actualizar Roles Existentes

```sql
-- Los roles existentes seguirán funcionando
-- Solo necesita actualizar si quiere usar los nuevos roles

-- Ejemplo: Actualizar usuarios a nuevos roles
UPDATE usuarios SET rol = 'gerente' WHERE cargos LIKE '%gerente%';
UPDATE usuarios SET rol = 'supervisor' WHERE cargos LIKE '%supervisor%';
```

### 2. Actualizar Estados

```sql
-- Convertir estados antiguos al nuevo formato
UPDATE usuarios SET estado = 'ACTIVO' WHERE estado = 'activo';
UPDATE usuarios SET estado = 'INACTIVO' WHERE estado = 'inactivo';
```

### 3. Agregar Fechas de Modificación

```sql
-- Inicializar fechas de modificación con fecha de creación
UPDATE usuarios 
SET fecha_modificacion = fecha_creacion 
WHERE fecha_modificacion IS NULL;
```

## Compatibilidad con Sistema Anterior

El nuevo sistema es **100% compatible** con el sistema anterior:

- ✅ Los archivos antiguos siguen funcionando
- ✅ Los usuarios existentes no se ven afectados
- ✅ Las sesiones actuales continúan funcionando
- ✅ Puede usar ambos sistemas simultáneamente

**Archivos antiguos que siguen funcionando:**
- `vistas/Usuario.php` (sistema anterior)
- `servicios/listar_usuarios.php`
- `servicios/registro.php`
- `servicios/editar_usuario.php`
- `servicios/eliminar_usuario.php`

**Nuevos archivos (sistema mejorado):**
- `vistas/gestion_usuarios.php` (sistema nuevo)
- `servicios/listar_usuarios_mejorado.php`
- `servicios/registro_mejorado.php`
- `servicios/actualizar_usuario_mejorado.php`
- `servicios/cambiar_estado_usuario.php`
- `servicios/eliminar_usuario_mejorado.php`

## Solución de Problemas

### Error: "Table 'auditoria_usuarios' doesn't exist"

**Solución:**
```sql
-- Ejecutar manualmente la creación de la tabla
CREATE TABLE IF NOT EXISTS `auditoria_usuarios` (
  `id_auditoria` INT NOT NULL AUTO_INCREMENT,
  `usuario_id` INT NOT NULL,
  `accion` ENUM('crear','editar','eliminar','activar','desactivar','suspender') NOT NULL,
  `campo_modificado` VARCHAR(50) NULL,
  `valor_anterior` TEXT NULL,
  `valor_nuevo` TEXT NULL,
  `realizado_por` INT NOT NULL,
  `fecha_accion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` VARCHAR(45) NULL,
  PRIMARY KEY (`id_auditoria`),
  INDEX `idx_usuario` (`usuario_id`),
  INDEX `idx_fecha` (`fecha_accion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
```

### Error: "Column 'fecha_modificacion' doesn't exist"

**Solución:**
```sql
-- Agregar columnas faltantes
ALTER TABLE `usuarios` 
ADD COLUMN `fecha_modificacion` DATETIME NULL DEFAULT NULL AFTER `fecha_creacion`,
ADD COLUMN `modificado_por` INT NULL DEFAULT NULL AFTER `fecha_modificacion`;
```

### Error: "Invalid enum value for column 'rol'"

**Solución:**
```sql
-- Actualizar el ENUM de roles
ALTER TABLE `usuarios` 
MODIFY COLUMN `rol` ENUM('administrador','usuario','almacenista','supervisor','gerente') 
COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'usuario';
```

### Error: "Invalid enum value for column 'estado'"

**Solución:**
```sql
-- Actualizar el ENUM de estados
ALTER TABLE `usuarios` 
MODIFY COLUMN `estado` ENUM('ACTIVO','INACTIVO','SUSPENDIDO') 
COLLATE utf8mb4_spanish_ci NOT NULL DEFAULT 'ACTIVO';
```

### Error: JavaScript no carga

**Solución:**
1. Verificar que el archivo `componentes/gestion_usuarios.js` existe
2. Verificar la ruta en el HTML: `<script src="../componentes/gestion_usuarios.js"></script>`
3. Abrir consola del navegador (F12) para ver errores
4. Verificar permisos del archivo

### Error: No se muestran usuarios

**Solución:**
1. Verificar que `servicios/listar_usuarios_mejorado.php` existe
2. Abrir el archivo directamente en el navegador para ver errores
3. Verificar conexión a base de datos
4. Verificar que hay usuarios en la tabla

## Respaldo y Restauración

### Crear Respaldo

Antes de instalar, crear un respaldo de la base de datos:

```bash
# Respaldo completo
mysqldump -u root -p arco_bdd > backup_antes_mejora.sql

# Solo tabla de usuarios
mysqldump -u root -p arco_bdd usuarios > backup_usuarios.sql
```

### Restaurar Respaldo

Si algo sale mal:

```bash
# Restaurar base de datos completa
mysql -u root -p arco_bdd < backup_antes_mejora.sql

# Restaurar solo usuarios
mysql -u root -p arco_bdd < backup_usuarios.sql
```

## Verificación Final

Lista de verificación después de la instalación:

- [ ] Script SQL ejecutado sin errores
- [ ] Tabla `auditoria_usuarios` creada
- [ ] Columnas `fecha_modificacion` y `modificado_por` agregadas
- [ ] Todos los archivos PHP en su lugar
- [ ] Archivo JavaScript cargando correctamente
- [ ] Puede acceder a `/vistas/gestion_usuarios.php`
- [ ] Puede crear un usuario nuevo
- [ ] Puede editar un usuario
- [ ] Puede cambiar estado de usuario
- [ ] Puede eliminar un usuario
- [ ] Búsqueda funciona correctamente
- [ ] Filtros funcionan correctamente
- [ ] Estadísticas se muestran correctamente
- [ ] Auditoría registra acciones

## Soporte

Para problemas durante la instalación:

1. Revisar logs de PHP: `php_error.log`
2. Revisar logs de MySQL
3. Revisar consola del navegador (F12)
4. Consultar documentación en `/documentacion/`

---

**Última actualización:** Diciembre 2025  
**Versión:** 2.0
