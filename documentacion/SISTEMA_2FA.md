# 🔐 Sistema de Autenticación de Dos Factores (2FA)

## Sistema ARCO - Documentación Completa

---

## 📋 Índice

1. [Descripción General](#descripción-general)
2. [Características](#características)
3. [Arquitectura](#arquitectura)
4. [Instalación y Configuración](#instalación-y-configuración)
5. [Uso del Sistema](#uso-del-sistema)
6. [Flujo de Autenticación](#flujo-de-autenticación)
7. [Configuración para Usuarios](#configuración-para-usuarios)
8. [Administración](#administración)
9. [Seguridad](#seguridad)
10. [Troubleshooting](#troubleshooting)

---

## 📖 Descripción General

El Sistema ARCO implementa autenticación de dos factores (2FA) para proporcionar una capa adicional de seguridad al proceso de inicio de sesión. Los usuarios pueden elegir recibir códigos de verificación por:

- **📧 Email** (Predeterminado)
- **📱 SMS** (Requiere configuración adicional)

### Beneficios

- ✅ Mayor seguridad en el acceso al sistema
- ✅ Protección contra accesos no autorizados
- ✅ Cumplimiento de estándares de seguridad
- ✅ Auditoría completa de accesos
- ✅ Flexibilidad en métodos de verificación

---

## ✨ Características

### Funcionalidades Principales

1. **Verificación por Email**
   - Emails HTML profesionales
   - Códigos de 6 dígitos
   - Expiración de 10 minutos
   - Envío con PHPMailer

2. **Verificación por SMS**
   - Soporte para servicios SMS (Twilio, etc.)
   - Códigos de 6 dígitos
   - Expiración de 10 minutos

3. **Interfaz de Usuario**
   - Diseño moderno y responsive
   - Entrada de código intuitiva
   - Auto-avance entre dígitos
   - Soporte para pegar código completo
   - Temporizador de expiración visible
   - Opción de reenvío de código

4. **Seguridad**
   - Códigos de un solo uso
   - Expiración automática
   - Límite de intentos
   - Bloqueo temporal por intentos fallidos
   - Auditoría completa

5. **Administración**
   - Habilitar/deshabilitar 2FA por usuario
   - Elegir método de verificación
   - Configuración desde panel de usuario
   - Logs de auditoría

---

## 🏗️ Arquitectura

### Componentes del Sistema

```
Sistema 2FA
├── Backend (PHP)
│   ├── two_factor_auth.php          # Clase principal de 2FA
│   ├── autenticador.php              # Integración con login
│   ├── verificacion-2fa.php          # Página de verificación
│   ├── procesar-2fa.php              # Procesador de códigos
│   ├── reenviar-codigo-2fa.php       # Reenvío de códigos
│   ├── guardar_2fa.php               # Guardar preferencias
│   └── email_sender.php              # Envío de emails
│
├── Base de Datos
│   ├── verification_codes            # Tabla de códigos
│   ├── usuarios (columnas 2FA)       # Preferencias de usuario
│   └── auditoria                     # Registro de eventos
│
└── Frontend
    ├── Interfaz de verificación      # HTML/CSS/JS
    └── Panel de configuración        # Configuración de usuario
```

### Base de Datos

#### Tabla: `verification_codes`

```sql
CREATE TABLE `verification_codes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `code` VARCHAR(6) NOT NULL,
  `type` VARCHAR(10) DEFAULT 'email',
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `attempts` INT DEFAULT 0,
  INDEX `idx_user_code` (`user_id`, `code`),
  INDEX `idx_expires` (`expires_at`),
  FOREIGN KEY (`user_id`) REFERENCES `usuarios`(`id_usuarios`) ON DELETE CASCADE
);
```

#### Columnas en `usuarios`

```sql
ALTER TABLE `usuarios` ADD COLUMN:
- `two_factor_enabled` TINYINT(1) DEFAULT 0
- `two_factor_method` VARCHAR(10) DEFAULT 'email'
- `intentos_fallidos` INT DEFAULT 0
- `bloqueado_hasta` DATETIME NULL
- `token_recordar` VARCHAR(64) NULL
- `token_recordar_expira` DATETIME NULL
- `ultimo_acceso` DATETIME NULL
```

---

## 🚀 Instalación y Configuración

### Paso 1: Verificar Requisitos

```bash
# PHP 8.0+
php -v

# PHPMailer instalado
composer require phpmailer/phpmailer
```

### Paso 2: Configurar Base de Datos

**Opción A: Script Automático**

Abrir en el navegador:
```
http://localhost/ARCO/ARCO/servicios/verificar_2fa_setup.php
```

Este script:
- ✅ Verifica y crea tablas necesarias
- ✅ Agrega columnas a usuarios
- ✅ Crea índices de optimización
- ✅ Verifica archivos del sistema
- ✅ Verifica PHPMailer

**Opción B: Manual**

```bash
mysql -u root -p arco_bdd < base-datos/crear_tabla_verification_codes.sql
```

### Paso 3: Configurar Email

Editar `servicios/config_email.php`:

```php
const MODO = 'produccion';
const SMTP_PROVIDER = 'gmail';
const SMTP_USERNAME = 'tu_email@gmail.com';
const SMTP_PASSWORD = 'tu_contraseña_app';
```

### Paso 4: Probar Configuración

```
http://localhost/ARCO/ARCO/servicios/test_email.php
```

---

## 💻 Uso del Sistema

### Para Usuarios

#### 1. Habilitar 2FA

1. Iniciar sesión en el sistema
2. Ir a **Configuración** → **Seguridad**
3. Activar "Autenticación de Dos Factores"
4. Seleccionar método (Email o SMS)
5. Guardar cambios

#### 2. Iniciar Sesión con 2FA

1. Ingresar usuario y contraseña en el login
2. Si las credenciales son correctas, se redirige a verificación 2FA
3. Ingresar el código de 6 dígitos recibido
4. Acceso concedido al sistema

#### 3. Reenviar Código

Si no recibes el código:
1. Esperar 60 segundos
2. Hacer clic en "Reenviar código"
3. Verificar carpeta de spam (email)

### Para Administradores

#### Habilitar 2FA para Usuarios

1. Ir a **Usuarios** → **Gestión de Usuarios**
2. Editar usuario
3. Activar "Requiere 2FA"
4. Seleccionar método predeterminado
5. Guardar cambios

---

## 🔄 Flujo de Autenticación

### Diagrama de Flujo

```
Usuario ingresa credenciales
         ↓
¿Credenciales válidas?
    ↓ No → Error y volver al login
    ↓ Sí
¿Tiene 2FA habilitado?
    ↓ No → Login exitoso → Dashboard
    ↓ Sí
Generar código de 6 dígitos
         ↓
Guardar en base de datos (expira en 10 min)
         ↓
Enviar código por email/SMS
         ↓
Mostrar página de verificación
         ↓
Usuario ingresa código
         ↓
¿Código válido?
    ↓ No → Error y permitir reintentar
    ↓ Sí
Login exitoso → Dashboard
```

### Proceso Detallado

#### 1. Login Inicial (`autenticador.php`)

```php
// Verificar credenciales
if (password_verify($contrasena, $hashBD)) {
    // Limpiar intentos fallidos
    
    // Verificar si tiene 2FA
    if ($usuario['two_factor_enabled']) {
        // Guardar datos temporales
        $_SESSION['temp_user_id'] = $usuario['id_usuarios'];
        $_SESSION['temp_user_data'] = $usuario;
        
        // Generar y enviar código
        $tfa = new TwoFactorAuth();
        $codigo = $tfa->generateVerificationCode();
        $tfa->saveVerificationCode($userId, $codigo, $metodo);
        
        if ($metodo === 'email') {
            $tfa->sendEmailCode($email, $codigo, $nombre);
        } else {
            $tfa->sendSMSCode($telefono, $codigo);
        }
        
        // Redirigir a verificación
        header("Location: verificacion-2fa.php");
    } else {
        // Login directo sin 2FA
        $_SESSION['usuario_id'] = $usuario['id_usuarios'];
        header("Location: ../vistas/dashboard.php");
    }
}
```

#### 2. Verificación de Código (`procesar-2fa.php`)

```php
// Verificar código
$tfa = new TwoFactorAuth();

if ($tfa->verifyCode($userId, $codigo)) {
    // Código válido - completar login
    $_SESSION['usuario_id'] = $usuario['id_usuarios'];
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['rol'] = $usuario['rol'];
    
    // Limpiar datos temporales
    unset($_SESSION['temp_user_id']);
    unset($_SESSION['temp_user_data']);
    
    // Registrar en auditoría
    // Redirigir al dashboard
    header("Location: ../vistas/dashboard.php");
} else {
    // Código inválido
    header("Location: verificacion-2fa.php?error=Código incorrecto");
}
```

---

## ⚙️ Configuración para Usuarios

### Panel de Configuración

Ubicación: `vistas/configuracion.php` → Pestaña "Seguridad"

#### Opciones Disponibles

1. **Habilitar/Deshabilitar 2FA**
   - Switch on/off
   - Efecto inmediato en próximo login

2. **Método de Verificación**
   - 📧 Email (predeterminado)
   - 📱 SMS (requiere número de teléfono)

3. **Información Mostrada**
   - Estado actual de 2FA
   - Método configurado
   - Último acceso
   - Dispositivos recordados

### Código de Configuración

```php
<div class="form-group">
    <label>Autenticación de Dos Factores (2FA)</label>
    <label class="switch">
        <input type="checkbox" id="enable2FA" name="enable2FA" value="1" 
            <?= $preferencias2FA['two_factor_enabled'] ? 'checked' : '' ?>>
        <span class="slider"></span>
    </label>
</div>

<div class="form-group" id="method2FAGroup">
    <label for="method2FA">Método de Verificación</label>
    <select class="form-control" id="method2FA" name="method2FA">
        <option value="email" <?= $preferencias2FA['two_factor_method'] == 'email' ? 'selected' : '' ?>>
            📧 Correo Electrónico
        </option>
        <option value="sms" <?= $preferencias2FA['two_factor_method'] == 'sms' ? 'selected' : '' ?>>
            📱 Mensaje SMS
        </option>
    </select>
</div>
```

---

## 👨‍💼 Administración

### Gestión de 2FA para Usuarios

Los administradores pueden:

1. **Habilitar 2FA obligatorio** para ciertos roles
2. **Deshabilitar 2FA** temporalmente para un usuario
3. **Cambiar método** de verificación
4. **Ver logs** de autenticación
5. **Resetear intentos** fallidos

### Auditoría

Todas las acciones de 2FA se registran en la tabla `auditoria`:

```sql
SELECT * FROM auditoria 
WHERE accion IN ('login_2fa', 'habilitar_2fa', 'deshabilitar_2fa')
ORDER BY fecha_hora DESC;
```

Información registrada:
- Usuario que realizó la acción
- Tipo de acción
- Descripción detallada
- IP address
- User agent
- Fecha y hora

---

## 🔒 Seguridad

### Medidas Implementadas

1. **Códigos de Un Solo Uso**
   - Cada código solo puede usarse una vez
   - Se elimina de la BD después de usarse

2. **Expiración Temporal**
   - Códigos expiran en 10 minutos
   - Limpieza automática de códigos expirados

3. **Límite de Intentos**
   - Máximo 5 intentos fallidos
   - Bloqueo temporal de 15 minutos

4. **Protección contra Fuerza Bruta**
   - Códigos de 6 dígitos (1,000,000 combinaciones)
   - Expiración rápida
   - Límite de intentos

5. **Sesiones Seguras**
   - Datos temporales en sesión
   - Limpieza automática después de verificación
   - Tokens seguros para "recordarme"

6. **Auditoría Completa**
   - Registro de todos los intentos
   - IP y user agent registrados
   - Alertas de actividad sospechosa

### Mejores Prácticas

1. **Para Usuarios:**
   - No compartir códigos de verificación
   - Verificar remitente de emails
   - Reportar emails sospechosos
   - Mantener email/teléfono actualizados

2. **Para Administradores:**
   - Revisar logs regularmente
   - Habilitar 2FA para roles críticos
   - Configurar alertas de seguridad
   - Mantener sistema actualizado

---

## 🔧 Troubleshooting

### Problema: No llega el código por email

**Causas posibles:**
- PHPMailer no configurado
- Credenciales SMTP incorrectas
- Email en carpeta de spam
- Servidor SMTP bloqueado

**Soluciones:**
1. Verificar configuración en `test_email.php`
2. Revisar logs del servidor
3. Verificar carpeta de spam
4. Probar con otro proveedor SMTP

### Problema: Código expirado

**Causa:** Han pasado más de 10 minutos

**Solución:**
1. Hacer clic en "Reenviar código"
2. Ingresar el nuevo código rápidamente

### Problema: Código incorrecto

**Causas posibles:**
- Error al escribir el código
- Código ya usado
- Código expirado

**Soluciones:**
1. Verificar que el código sea correcto
2. Solicitar nuevo código
3. Verificar que no hayan pasado 10 minutos

### Problema: No puedo habilitar 2FA

**Causas posibles:**
- No hay email/teléfono configurado
- Permisos insuficientes
- Error en base de datos

**Soluciones:**
1. Verificar que tengas email configurado
2. Contactar al administrador
3. Revisar logs de error

### Problema: SMS no llega

**Causa:** Servicio SMS no configurado

**Solución:**
1. Configurar servicio SMS (Twilio, etc.)
2. Usar método de email temporalmente
3. Contactar al administrador

---

## 📊 Estadísticas y Monitoreo

### Consultas Útiles

**Usuarios con 2FA habilitado:**
```sql
SELECT COUNT(*) as total, two_factor_method, 
       COUNT(*) * 100.0 / (SELECT COUNT(*) FROM usuarios) as porcentaje
FROM usuarios 
WHERE two_factor_enabled = 1
GROUP BY two_factor_method;
```

**Intentos de login con 2FA (últimos 7 días):**
```sql
SELECT DATE(fecha_hora) as fecha, COUNT(*) as intentos
FROM auditoria
WHERE accion = 'login_2fa'
AND fecha_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY DATE(fecha_hora)
ORDER BY fecha DESC;
```

**Códigos activos:**
```sql
SELECT COUNT(*) as codigos_activos
FROM verification_codes
WHERE expires_at > NOW();
```

---

## 📚 Referencias

### Archivos del Sistema

- `servicios/two_factor_auth.php` - Clase principal
- `servicios/autenticador.php` - Integración con login
- `servicios/verificacion-2fa.php` - Interfaz de verificación
- `servicios/procesar-2fa.php` - Procesador de códigos
- `servicios/reenviar-codigo-2fa.php` - Reenvío de códigos
- `servicios/guardar_2fa.php` - Guardar preferencias
- `servicios/verificar_2fa_setup.php` - Verificación de instalación

### Documentación Relacionada

- [Configuración de Email](configuracion_email_produccion.md)
- [Arquitectura del Sistema](arquitectura_sistema.md)
- [Solución de Problemas](../SOLUCION_PROBLEMAS.md)

---

**Sistema ARCO v2.0**  
**Autenticación de Dos Factores**  
**Documentación Completa**  
**Última actualización:** Diciembre 2025
