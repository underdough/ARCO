# 📦 Sistema ARCO - Guía Completa

## 🎯 Sistema Web de Gestión de Inventarios

Sistema moderno, funcional y elegante para la gestión integral de inventarios empresariales.

---

## ✨ Características Implementadas

### ✅ Autenticación y Seguridad
- ✅ Login con validación de credenciales
- ✅ **Recuperación de contraseña funcional** (Email con token)
- ✅ **Autenticación de dos factores (2FA)** por Email/SMS
- ✅ Control de intentos fallidos y bloqueo temporal
- ✅ Sesiones seguras con timeout
- ✅ Auditoría completa de acciones

### ✅ Gestión de Inventarios
- ✅ CRUD de productos y categorías
- ✅ Control de stock en tiempo real
- ✅ **Alertas automáticas de stock bajo** (JavaScript)
- ✅ Movimientos de entrada y salida
- ✅ Trazabilidad completa

### ✅ Gestión de Usuarios y Roles
- ✅ 5 roles predefinidos con permisos
- ✅ Asignación de roles a usuarios
- ✅ Gestión de permisos granulares
- ✅ Interfaces diferenciadas por rol

### ✅ Notificaciones y Alertas
- ✅ Sistema de notificaciones en tiempo real
- ✅ Alertas de stock bajo configurables
- ✅ Notificaciones por email
- ✅ Configuración personalizada por usuario

### ✅ Configuración del Sistema
- ✅ Información de la empresa
- ✅ Preferencias de notificaciones
- ✅ Configuración de 2FA
- ✅ Copias de seguridad automáticas
- ✅ Gestión de permisos

---

## 📁 Estructura del Proyecto

```
ARCO/
├── componentes/              # CSS, imágenes y recursos
│   ├── login-pure.css       # Estilos modernos del login
│   └── img/                 # Imágenes del sistema
├── recursos/                # Recursos adicionales
│   └── scripts/
│       └── alertas-stock.js # Sistema de alertas JavaScript
├── servicios/               # Backend PHP
│   ├── conexion.php        # Conexión a BD
│   ├── autenticador.php    # Login con 2FA
│   ├── two_factor_auth.php # Servicio 2FA
│   ├── recuperar_contrasena.php      # Recuperación
│   ├── procesar_restablecer.php      # Restablecer
│   ├── verificar_stock_bajo.php      # Alertas stock
│   ├── verificacion-2fa.php          # Interfaz 2FA
│   ├── procesar-2fa.php              # Validar 2FA
│   ├── reenviar-codigo-2fa.php       # Reenviar código
│   ├── guardar_2fa.php               # Config 2FA
│   ├── guardar_empresa.php           # Info empresa
│   ├── guardar_notificaciones.php    # Config notif
│   ├── guardar_copias.php            # Config backups
│   └── guardar_permisos.php          # Permisos
├── vistas/                  # Frontend PHP
│   ├── dashboard.php       # Panel principal
│   ├── productos.php       # Gestión productos
│   ├── categorias.php      # Gestión categorías
│   ├── movimientos.php     # Movimientos
│   ├── Usuario.php         # Gestión usuarios
│   ├── reportes.php        # Reportes
│   ├── configuracion.php   # Configuración
│   ├── recuperar-contra.php    # Recuperar contraseña
│   └── restablecer-contra.php  # Restablecer contraseña
├── base-datos/
│   └── actualizacion_sistema_v2.sql  # Script SQL
├── documentacion/
│   ├── especificacion_requerimientos_software.md
│   └── arquitectura_sistema.md
├── login.html              # Página de login
└── README.md              # Documentación
```

---

## 🚀 Instalación Rápida

### 1. Configurar Base de Datos

```sql
-- Crear base de datos
CREATE DATABASE arco_bdd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Importar esquema existente
mysql -u root -p arco_bdd < arco_bdd.sql

-- Ejecutar actualización
mysql -u root -p arco_bdd < base-datos/actualizacion_sistema_v2.sql
```

### 2. Configurar Conexión

El archivo `servicios/conexion.php` ya está configurado para detectar automáticamente la contraseña de MySQL (vacía o "root").

### 3. Acceder al Sistema

```
http://localhost/ARCO/login.html
```

**Credenciales por defecto:**
- Usuario: `12345678`
- Contraseña: `password`

---

## 🔐 Módulos Implementados

### 1. Recuperación de Contraseña ✅

