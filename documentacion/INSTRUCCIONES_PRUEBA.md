# Instrucciones de Prueba - Sistema de Gestión de Usuarios

## 🚀 Inicio Rápido

### Paso 1: Ejecutar Script SQL

```bash
# Opción 1: Desde phpMyAdmin
1. Abrir phpMyAdmin
2. Seleccionar base de datos 'arco_bdd'
3. Ir a pestaña "SQL"
4. Copiar contenido de: base-datos/mejora_gestion_roles.sql
5. Ejecutar

# Opción 2: Desde línea de comandos
mysql -u root -p arco_bdd < base-datos/mejora_gestion_roles.sql
```

### Paso 2: Acceder al Sistema

```
URL: http://localhost/ARCO/vistas/gestion_usuarios.php
```

**Credenciales de prueba:**
- Usuario: admin@arco.com
- Contraseña: (tu contraseña de administrador)

### Paso 3: Probar Notificaciones Visuales

Abrir en navegador:
```
file:///C:/laragon/www/ARCO/documentacion/PRUEBA_NOTIFICACIONES.html
```

## 📋 Lista de Verificación de Pruebas

### ✅ Prueba 1: Crear Usuario

**Pasos:**
1. Hacer clic en "Nuevo Usuario"
2. Completar formulario:
   - Nombre: Juan
   - Apellido: Pérez
   - Documento: 1234567890
   - Email: juan.perez@test.com
   - Teléfono: 3001234567
   - Rol: Almacenista
   - Cargo: Almacén Principal
   - Contraseña: Test1234
   - Confirmar: Test1234
3. Hacer clic en "Crear Usuario"

**Resultado Esperado:**
- ✅ Alerta verde en modal: "Usuario creado exitosamente"
- ✅ Notificación toast: "Usuario 'Juan Pérez' creado exitosamente"
- ✅ Modal se cierra automáticamente
- ✅ Usuario aparece en la tabla
- ✅ Estadísticas se actualizan
- ✅ Registro en consola del navegador (F12)

**Verificar en Base de Datos:**
```sql
SELECT * FROM usuarios WHERE correo = 'juan.perez@test.com';
SELECT * FROM auditoria_usuarios WHERE accion = 'crear' ORDER BY fecha_accion DESC LIMIT 1;
```

---

### ✅ Prueba 2: Buscar Usuario

**Pasos:**
1. En el campo de búsqueda, escribir: "Juan"
2. Esperar 500ms (búsqueda automática)

**Resultado Esperado:**
- ✅ Tabla se filtra mostrando solo usuarios con "Juan"
- ✅ Estadísticas se actualizan según filtro
- ✅ Búsqueda funciona en tiempo real

**Probar también:**
- Buscar por apellido: "Pérez"
- Buscar por correo: "juan.perez"
- Buscar por documento: "1234567890"

---

### ✅ Prueba 3: Filtrar por Rol

**Pasos:**
1. Seleccionar "Almacenista" en filtro de Rol
2. Hacer clic en "Filtrar"

**Resultado Esperado:**
- ✅ Tabla muestra solo usuarios con rol Almacenista
- ✅ Estadísticas se actualizan
- ✅ Badge de rol visible en tabla

**Probar también:**
- Filtrar por "Administrador"
- Filtrar por "Usuario"
- Combinar búsqueda + filtro de rol

---

### ✅ Prueba 4: Filtrar por Estado

**Pasos:**
1. Seleccionar "Activo" en filtro de Estado
2. Hacer clic en "Filtrar"

**Resultado Esperado:**
- ✅ Tabla muestra solo usuarios activos
- ✅ Badge de estado visible (verde para ACTIVO)
- ✅ Estadísticas correctas

---

### ✅ Prueba 5: Editar Usuario

**Pasos:**
1. Localizar usuario "Juan Pérez"
2. Hacer clic en botón "Editar" (icono de lápiz)
3. Modificar:
   - Cargo: "Almacén Secundario"
   - Rol: "Supervisor"
4. Hacer clic en "Guardar Cambios"
5. Confirmar en diálogo

**Resultado Esperado:**
- ✅ Confirmación: "¿Está seguro de actualizar...?"
- ✅ Alerta verde en modal: "Usuario actualizado correctamente"
- ✅ Notificación toast: "Usuario 'Juan Pérez' actualizado correctamente"
- ✅ Modal se cierra automáticamente
- ✅ Tabla muestra cambios (nuevo rol y cargo)
- ✅ Registro en consola con número de cambios
- ✅ Registro en auditoría

