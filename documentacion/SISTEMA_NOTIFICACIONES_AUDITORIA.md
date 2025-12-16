# Sistema de Notificaciones y Auditoría - Gestión de Usuarios

## Descripción General

El sistema de gestión de usuarios de ARCO incluye un completo sistema de notificaciones visuales y auditoría que cumple con todos los criterios de aceptación especificados.

## 🔔 Sistema de Notificaciones

### Tipos de Notificaciones

El sistema implementa **dos tipos de notificaciones**:

#### 1. Alertas en Modales (Inline Alerts)
- Aparecen dentro de los modales de creación y edición
- Permanecen visibles hasta que el usuario cierre el modal
- Útiles para errores de validación y confirmaciones

**Tipos:**
- ✅ **Success (Verde)**: Operación exitosa
- ❌ **Error (Rojo)**: Error en la operación
- ℹ️ **Info (Azul)**: Información general
- ⚠️ **Warning (Amarillo)**: Advertencias

#### 2. Notificaciones Toast (Esquina Superior Derecha)
- Aparecen en la esquina superior derecha
- Se auto-cierran después de 5 segundos
- Pueden cerrarse manualmente con el botón X
- Animación suave de entrada y salida
- Múltiples notificaciones se apilan verticalmente

**Características:**
- Icono según el tipo de mensaje
- Borde de color según el tipo
- Animación de deslizamiento
- Auto-cierre programado
- Botón de cierre manual

## 📋 Proceso de Notificaciones por Operación

### 1. Creación de Usuario

**Flujo:**
1. Usuario completa el formulario
2. Sistema valida los datos
3. Si hay errores: Alerta roja en el modal
4. Si es exitoso:
   - Alerta verde en el modal: "✅ Usuario creado exitosamente"
   - Notificación toast: "Usuario '[Nombre]' creado exitosamente"
   - Registro en auditoría
   - Modal se cierra automáticamente después de 1.5 segundos
   - Tabla se actualiza mostrando el nuevo usuario

**Mensajes:**
- ✅ "Usuario '[Nombre Completo]' creado exitosamente"
- ❌ "El número de documento ya está registrado"
- ❌ "El correo electrónico ya está registrado"
- ❌ "Las contraseñas no coinciden"

### 2. Edición de Usuario

**Flujo:**
1. Administrador hace clic en "Editar"
2. Modal se abre con datos actuales
3. Administrador modifica información
4. Sistema solicita confirmación: "¿Está seguro de actualizar la información del usuario '[Nombre]'?"
5. Si confirma:
   - Alerta verde en el modal: "✅ Usuario actualizado correctamente"
   - Notificación toast: "Usuario '[Nombre]' actualizado correctamente"
   - Registro en auditoría con número de cambios
   - Modal se cierra automáticamente
   - Tabla se actualiza con nueva información

**Mensajes:**
- ✅ "Usuario '[Nombre]' actualizado correctamente"
- ❌ "El número de documento ya está registrado en otro usuario"
- ❌ "El correo electrónico ya está registrado en otro usuario"
- ℹ️ "No se realizaron cambios"

### 3. Desactivación de Usuario

**Flujo:**
1. Administrador hace clic en botón "Cambiar Estado"
2. Sistema muestra confirmación personalizada según el nuevo estado:
   - **Para INACTIVO**: "¿Está seguro de DESACTIVAR al usuario '[Nombre]'? El usuario no podrá acceder al sistema hasta que sea reactivado."
   - **Para SUSPENDIDO**: "¿Está seguro de SUSPENDER al usuario '[Nombre]'? Esta acción indica una suspensión temporal por razones administrativas."
   - **Para ACTIVO**: "¿Está seguro de ACTIVAR al usuario '[Nombre]'? El usuario podrá acceder al sistema normalmente."
3. Si confirma:
   - Notificación toast: "✅ Usuario '[Nombre]' [desactivado/suspendido/activado] correctamente"
   - Registro en auditoría con cambio de estado
   - Tabla se actualiza mostrando el nuevo estado con badge de color

**Mensajes:**
- ✅ "Usuario '[Nombre]' desactivado correctamente"
- ✅ "Usuario '[Nombre]' suspendido correctamente"
- ✅ "Usuario '[Nombre]' activado correctamente"
- ℹ️ "Cambio de estado cancelado"

### 4. Eliminación de Usuario

**Flujo:**
1. Administrador hace clic en botón "Eliminar"
2. **Primera confirmación**: "⚠️ ADVERTENCIA: ELIMINACIÓN PERMANENTE - ¿Está seguro de eliminar al usuario '[Nombre]'? Esta acción NO se puede deshacer. Se recomienda DESACTIVAR el usuario en lugar de eliminarlo."
3. Si confirma, **segunda confirmación**: "CONFIRMACIÓN FINAL - Escriba mentalmente 'CONFIRMAR' para proceder con la eliminación de '[Nombre]' - ¿Desea continuar?"
4. Si confirma ambas:
   - Notificación toast: "✅ Usuario '[Nombre]' eliminado del sistema"
   - Registro en auditoría
   - Usuario desaparece de la tabla

