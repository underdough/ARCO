# 📊 Estado de Uso de Tablas de Anomalías

## ✅ Tablas Activas (En Uso)

### 1. **`anomalias`** - Tabla Principal
**Estado:** ✅ **ACTIVA**

**Se usa en:**
- Crear anomalías (`guardar_anomalia_simple.php`)
- Editar anomalías (`guardar_anomalia_simple.php`)
- Listar anomalías (`obtener_anomalias.php`)
- Ver detalle (`obtener_anomalia.php`)
- Cambiar estado (`cambiar_estado_anomalia.php`)
- Reportes (`reporte_anomalias.php`)

**Campos importantes:**
- `codigo_seguimiento` - Generado automáticamente
- `estado` - Actualizado al cambiar estado
- `fecha_resolucion` - Se llena al marcar como resuelta/cerrada

---

### 2. **`anomalias_auditoria`** - Registro de Auditoría
**Estado:** ✅ **ACTIVA**

**Se llena cuando:**
- ✅ Creas una anomalía → Acción: "crear"
- ✅ Editas una anomalía → Acción: "editar"
- ✅ Cambias el estado → Acción: "cambiar_estado"

**Función que la usa:**
```php
registrarAuditoria($conn, $anomalia_id, $usuario_id, $accion, $descripcion, $datos_anteriores, $datos_nuevos)
```

**Ubicación:** `servicios/guardar_anomalia_simple.php` (líneas 120-140)

**Datos que guarda:**
- IP del usuario
- User Agent (navegador)
- Datos anteriores (JSON)
- Datos nuevos (JSON)
- Fecha y hora exacta

**Para verificar:**
```sql
SELECT * FROM anomalias_auditoria ORDER BY fecha_accion DESC LIMIT 10;
```

---

### 3. **`anomalias_historial`** - Historial de Cambios
**Estado:** ✅ **ACTIVA**

**Se llena cuando:**
- ✅ Editas campos de una anomalía (guarda cada cambio)
- ✅ Cambias el estado de una anomalía

**Función que la usa:**
```php
// En guardar_anomalia_simple.php (líneas 130-150)
// Registra cambios campo por campo
```

**Ubicación:** 
- `servicios/guardar_anomalia_simple.php`
- `servicios/cambiar_estado_anomalia.php`

**Datos que guarda:**
- Campo modificado
- Valor anterior
- Valor nuevo
- Usuario que hizo el cambio
- Comentario

**Para verificar:**
```sql
SELECT * FROM anomalias_historial ORDER BY fecha_modificacion DESC LIMIT 10;
```

---

### 4. **`anomalias_notificaciones`** - Sistema de Notificaciones
**Estado:** ✅ **ACTIVA**

**Se llena cuando:**
- ✅ Asignas un responsable a una anomalía
- ✅ Creas una anomalía urgente (notifica a administradores)
- ✅ Cambias el estado (notifica al responsable)

**Función que la usa:**
```php
enviarNotificacion($conn, $anomalia_id, $usuario_id, $tipo, $mensaje)
notificarAdministradores($conn, $anomalia_id, $titulo, $prioridad)
```

**Ubicación:** `servicios/guardar_anomalia_simple.php` (líneas 220-250)

**Tipos de notificación:**
- `creacion` - Nueva anomalía creada
- `asignacion` - Responsable asignado
- `actualizacion` - Anomalía actualizada
- `resolucion` - Anomalía resuelta
- `vencimiento` - Fecha límite próxima

**Para verificar:**
```sql
SELECT * FROM anomalias_notificaciones ORDER BY fecha_envio DESC LIMIT 10;
```

---

## ⚠️ Tablas Preparadas (No Usadas Actualmente)

### 5. **`anomalias_materiales`** - Materiales Afectados Detallados
**Estado:** ⚠️ **PREPARADA - NO EN USO**

**Por qué no se usa:**
- Actualmente se usa el campo `materiales_afectados` (texto) en la tabla `anomalias`
- Esta tabla está preparada para un futuro donde se quiera relacionar directamente con productos

