# 🚀 Guía de Acceso Rápido - Sistema ARCO

## 📍 Enlaces de Acceso a Funcionalidades

### 🔐 Autenticación

| Funcionalidad | URL | Descripción |
|---------------|-----|-------------|
| **Login** | `http://localhost/ARCO/login.html` | Inicio de sesión |
| **Recuperar Contraseña** | `http://localhost/ARCO/vistas/recuperar-contra.html` | Recuperación de contraseña |
| **2FA** | `http://localhost/ARCO/vistas/two-factor-auth.html` | Verificación de dos factores |

---

### 🏠 Panel Principal

| Funcionalidad | URL | Rol Requerido |
|---------------|-----|---------------|
| **Dashboard** | `http://localhost/ARCO/vistas/dashboard.php` | Todos |

---

### 📦 Gestión de Inventario

| Funcionalidad | URL | Rol Requerido |
|---------------|-----|---------------|
| **Productos** | `http://localhost/ARCO/vistas/productos.php` | Todos (permisos según rol) |
| **Categorías** | `http://localhost/ARCO/vistas/categorias.php` | Todos (permisos según rol) |
| **Movimientos** | `http://localhost/ARCO/vistas/movimientos.php` | Todos (permisos según rol) |

---

### 👥 Gestión de Usuarios

| Funcionalidad | URL | Rol Requerido |
|---------------|-----|---------------|
| **Gestión de Usuarios** | `http://localhost/ARCO/vistas/gestion_usuarios.php` | Administrador |
| **Gestión de Permisos** | `http://localhost/ARCO/vistas/gestion_permisos.php` | Administrador |
| **Productos Protegido** | `http://localhost/ARCO/vistas/productos_protegido.php` | Según permisos |
| **Categorías Protegido** | `http://localhost/ARCO/vistas/categorias_protegido.php` | Según permisos |

**Características:**
- ✅ Crear, editar, desactivar, eliminar usuarios
- ✅ Búsqueda en tiempo real
- ✅ Filtros por rol y estado
- ✅ Notificaciones avanzadas
- ✅ Auditoría completa
- ✅ **Visualización de permisos por rol**
- ✅ **Matriz de permisos interactiva**

---

### 📊 Reportes

| Funcionalidad | URL | Rol Requerido |
|---------------|-----|---------------|
| **Reportes** | `http://localhost/ARCO/vistas/reportes.php` | Todos (permisos según rol) |

---

### ⚙️ Configuración

| Funcionalidad | URL | Rol Requerido |
|---------------|-----|---------------|
| **Configuración** | `http://localhost/ARCO/vistas/configuracion.php` | Administrador, Gerente |

---

### 🛠️ Herramientas y Utilidades

| Funcionalidad | URL | Descripción |
|---------------|-----|-------------|
| **Actualizar Enlaces** | `http://localhost/ARCO/actualizar_enlaces_usuarios.php` | Script de actualización |
| **Resumen Visual** | `http://localhost/ARCO/RESUMEN_VISUAL.html` | Resumen del sistema |
| **Demo Notificaciones** | `http://localhost/ARCO/documentacion/PRUEBA_NOTIFICACIONES.html` | Prueba de notificaciones |

---

### 📚 Documentación

| Documento | Ubicación | Descripción |
|-----------|-----------|-------------|
| **README Principal** | `README_GESTION_USUARIOS.md` | Guía principal |
| **Guía de Usuario** | `documentacion/GUIA_GESTION_USUARIOS.md` | Para administradores |
| **Instalación** | `documentacion/INSTALACION_GESTION_USUARIOS.md` | Instrucciones de instalación |
| **Sistema de Permisos** | `documentacion/SISTEMA_PERMISOS.md` | Guía de permisos |
| **Notificaciones** | `documentacion/SISTEMA_NOTIFICACIONES_AUDITORIA.md` | Sistema de notificaciones |
| **Pruebas** | `documentacion/INSTRUCCIONES_PRUEBA.md` | Lista de pruebas |

---

## 🎯 Acceso Rápido por Funcionalidad

### Para Administradores