**Mensajes:**
- ✅ "Usuario '[Nombre]' eliminado del sistema"
- ℹ️ "Eliminación cancelada"
- ❌ "No puede eliminar su propia cuenta"

## 📊 Sistema de Auditoría

### Registro en Base de Datos

Todas las acciones quedan registradas en la tabla `auditoria_usuarios`:

```sql
CREATE TABLE `auditoria_usuarios` (
  `id_auditoria` INT NOT NULL AUTO_INCREMENT,
  `usuario_id` INT NOT NULL,
  `accion` ENUM('crear','editar','eliminar','activar','desactivar','suspender'),
  `campo_modificado` VARCHAR(50) NULL,
  `valor_anterior` TEXT NULL,
  `valor_nuevo` TEXT NULL,
  `realizado_por` INT NOT NULL,
  `fecha_accion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` VARCHAR(45) NULL,
  PRIMARY KEY (`id_auditoria`)
);
```

### Información Registrada

Para cada acción se registra:

1. **ID del usuario afectado**: Usuario sobre el que se realizó la acción
2. **Tipo de acción**: crear, editar, eliminar, activar, desactivar, suspender
3. **Campo modificado**: (Solo para ediciones) Qué campo se cambió
4. **Valor anterior**: Valor antes del cambio
5. **Valor nuevo**: Valor después del cambio
6. **Realizado por**: ID del administrador que realizó la acción
7. **Fecha y hora**: Timestamp exacto de la acción
8. **Dirección IP**: (Preparado para implementación futura)

### Registro en Consola del Navegador

Además del registro en base de datos, cada acción se registra en la consola del navegador con formato estructurado:

```
╔════════════════════════════════════════════════════════════════
║ REGISTRO DE AUDITORÍA - GESTIÓN DE USUARIOS
╠════════════════════════════════════════════════════════════════
║ Fecha/Hora: 16/12/2025, 10:30:45
║ Acción: CREAR USUARIO
║ Detalles: Usuario "Juan Pérez" creado con rol: almacenista
║ Usuario: Admin Sistema
╚════════════════════════════════════════════════════════════════
```

### Ejemplos de Registros

#### Creación de Usuario
```
Acción: CREAR USUARIO
Detalles: Usuario "Juan Pérez" creado con rol: almacenista
```

#### Edición de Usuario
```
Acción: EDITAR USUARIO
Detalles: Usuario "Juan Pérez" actualizado - 3 cambios realizados
Campos modificados:
- correo: juan@old.com → juan@new.com
- rol: usuario → almacenista
- cargos: Almacén 1 → Almacén Principal
```

#### Cambio de Estado
```
Acción: CAMBIAR ESTADO
Detalles: Usuario "Juan Pérez" - Estado: ACTIVO → INACTIVO
```

#### Eliminación
```
Acción: ELIMINAR USUARIO
Detalles: Usuario "Juan Pérez" eliminado permanentemente del sistema
```

## 🔍 Consultar Auditoría

### Desde la Base de Datos

```sql
-- Ver todas las acciones recientes
SELECT 
    a.fecha_accion,
    a.accion,
    CONCAT(u.nombre, ' ', u.apellido) as usuario_afectado,
    CONCAT(admin.nombre, ' ', admin.apellido) as realizado_por,
    a.campo_modificado,
    a.valor_anterior,
    a.valor_nuevo
FROM auditoria_usuarios a
JOIN usuarios u ON a.usuario_id = u.id_usuarios
JOIN usuarios admin ON a.realizado_por = admin.id_usuarios
ORDER BY a.fecha_accion DESC
LIMIT 50;

-- Ver acciones sobre un usuario específico
SELECT * FROM auditoria_usuarios 
WHERE usuario_id = 1 
ORDER BY fecha_accion DESC;

-- Ver acciones realizadas por un administrador
SELECT * FROM auditoria_usuarios 
WHERE realizado_por = 1 
ORDER BY fecha_accion DESC;

-- Ver solo eliminaciones
SELECT * FROM auditoria_usuarios 
WHERE accion = 'eliminar' 
ORDER BY fecha_accion DESC;

-- Estadísticas de acciones por tipo
SELECT 
    accion,
    COUNT(*) as total,
    DATE(fecha_accion) as fecha
FROM auditoria_usuarios
GROUP BY accion, DATE(fecha_accion)
ORDER BY fecha DESC;
```

### Desde la Consola del Navegador

1. Abrir DevTools (F12)
2. Ir a la pestaña "Console"
3. Realizar acciones en el sistema
4. Ver registros formateados en tiempo real

**Filtrar registros:**
```javascript
// En la consola del navegador
console.log('Mostrando solo registros de auditoría');
```

## ✅ Cumplimiento de Criterios de Aceptación