**Uso futuro:**
- Relacionar anomalías con productos específicos del inventario
- Registrar cantidades exactas afectadas
- Calcular costos automáticamente

**Para activarla en el futuro:**
Modificar `guardar_anomalia_simple.php` para insertar en esta tabla cuando se especifiquen materiales.

---

### 6. **`anomalias_comentarios`** - Sistema de Comentarios
**Estado:** ⚠️ **PREPARADA - NO EN USO**

**Por qué no se usa:**
- No se implementó la funcionalidad de comentarios en la interfaz actual
- Está preparada para futuras mejoras

**Uso futuro:**
- Permitir que usuarios comenten en las anomalías
- Sistema de seguimiento colaborativo
- Adjuntar archivos a comentarios

**Para activarla en el futuro:**
Crear interfaz de comentarios en `anomalia_seguimiento.php` o `anomalia_detalle.php`.

---

## 🔍 Cómo Verificar que las Tablas se Están Llenando

### Paso 1: Ejecutar Consultas SQL
```sql
-- Ver registros en auditoría
SELECT COUNT(*) as total FROM anomalias_auditoria;
SELECT * FROM anomalias_auditoria ORDER BY fecha_accion DESC LIMIT 5;

-- Ver registros en historial
SELECT COUNT(*) as total FROM anomalias_historial;
SELECT * FROM anomalias_historial ORDER BY fecha_modificacion DESC LIMIT 5;

-- Ver notificaciones
SELECT COUNT(*) as total FROM anomalias_notificaciones;
SELECT * FROM anomalias_notificaciones ORDER BY fecha_envio DESC LIMIT 5;
```

### Paso 2: Probar Acciones
1. **Crear una anomalía nueva** → Debe crear registro en `anomalias_auditoria`
2. **Editar una anomalía** → Debe crear registros en `anomalias_historial` y `anomalias_auditoria`
3. **Asignar responsable** → Debe crear registro en `anomalias_notificaciones`
4. **Cambiar estado** → Debe crear registros en las 3 tablas

### Paso 3: Si Están Vacías
Si después de crear/editar anomalías las tablas siguen vacías:

1. **Verificar que existen las tablas:**
```sql
SHOW TABLES LIKE 'anomalias_%';
```

2. **Ejecutar el script SQL:**
```sql
-- Ejecutar: base-datos/actualizar_anomalias_avanzado.sql
```

3. **Verificar permisos:**
```sql
SHOW GRANTS FOR 'root'@'localhost';
```

---

## 📝 Recomendaciones

### ✅ Mantener Activas
- `anomalias_auditoria` - Importante para seguridad y trazabilidad
- `anomalias_historial` - Útil para ver cambios históricos
- `anomalias_notificaciones` - Mejora la comunicación del equipo

### 🔮 Activar en el Futuro
- `anomalias_materiales` - Cuando necesites relacionar con productos específicos
- `anomalias_comentarios` - Cuando necesites colaboración en tiempo real

### ❌ NO Eliminar
Ninguna de estas tablas debe eliminarse, ya que:
- Las activas son necesarias para el funcionamiento
- Las preparadas están listas para futuras mejoras
- No ocupan espacio significativo si están vacías

---

## 🎯 Resumen Rápido

| Tabla | Estado | Se Llena Al | Importancia |
|-------|--------|-------------|-------------|
| `anomalias` | ✅ Activa | Crear/Editar | ⭐⭐⭐⭐⭐ Crítica |
| `anomalias_auditoria` | ✅ Activa | Crear/Editar/Cambiar Estado | ⭐⭐⭐⭐ Alta |
| `anomalias_historial` | ✅ Activa | Editar/Cambiar Estado | ⭐⭐⭐⭐ Alta |
| `anomalias_notificaciones` | ✅ Activa | Asignar/Crear Urgente | ⭐⭐⭐ Media |
| `anomalias_materiales` | ⚠️ Preparada | No se usa aún | ⭐⭐ Baja |
| `anomalias_comentarios` | ⚠️ Preparada | No se usa aún | ⭐⭐ Baja |

---

**Última actualización:** 17 de diciembre de 2024  
**Versión del módulo:** 2.0 - Avanzado