```
1. Login: http://localhost/ARCO/login.html
2. Dashboard: http://localhost/ARCO/vistas/dashboard.php
3. Gestión de Usuarios: http://localhost/ARCO/vistas/gestion_usuarios.php
4. Gestión de Permisos: http://localhost/ARCO/vistas/gestion_permisos.php
5. Configuración: http://localhost/ARCO/vistas/configuracion.php
```

### Para Gerentes

```
1. Login: http://localhost/ARCO/login.html
2. Dashboard: http://localhost/ARCO/vistas/dashboard.php
3. Productos: http://localhost/ARCO/vistas/productos.php
4. Reportes: http://localhost/ARCO/vistas/reportes.php
```

### Para Almacenistas

```
1. Login: http://localhost/ARCO/login.html
2. Dashboard: http://localhost/ARCO/vistas/dashboard.php
3. Productos: http://localhost/ARCO/vistas/productos.php
4. Movimientos: http://localhost/ARCO/vistas/movimientos.php
```

### Para Supervisores

```
1. Login: http://localhost/ARCO/login.html
2. Dashboard: http://localhost/ARCO/vistas/dashboard.php
3. Movimientos: http://localhost/ARCO/vistas/movimientos.php
4. Reportes: http://localhost/ARCO/vistas/reportes.php
```

### Para Usuarios

```
1. Login: http://localhost/ARCO/login.html
2. Dashboard: http://localhost/ARCO/vistas/dashboard.php
3. Productos: http://localhost/ARCO/vistas/productos.php (solo lectura)
```

---

## 🔧 APIs y Servicios

### Servicios de Usuarios

| Servicio | URL | Método | Descripción |
|----------|-----|--------|-------------|
| Listar Usuarios | `servicios/listar_usuarios_mejorado.php` | GET | Lista con filtros |
| Crear Usuario | `servicios/registro_mejorado.php` | POST | Crear nuevo usuario |
| Editar Usuario | `servicios/actualizar_usuario_mejorado.php` | POST | Actualizar usuario |
| Cambiar Estado | `servicios/cambiar_estado_usuario.php` | POST | Activar/Desactivar |
| Eliminar Usuario | `servicios/eliminar_usuario_mejorado.php` | POST | Eliminar usuario |

### Servicios de Permisos

| Servicio | URL | Método | Descripción |
|----------|-----|--------|-------------|
| Verificar Permisos | `servicios/verificar_permisos.php` | - | Funciones PHP |
| Obtener Permisos | `servicios/obtener_permisos_usuario.php` | GET | API JSON |
| Obtener Permisos Rol | `servicios/obtener_permisos_rol.php` | GET | Permisos por rol |
| Middleware Permisos | `servicios/middleware_permisos.php` | - | Protección de vistas |

---

## 📋 Checklist de Primer Acceso

### Paso 1: Instalación Base de Datos

```bash
# Gestión de Usuarios
mysql -u root -p arco_bdd < base-datos/mejora_gestion_roles.sql

# Sistema de Permisos
mysql -u root -p arco_bdd < base-datos/sistema_permisos_completo.sql
```

### Paso 2: Actualizar Enlaces

```
Acceder a: http://localhost/ARCO/actualizar_enlaces_usuarios.php
```

### Paso 3: Iniciar Sesión

```
URL: http://localhost/ARCO/login.html
Usuario: admin@arco.com (o tu usuario administrador)
Contraseña: (tu contraseña)
```

### Paso 4: Verificar Funcionalidades

1. ✅ Dashboard carga correctamente
2. ✅ Menú lateral muestra todos los módulos
3. ✅ Acceder a Gestión de Usuarios
4. ✅ Probar crear un usuario de prueba
5. ✅ Verificar notificaciones

---

## 🎨 Características Nuevas Implementadas

### 1. Gestión Avanzada de Usuarios
**URL:** `http://localhost/ARCO/vistas/gestion_usuarios.php`

**Funcionalidades:**
- ✅ Crear usuarios con validaciones completas
- ✅ Editar información de usuarios
- ✅ Cambiar estado (Activo/Inactivo/Suspendido)
- ✅ Eliminar usuarios con doble confirmación
- ✅ Búsqueda en tiempo real
- ✅ Filtros por rol y estado
- ✅ Dashboard con estadísticas
- ✅ Notificaciones toast
- ✅ Auditoría completa