### 1. Registro de Todas las Acciones
✅ **CUMPLIDO**: Todas las acciones (crear, editar, eliminar, cambiar estado) se registran en:
- Base de datos (tabla `auditoria_usuarios`)
- Consola del navegador (formato estructurado)

### 2. Reflejar Cambios en Base de Datos
✅ **CUMPLIDO**: Todos los cambios se reflejan inmediatamente en:
- Tabla de usuarios
- Tabla de auditoría
- Interfaz visual (actualización automática)

### 3. Registro Accesible Solo para Administradores
✅ **CUMPLIDO**: 
- Solo usuarios con rol "administrador" pueden acceder a gestión de usuarios
- Verificación en backend y frontend
- Tabla de auditoría solo consultable por administradores

### 4. Notificaciones Claras
✅ **CUMPLIDO**: Sistema completo de notificaciones:
- Alertas inline en modales
- Notificaciones toast
- Mensajes específicos para cada acción
- Iconos y colores según tipo de mensaje
- Confirmaciones antes de acciones críticas

### 5. Indicación de Éxito o Fallo
✅ **CUMPLIDO**: Cada operación muestra claramente:
- ✅ Éxito: Mensaje verde con icono de check
- ❌ Error: Mensaje rojo con icono de error
- ℹ️ Info: Mensaje azul con icono de información
- ⚠️ Advertencia: Mensaje amarillo con icono de advertencia

## 🎨 Personalización de Notificaciones

### Modificar Duración de Toast

En `componentes/gestion_usuarios.js`, línea ~380:

```javascript
// Cambiar de 5000ms (5 segundos) a otro valor
setTimeout(() => {
    notification.style.animation = 'slideOut 0.3s ease-out';
    setTimeout(() => notification.remove(), 300);
}, 5000); // <-- Cambiar este valor
```

### Modificar Colores

En `componentes/gestion_usuarios.js`, función `showNotification`:

```javascript
const colors = {
    success: '#4CAF50',  // Verde
    error: '#f44336',    // Rojo
    warning: '#ff9800',  // Naranja
    info: '#2196F3'      // Azul
};
```

### Agregar Nuevos Tipos de Notificación

```javascript
// En showNotification, agregar nuevo tipo
const icons = {
    success: 'fa-check-circle',
    error: 'fa-exclamation-circle',
    warning: 'fa-exclamation-triangle',
    info: 'fa-info-circle',
    custom: 'fa-star' // <-- Nuevo tipo
};

const colors = {
    success: '#4CAF50',
    error: '#f44336',
    warning: '#ff9800',
    info: '#2196F3',
    custom: '#9C27B0' // <-- Color para nuevo tipo
};
```

## 📱 Notificaciones Responsive

Las notificaciones están optimizadas para todos los dispositivos:

- **Desktop**: Esquina superior derecha, ancho máximo 400px
- **Tablet**: Se adapta al ancho disponible
- **Mobile**: Ancho completo menos márgenes, posición superior

## 🔐 Seguridad

### Validaciones Implementadas

1. **Doble confirmación para eliminación**: Previene eliminaciones accidentales
2. **Confirmación para cambios de estado**: Usuario debe confirmar cada cambio
3. **Confirmación para edición**: Previene cambios accidentales
4. **Validación de permisos**: Solo administradores pueden realizar acciones
5. **Registro completo**: Todas las acciones quedan registradas

### Protección contra Acciones Accidentales

- Eliminación requiere 2 confirmaciones
- Mensajes claros sobre consecuencias
- Recomendación de desactivar en lugar de eliminar
- No se puede eliminar la propia cuenta de administrador

## 📈 Mejores Prácticas

### Para Administradores

1. **Revisar auditoría regularmente**: Consultar registros semanalmente
2. **Usar desactivación en lugar de eliminación**: Mantener historial
3. **Documentar razones de suspensión**: En notas o sistema externo
4. **Verificar notificaciones**: Asegurarse de que las acciones se completaron

### Para Desarrolladores

1. **Mantener consistencia**: Usar siempre las funciones de notificación
2. **Registrar todas las acciones**: No omitir ninguna operación
3. **Mensajes descriptivos**: Incluir detalles relevantes
4. **Probar notificaciones**: Verificar en diferentes navegadores

## 🐛 Solución de Problemas

### Las notificaciones no aparecen

**Solución:**
1. Verificar que `gestion_usuarios.js` está cargando
2. Abrir consola del navegador (F12) y buscar errores
3. Verificar que la función `showNotification` está definida

### Los registros de auditoría no se guardan

**Solución:**
1. Verificar que la tabla `auditoria_usuarios` existe
2. Verificar permisos de base de datos
3. Revisar logs de PHP para errores

### Las notificaciones se superponen

**Solución:**
Las notificaciones están diseñadas para apilarse. Si se superponen incorrectamente:
1. Verificar que el CSS está cargando correctamente
2. Verificar z-index del contenedor (debe ser 10000)

---

**Última actualización:** Diciembre 2025  
**Versión:** 2.0  
**Estado:** ✅ Completado y probado