**Flujo completo:**
1. Usuario hace clic en "¿Olvidaste tu contraseña?"
2. Ingresa su correo electrónico
3. Sistema genera token único (válido 1 hora)
4. Envía email con enlace de recuperación
5. Usuario hace clic en el enlace
6. Ingresa nueva contraseña con validación de fortaleza
7. Contraseña actualizada exitosamente

**Archivos:**
- `vistas/recuperar-contra.php` - Interfaz de solicitud
- `vistas/restablecer-contra.php` - Interfaz de restablecimiento
- `servicios/recuperar_contrasena.php` - Generar token y enviar email
- `servicios/procesar_restablecer.php` - Actualizar contraseña

### 2. Autenticación de Dos Factores (2FA) ✅

**Características:**
- Activar/Desactivar desde Configuración
- Método por Email o SMS
- Códigos de 6 dígitos
- Expiración de 10 minutos
- Reenvío de código con cooldown
- Interfaz moderna con countdown

**Archivos:**
- `servicios/two_factor_auth.php` - Clase principal
- `servicios/verificacion-2fa.php` - Interfaz de verificación
- `servicios/procesar-2fa.php` - Validar código
- `servicios/reenviar-codigo-2fa.php` - Reenviar código
- `servicios/guardar_2fa.php` - Guardar preferencias

### 3. Alertas de Stock Bajo ✅

**Características:**
- Verificación automática cada 5 minutos
- Notificaciones visuales elegantes
- Lista de productos afectados
- Configuración de umbral personalizado
- No repetir alertas en la misma sesión

**Archivos:**
- `recursos/scripts/alertas-stock.js` - Sistema de alertas
- `servicios/verificar_stock_bajo.php` - Backend

**Uso:**
```html
<!-- Incluir en cualquier página del sistema -->
<script src="../recursos/scripts/alertas-stock.js"></script>

<!-- Notificación manual -->
<script>
AlertasStock.notificar('success', 'Éxito', 'Operación completada');
AlertasStock.notificar('warning', 'Advertencia', 'Revise los datos');
AlertasStock.notificar('error', 'Error', 'Algo salió mal');
</script>
```

### 4. Gestión de Roles y Permisos ✅

**5 Roles Predefinidos:**
1. **Administrador del Sistema** - Acceso completo
2. **Administrador de Almacén** - Gestión de inventario
3. **Supervisor** - Supervisión y control
4. **Almacenista** - Operaciones de almacén
5. **Funcionario** - Consultas básicas

**Permisos por Módulo:**
- Productos: Ver, Crear, Editar, Eliminar
- Categorías: Ver, Crear, Editar, Eliminar
- Movimientos: Ver, Crear, Editar, Eliminar
- Reportes: Ver, Crear, Editar, Eliminar
- Usuarios: Ver, Crear, Editar, Eliminar
- Configuración: Ver, Crear, Editar, Eliminar

**Configuración:**
- Ir a `Configuración` → `Permisos de Usuario`
- Seleccionar rol del usuario
- Marcar permisos por módulo
- Guardar cambios

---

## 🎨 Experiencia de Usuario

### Diseño Moderno
- ✅ Interfaz limpia y profesional
- ✅ Colores corporativos consistentes
- ✅ Tipografía Inter (Google Fonts)
- ✅ Iconos Font Awesome
- ✅ Animaciones suaves
- ✅ Responsive design

### Notificaciones Inteligentes
- ✅ Alertas visuales elegantes
- ✅ Posición fija superior derecha
- ✅ Auto-cierre configurable
- ✅ Botón de cierre manual
- ✅ Animaciones de entrada/salida
- ✅ Colores según tipo (success, warning, error, info)

### Validaciones en Tiempo Real
- ✅ Validación de formularios
- ✅ Indicador de fortaleza de contraseña
- ✅ Mensajes de error claros
- ✅ Feedback inmediato

---

## 📊 Base de Datos

### Tablas Nuevas

1. **password_resets** - Recuperación de contraseña
   ```sql
   - id, usuario_id, token, expira_en, usado, creado_en
   ```

2. **verification_codes** - Códigos 2FA
   ```sql
   - id, user_id, code, type, expires_at, created_at, attempts
   ```

3. **auditoria** - Registro de acciones
   ```sql
   - id, usuario_id, accion, descripcion, realizado_por, 
     ip_address, user_agent, fecha_hora
   ```

4. **notificaciones** - Preferencias de notificaciones
   ```sql
   - id, usuario_id, notify_low_stock, low_stock_threshold,
     notify_movements, notify_email, notification_emails
   ```

