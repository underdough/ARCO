# Migración del Sistema Antiguo al Nuevo

## 📋 Descripción

Este documento explica cómo migrar del sistema antiguo de gestión de usuarios (`Usuario.php`) al nuevo sistema mejorado (`gestion_usuarios.php`).

## 🔄 Diferencias entre Sistemas

| Característica | Sistema Antiguo | Sistema Nuevo |
|----------------|-----------------|---------------|
| Archivo principal | `Usuario.php` | `gestion_usuarios.php` |
| Servicios | Básicos | Mejorados con `_mejorado.php` |
| Búsqueda | No disponible | Tiempo real |
| Filtros | No disponible | Por rol y estado |
| Notificaciones | Básicas | Avanzadas (toast + inline) |
| Auditoría | No disponible | Completa |
| Confirmaciones | Simple | Doble para eliminar |
| Estados | 2 (activo/inactivo) | 3 (activo/inactivo/suspendido) |
| Roles | 2 | 5 |

## ✅ Compatibilidad

**Importante:** Ambos sistemas son **100% compatibles** y pueden coexistir:

- ✅ Usan la misma base de datos
- ✅ Usan la misma tabla de usuarios
- ✅ No hay conflictos entre archivos
- ✅ Puedes usar ambos simultáneamente

## 🚀 Proceso de Migración

### Opción 1: Migración Completa (Recomendada)

**Paso 1: Actualizar Base de Datos**
```bash
mysql -u root -p arco_bdd < base-datos/mejora_gestion_roles.sql
```

**Paso 2: Actualizar Enlaces en Vistas**

Buscar y reemplazar en todos los archivos PHP:
```php
// Cambiar de:
<a href="Usuario.php" class="menu-item">

// A:
<a href="gestion_usuarios.php" class="menu-item">
```

**Archivos a actualizar:**
- `vistas/dashboard.php` ✅ (ya actualizado)
- `vistas/productos.php`
- `vistas/categorias.php`
- `vistas/movimientos.php`
- `vistas/reportes.php`
- `vistas/configuracion.php`

**Paso 3: Verificar Funcionamiento**
1. Acceder a `gestion_usuarios.php`
2. Probar todas las funcionalidades
3. Verificar que los usuarios existentes se muestran correctamente

**Paso 4: (Opcional) Mantener Sistema Antiguo como Respaldo**
- Renombrar `Usuario.php` a `Usuario_backup.php`
- Mantener archivos antiguos por 30 días
- Eliminar después de verificar que todo funciona

### Opción 2: Migración Gradual

**Fase 1: Instalación (Semana 1)**
1. Ejecutar script SQL
2. Instalar nuevos archivos
3. Mantener ambos sistemas activos

**Fase 2: Pruebas (Semana 2)**
1. Probar nuevo sistema en paralelo
2. Capacitar administradores
3. Identificar y resolver problemas

**Fase 3: Transición (Semana 3)**
1. Actualizar enlaces principales
2. Comunicar cambio a usuarios
3. Monitorear uso

**Fase 4: Consolidación (Semana 4)**
1. Verificar que todos usan nuevo sistema
2. Archivar sistema antiguo
3. Documentar lecciones aprendidas

## 📊 Migración de Datos

### Verificar Datos Existentes

```sql
-- Ver usuarios actuales
SELECT 
    id_usuarios,
    num_doc,
    nombre,
    apellido,
    rol,
    estado,
    fecha_creacion
FROM usuarios
ORDER BY fecha_creacion DESC;

-- Verificar roles
SELECT rol, COUNT(*) as total
FROM usuarios
GROUP BY rol;

-- Verificar estados
SELECT estado, COUNT(*) as total
FROM usuarios
GROUP BY estado;
```

### Actualizar Datos si es Necesario

```sql
-- Actualizar estados al nuevo formato (si están en minúsculas)
UPDATE usuarios 
SET estado = UPPER(estado)
WHERE estado IN ('activo', 'inactivo');

-- Agregar fechas de modificación faltantes
UPDATE usuarios 
SET fecha_modificacion = fecha_creacion 
WHERE fecha_modificacion IS NULL;

-- Verificar que no hay valores NULL en campos requeridos
SELECT * FROM usuarios 
WHERE nombre IS NULL 
   OR apellido IS NULL 
   OR correo IS NULL 
   OR rol IS NULL;
```

## 🔧 Actualización de Enlaces

### Script de Actualización Automática

Crear archivo `actualizar_enlaces.php`:

```php
<?php
// Script para actualizar enlaces en archivos PHP
$archivos = [
    'vistas/productos.php',
    'vistas/categorias.php',
    'vistas/movimientos.php',
    'vistas/reportes.php',
    'vistas/configuracion.php'
];

$buscar = 'Usuario.php';
$reemplazar = 'gestion_usuarios.php';

foreach ($archivos as $archivo) {
    if (file_exists($archivo)) {
        $contenido = file_get_contents($archivo);
        $contenido_nuevo = str_replace($buscar, $reemplazar, $contenido);
        
        if ($contenido !== $contenido_nuevo) {
            file_put_contents($archivo, $contenido_nuevo);
            echo "✅ Actualizado: $archivo\n";
        } else {
            echo "ℹ️ Sin cambios: $archivo\n";
        }
    } else {
        echo "❌ No encontrado: $archivo\n";
    }
}

echo "\n✅ Proceso completado\n";
?>
```

Ejecutar:
```bash
php actualizar_enlaces.php
```