**Verificar en Base de Datos:**
```sql
SELECT * FROM usuarios WHERE correo = 'juan.perez@test.com';
SELECT * FROM auditoria_usuarios WHERE usuario_id = (SELECT id_usuarios FROM usuarios WHERE correo = 'juan.perez@test.com') ORDER BY fecha_accion DESC;
```

---

### ✅ Prueba 6: Cambiar Estado (Desactivar)

**Pasos:**
1. Localizar usuario "Juan Pérez"
2. Hacer clic en botón "Cambiar Estado" (icono de toggle)
3. Leer confirmación
4. Confirmar

**Resultado Esperado:**
- ✅ Confirmación específica: "¿Está seguro de DESACTIVAR al usuario 'Juan Pérez'? El usuario no podrá acceder al sistema..."
- ✅ Notificación toast: "Usuario 'Juan Pérez' desactivado correctamente"
- ✅ Badge cambia a rojo (INACTIVO)
- ✅ Estadísticas se actualizan (activos -1, inactivos +1)
- ✅ Registro en auditoría

**Probar ciclo completo:**
1. ACTIVO → INACTIVO (desactivar)
2. INACTIVO → SUSPENDIDO (suspender)
3. SUSPENDIDO → ACTIVO (activar)

**Verificar mensajes específicos para cada estado**

---

### ✅ Prueba 7: Eliminar Usuario (Doble Confirmación)

**Pasos:**
1. Localizar usuario "Juan Pérez"
2. Hacer clic en botón "Eliminar" (icono de papelera)
3. Leer primera advertencia
4. Confirmar primera vez
5. Leer segunda confirmación
6. Confirmar segunda vez

**Resultado Esperado:**
- ✅ Primera confirmación: "⚠️ ADVERTENCIA: ELIMINACIÓN PERMANENTE..."
- ✅ Segunda confirmación: "CONFIRMACIÓN FINAL..."
- ✅ Notificación toast: "Usuario 'Juan Pérez' eliminado del sistema"
- ✅ Usuario desaparece de la tabla
- ✅ Estadísticas se actualizan
- ✅ Registro en auditoría

**Verificar en Base de Datos:**
```sql
-- Usuario no debe existir
SELECT * FROM usuarios WHERE correo = 'juan.perez@test.com';

-- Debe existir registro de eliminación
SELECT * FROM auditoria_usuarios WHERE accion = 'eliminar' ORDER BY fecha_accion DESC LIMIT 1;
```

**Probar cancelación:**
1. Intentar eliminar otro usuario
2. Cancelar en primera confirmación
3. Verificar notificación: "Eliminación cancelada"
4. Usuario debe seguir en tabla

---

### ✅ Prueba 8: Validaciones de Formulario

#### Crear Usuario - Validaciones

**Prueba 8.1: Contraseñas no coinciden**
1. Abrir modal "Nuevo Usuario"
2. Completar formulario
3. Contraseña: "Test1234"
4. Confirmar: "Test5678" (diferente)
5. Intentar crear

**Resultado Esperado:**
- ❌ Alerta roja: "Las contraseñas no coinciden"
- ❌ Notificación toast de error
- ❌ Usuario no se crea

**Prueba 8.2: Documento duplicado**
1. Intentar crear usuario con documento existente
2. Completar formulario con documento: 1234567890 (ya existe)

**Resultado Esperado:**
- ❌ Alerta roja: "El número de documento ya está registrado"
- ❌ Usuario no se crea

**Prueba 8.3: Email duplicado**
1. Intentar crear usuario con email existente

**Resultado Esperado:**
- ❌ Alerta roja: "El correo electrónico ya está registrado"
- ❌ Usuario no se crea

**Prueba 8.4: Campos obligatorios**
1. Intentar crear usuario sin completar campos obligatorios

**Resultado Esperado:**
- ❌ Validación HTML5 impide envío
- ❌ Campos requeridos marcados en rojo

---

### ✅ Prueba 9: Auditoría Completa

**Pasos:**
1. Realizar varias acciones:
   - Crear 2 usuarios
   - Editar 1 usuario
   - Cambiar estado de 1 usuario
   - Eliminar 1 usuario
2. Abrir consola del navegador (F12)
3. Revisar registros de auditoría

**Resultado Esperado en Consola:**
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

**Verificar en Base de Datos:**
```sql
-- Ver todas las acciones
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
LIMIT 20;
```

---

### ✅ Prueba 10: Notificaciones Múltiples

**Pasos:**
1. Crear usuario rápidamente
2. Inmediatamente editar otro usuario
3. Cambiar estado de otro usuario