### 2. Sistema de Permisos Granulares
**Implementación:** Backend (PHP)

**Funcionalidades:**
- ✅ 10 módulos del sistema
- ✅ 8 tipos de permisos
- ✅ 5 roles con permisos específicos
- ✅ API PHP completa
- ✅ Middleware de protección
- ✅ Auditoría de cambios

### 3. Sistema de Notificaciones
**Implementación:** JavaScript + PHP

**Tipos:**
- ✅ Notificaciones toast (esquina superior derecha)
- ✅ Alertas inline (dentro de modales)
- ✅ 4 tipos: Success, Error, Warning, Info
- ✅ Auto-cierre y cierre manual
- ✅ Animaciones suaves

---

## 🔍 Verificación Rápida

### Verificar Gestión de Usuarios

```
1. Acceder a: http://localhost/ARCO/vistas/gestion_usuarios.php
2. Verificar que se muestran estadísticas
3. Hacer clic en "Nuevo Usuario"
4. Completar formulario
5. Verificar notificación de éxito
6. Usuario debe aparecer en la tabla
```

### Verificar Sistema de Permisos

```sql
-- En MySQL
USE arco_bdd;

-- Ver módulos
SELECT * FROM modulos ORDER BY orden;

-- Ver permisos de administrador
SELECT 
    m.nombre AS modulo,
    p.nombre AS permiso
FROM rol_permisos rp
JOIN modulos m ON rp.id_modulo = m.id_modulo
JOIN permisos p ON rp.id_permiso = p.id_permiso
WHERE rp.rol = 'administrador'
ORDER BY m.orden;
```

### Verificar Notificaciones

```
1. Acceder a: http://localhost/ARCO/documentacion/PRUEBA_NOTIFICACIONES.html
2. Hacer clic en los botones de prueba
3. Verificar que aparecen notificaciones
4. Verificar animaciones
```

---

## 📱 Acceso desde Diferentes Dispositivos

### Desktop
```
http://localhost/ARCO/vistas/gestion_usuarios.php
```

### Tablet/Mobile
```
http://localhost/ARCO/vistas/gestion_usuarios.php
(Interfaz responsive se adapta automáticamente)
```

### Red Local
```
http://[IP-DEL-SERVIDOR]/ARCO/vistas/gestion_usuarios.php
Ejemplo: http://192.168.1.100/ARCO/vistas/gestion_usuarios.php
```

---

## 🆘 Solución de Problemas

### No puedo acceder a Gestión de Usuarios

**Solución:**
1. Verificar que tienes rol "administrador"
2. Verificar sesión activa
3. Limpiar caché del navegador
4. Verificar que el archivo existe: `vistas/gestion_usuarios.php`

### Notificaciones no aparecen

**Solución:**
1. Abrir consola del navegador (F12)
2. Buscar errores en JavaScript
3. Verificar que `gestion_usuarios.js` carga
4. Verificar ruta del archivo

### Error al crear usuario

**Solución:**
1. Verificar que ejecutaste `mejora_gestion_roles.sql`
2. Verificar que tabla `auditoria_usuarios` existe
3. Verificar permisos de base de datos

---

## 📞 Soporte

### Documentación Completa
- `README_GESTION_USUARIOS.md` - Guía principal
- `documentacion/` - Carpeta con toda la documentación
- `ejemplos/` - Ejemplos de uso

### Archivos de Ayuda
- `RESUMEN_VISUAL.html` - Resumen interactivo
- `CHECKLIST_IMPLEMENTACION.md` - Lista de verificación
- `IMPLEMENTACION_COMPLETA.md` - Detalles técnicos

---

## 🎉 ¡Listo para Usar!

**Acceso Principal:**
```
http://localhost/ARCO/login.html
```

**Después de iniciar sesión:**
```
http://localhost/ARCO/vistas/gestion_usuarios.php
```

**Demo de Notificaciones:**
```
http://localhost/ARCO/documentacion/PRUEBA_NOTIFICACIONES.html
```

**Resumen Visual:**
```
http://localhost/ARCO/RESUMEN_VISUAL.html
```

---

**Última actualización:** Diciembre 16, 2025  
**Versión:** 2.0  
**Estado:** ✅ Completado y funcional