## 📝 Checklist de Migración

### Pre-Migración
- [ ] Hacer respaldo completo de base de datos
- [ ] Hacer respaldo de archivos PHP
- [ ] Documentar configuración actual
- [ ] Identificar usuarios administradores
- [ ] Planificar ventana de mantenimiento

### Durante Migración
- [ ] Ejecutar script SQL
- [ ] Verificar creación de tabla auditoria_usuarios
- [ ] Copiar nuevos archivos
- [ ] Actualizar enlaces en vistas
- [ ] Probar acceso al nuevo sistema
- [ ] Verificar que usuarios existentes se muestran
- [ ] Probar crear usuario
- [ ] Probar editar usuario
- [ ] Probar cambiar estado
- [ ] Probar eliminar usuario
- [ ] Verificar notificaciones
- [ ] Verificar auditoría

### Post-Migración
- [ ] Capacitar administradores
- [ ] Monitorear uso durante primera semana
- [ ] Recopilar feedback
- [ ] Resolver problemas identificados
- [ ] Documentar cambios realizados
- [ ] Archivar sistema antiguo
- [ ] Actualizar documentación de usuario

## 🐛 Problemas Comunes y Soluciones

### Problema 1: Error "Table 'auditoria_usuarios' doesn't exist"

**Causa:** Script SQL no se ejecutó correctamente

**Solución:**
```sql
-- Ejecutar manualmente
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

### Problema 2: Usuarios no se muestran

**Causa:** Permisos o ruta incorrecta

**Solución:**
1. Verificar que `servicios/listar_usuarios_mejorado.php` existe
2. Abrir directamente: `http://localhost/ARCO/servicios/listar_usuarios_mejorado.php`
3. Verificar errores en respuesta JSON
4. Revisar permisos de archivo

### Problema 3: Notificaciones no aparecen

**Causa:** JavaScript no carga

**Solución:**
1. Verificar que `componentes/gestion_usuarios.js` existe
2. Abrir consola del navegador (F12)
3. Buscar errores de carga
4. Verificar ruta en HTML: `<script src="../componentes/gestion_usuarios.js"></script>`

### Problema 4: Error al crear usuario

**Causa:** Validaciones o permisos

**Solución:**
1. Verificar que usuario tiene rol "administrador"
2. Revisar logs de PHP
3. Verificar que campos obligatorios están completos
4. Verificar que documento y email son únicos

## 📊 Monitoreo Post-Migración

### Métricas a Monitorear

```sql
-- Usuarios creados después de migración
SELECT COUNT(*) as nuevos_usuarios
FROM usuarios
WHERE fecha_creacion > '2025-12-16';

-- Acciones en auditoría
SELECT 
    accion,
    COUNT(*) as total,
    DATE(fecha_accion) as fecha
FROM auditoria_usuarios
GROUP BY accion, DATE(fecha_accion)
ORDER BY fecha DESC;

-- Usuarios por estado
SELECT 
    estado,
    COUNT(*) as total,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM usuarios), 2) as porcentaje
FROM usuarios
GROUP BY estado;

-- Usuarios por rol
SELECT 
    rol,
    COUNT(*) as total,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM usuarios), 2) as porcentaje
FROM usuarios
GROUP BY rol;
```

## 🔄 Rollback (Volver al Sistema Antiguo)

Si es necesario volver al sistema antiguo:

**Paso 1: Restaurar Enlaces**
```bash
# Revertir cambios en archivos
git checkout vistas/*.php
# O manualmente cambiar gestion_usuarios.php por Usuario.php
```

**Paso 2: (Opcional) Restaurar Base de Datos**
```bash
# Solo si hubo problemas con la migración
mysql -u root -p arco_bdd < backup_antes_migracion.sql
```

**Paso 3: Verificar**
1. Acceder a `Usuario.php`
2. Verificar que funciona correctamente

**Nota:** No es necesario eliminar los nuevos archivos, pueden coexistir.

## 📞 Soporte Durante Migración

### Antes de Migrar
- Revisar: `documentacion/INSTALACION_GESTION_USUARIOS.md`
- Hacer respaldo completo
- Planificar horario de baja actividad

### Durante Migración
- Tener respaldo disponible
- Monitorear logs en tiempo real
- Tener plan de rollback listo

### Después de Migración
- Monitorear primeras 24 horas
- Recopilar feedback de usuarios
- Documentar problemas y soluciones

## ✅ Verificación Final

Después de completar la migración, verificar:

1. ✅ Todos los usuarios existentes se muestran
2. ✅ Se pueden crear nuevos usuarios
3. ✅ Se pueden editar usuarios
4. ✅ Se pueden cambiar estados
5. ✅ Se pueden eliminar usuarios
6. ✅ Búsqueda funciona
7. ✅ Filtros funcionan
8. ✅ Notificaciones aparecen
9. ✅ Auditoría registra acciones
10. ✅ Todos los enlaces actualizados

## 📈 Beneficios Post-Migración

Después de migrar al nuevo sistema, tendrás:

- ✅ Búsqueda en tiempo real
- ✅ Filtros avanzados
- ✅ Notificaciones profesionales
- ✅ Auditoría completa
- ✅ Más roles disponibles
- ✅ Mejor seguridad
- ✅ Interfaz moderna
- ✅ Mejor experiencia de usuario

---

**Fecha de creación:** Diciembre 2025  
**Versión:** 1.0  
**Estado:** ✅ Listo para usar