**Resultado Esperado:**
- ✅ Múltiples notificaciones toast se apilan verticalmente
- ✅ Cada una se auto-cierra después de 5 segundos
- ✅ No se superponen
- ✅ Animaciones suaves

---

### ✅ Prueba 11: Responsive Design

**Pasos:**
1. Abrir DevTools (F12)
2. Activar modo responsive
3. Probar en diferentes tamaños:
   - Desktop (1920x1080)
   - Tablet (768x1024)
   - Mobile (375x667)

**Resultado Esperado:**
- ✅ Tabla se adapta al ancho
- ✅ Filtros se reorganizan en móvil
- ✅ Modales se ajustan al tamaño
- ✅ Notificaciones se adaptan
- ✅ Botones accesibles en todos los tamaños

---

### ✅ Prueba 12: Seguridad

**Prueba 12.1: Acceso sin sesión**
1. Cerrar sesión
2. Intentar acceder directamente a: `/vistas/gestion_usuarios.php`

**Resultado Esperado:**
- ❌ Redirección a login
- ❌ Mensaje: "Debe iniciar sesión"

**Prueba 12.2: Acceso sin permisos**
1. Iniciar sesión con usuario no administrador
2. Intentar acceder a gestión de usuarios

**Resultado Esperado:**
- ❌ Redirección a dashboard
- ❌ Mensaje: "No tiene permisos"

**Prueba 12.3: No eliminar propia cuenta**
1. Iniciar sesión como administrador
2. Intentar eliminar tu propia cuenta

**Resultado Esperado:**
- ❌ Error: "No puede eliminar su propia cuenta"

---

## 🎯 Checklist Final

Marcar cada item después de probarlo:

### Funcionalidades Básicas
- [ ] Crear usuario
- [ ] Editar usuario
- [ ] Cambiar estado (ACTIVO → INACTIVO → SUSPENDIDO)
- [ ] Eliminar usuario
- [ ] Buscar usuario
- [ ] Filtrar por rol
- [ ] Filtrar por estado

### Notificaciones
- [ ] Notificación de creación exitosa
- [ ] Notificación de edición exitosa
- [ ] Notificación de cambio de estado
- [ ] Notificación de eliminación
- [ ] Notificaciones de error
- [ ] Múltiples notificaciones simultáneas
- [ ] Auto-cierre de notificaciones
- [ ] Cierre manual de notificaciones

### Confirmaciones
- [ ] Confirmación antes de editar
- [ ] Confirmación antes de cambiar estado
- [ ] Doble confirmación antes de eliminar
- [ ] Mensajes específicos según acción

### Validaciones
- [ ] Contraseñas coinciden
- [ ] Documento único
- [ ] Email único
- [ ] Campos obligatorios
- [ ] Formato de email
- [ ] Longitud de contraseña

### Auditoría
- [ ] Registro en base de datos
- [ ] Registro en consola del navegador
- [ ] Información completa (fecha, hora, usuario, acción)
- [ ] Registro de cambios específicos en edición

### UI/UX
- [ ] Animaciones suaves
- [ ] Responsive design
- [ ] Iconos apropiados
- [ ] Colores consistentes
- [ ] Estados de carga
- [ ] Cierre con ESC

### Seguridad
- [ ] Verificación de sesión
- [ ] Verificación de permisos
- [ ] No eliminar propia cuenta
- [ ] Protección contra duplicados

---

## 📊 Resultados Esperados

Al completar todas las pruebas, deberías tener:

1. ✅ Al menos 3 usuarios creados
2. ✅ Al menos 5 registros en tabla de auditoría
3. ✅ Usuarios con diferentes roles y estados
4. ✅ Registros de auditoría en consola del navegador
5. ✅ Todas las notificaciones funcionando correctamente

---

## 🐛 Reporte de Problemas

Si encuentras algún problema, documentar:

1. **Descripción del problema**
2. **Pasos para reproducir**
3. **Resultado esperado**
4. **Resultado actual**
5. **Capturas de pantalla**
6. **Errores en consola** (F12)
7. **Navegador y versión**

---

## 📞 Soporte

Para dudas o problemas:
- Revisar: `documentacion/GUIA_GESTION_USUARIOS.md`
- Revisar: `documentacion/SISTEMA_NOTIFICACIONES_AUDITORIA.md`
- Consultar logs de PHP
- Revisar consola del navegador (F12)

---

**Última actualización:** Diciembre 2025  
**Versión:** 2.0  
**Estado:** ✅ Listo para pruebas