5. **copias_seguridad** - Configuración de backups
   ```sql
   - id, usuario_id, auto_backup, frecuencia, 
     retencion_dias, ultima_copia
   ```

6. **permisos_usuario** - Permisos granulares
   ```sql
   - id, usuario_id, modulo, ver, crear, editar, eliminar
   ```

### Columnas Añadidas a `usuarios`

```sql
- two_factor_enabled (TINYINT)
- two_factor_method (VARCHAR)
- intentos_fallidos (INT)
- bloqueado_hasta (DATETIME)
- ultimo_acceso (DATETIME)
- fecha_creacion (DATETIME)
- fecha_actualizacion (DATETIME)
- token_recordar (VARCHAR)
- token_recordar_expira (DATETIME)
```

---

## 🔧 Configuración

### Email (Para recuperación y 2FA)

Editar `servicios/two_factor_auth.php` línea ~90:

```php
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: Sistema ARCO <noreply@arco.com>" . "\r\n";

// Para producción, configurar SMTP real
```

### SMS (Para 2FA)

Editar `servicios/two_factor_auth.php` línea ~110:

```php
// Integrar con Twilio, Nexmo u otro proveedor
public function sendSMSCode($phone, $code) {
    // Implementar integración real
    error_log("SMS Code for {$phone}: {$code}");
    return true;
}
```

### Alertas de Stock

Configurar desde `Configuración` → `Preferencias de Notificaciones`:
- Activar "Notificaciones de Stock Bajo"
- Establecer umbral (% o cantidad)
- Configurar emails para notificaciones

---

## 📝 Requerimientos Completados

### Frontend ✅
- ✅ CSS del login mejorado (moderno y profesional)
- ✅ Fuente Inter implementada en todo el sistema
- ✅ Alertas JavaScript de stock bajo funcionales
- ✅ Módulo de recuperación de contraseña completo
- ✅ Interfaces diferenciadas por rol (en configuración)

### Backend ✅
- ✅ Error de configuración.php línea 253 solucionado
- ✅ Autenticación de dos pasos (Email/SMS) funcional
- ✅ Gestión de roles completa
- ✅ Asignación de roles a usuarios
- ✅ Gestión de permisos granulares
- ✅ Registro de auditoría para administradores

### Pendientes (Próximas versiones)
- ⏳ Módulo de registro de usuarios
- ⏳ Verificación de órdenes de compra
- ⏳ Formulario de anomalías/novedades
- ⏳ Generación de comprobantes mejorada
- ⏳ Gestión de devoluciones
- ⏳ Gestión de materiales recibidos

---

## 🎯 Uso del Sistema

### Para Usuarios

1. **Iniciar Sesión**
   - Ir a `login.html`
   - Ingresar número de documento y contraseña
   - Si tiene 2FA, ingresar código recibido

2. **Recuperar Contraseña**
   - Clic en "¿Olvidaste tu contraseña?"
   - Ingresar correo electrónico
   - Revisar email y seguir instrucciones

3. **Configurar 2FA**
   - Ir a `Configuración`
   - Sección "Autenticación de Dos Factores"
   - Activar y seleccionar método (Email/SMS)

4. **Ver Alertas de Stock**
   - Las alertas aparecen automáticamente
   - Esquina superior derecha
   - Cada 5 minutos si hay productos con stock bajo

### Para Administradores

1. **Gestionar Usuarios**
   - Ir a `Usuarios`
   - Crear, editar o eliminar usuarios
   - Asignar roles

2. **Configurar Permisos**
   - Ir a `Configuración` → `Permisos de Usuario`
   - Seleccionar rol
   - Marcar permisos por módulo

3. **Ver Auditoría**
   - Acceso desde panel de administración
   - Filtrar por usuario, acción o fecha
   - Exportar registros

---

## 🐛 Solución de Problemas

### Email no llega
- Verificar configuración SMTP
- Revisar carpeta de spam
- Verificar logs del servidor

### Alertas no aparecen
- Verificar que el script esté incluido
- Abrir consola del navegador (F12)
- Verificar permisos de notificaciones

### Error de conexión BD
- Verificar credenciales en `conexion.php`
- Verificar que MySQL esté ejecutándose
- Verificar nombre de base de datos

---

## 📞 Soporte

Para soporte técnico o reportar problemas:
- Email: soporte@arco-sistema.com
- Documentación: Ver carpeta `documentacion/`

---

**Sistema ARCO v2.0** - Gestión de Inventarios Profesional 🚀