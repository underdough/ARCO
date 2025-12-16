# Configuración de Email para Producción - Sistema ARCO

## 📧 Guía Completa de Configuración de Envío de Emails

Esta guía te ayudará a configurar el sistema de envío de emails para que funcione tanto en desarrollo como en producción.

---

## 🎯 Resumen Rápido

El sistema ARCO soporta dos métodos de envío de emails:

1. **PHPMailer (SMTP)** - Recomendado para producción
2. **mail() nativo** - Fallback automático si PHPMailer no está disponible

---

## 📦 Instalación de PHPMailer

### Opción 1: Usando Composer (Recomendado)

```bash
# Navegar a la carpeta del proyecto
cd C:\laragon\www\ARCO\ARCO

# Instalar PHPMailer
composer require phpmailer/phpmailer
```

### Opción 2: Instalación Manual

1. **Descargar PHPMailer:**
   - Ir a: https://github.com/PHPMailer/PHPMailer/releases
   - Descargar la última versión (ZIP)

2. **Extraer archivos:**
   - Extraer el contenido en: `servicios/PHPMailer/`
   - Estructura final:
     ```
     servicios/
     ├── PHPMailer/
     │   ├── PHPMailer.php
     │   ├── SMTP.php
     │   ├── Exception.php
     │   └── ...
     ```

3. **Verificar instalación:**
   - Los archivos deben estar en `servicios/PHPMailer/`
   - El sistema los detectará automáticamente

---

## ⚙️ Configuración del Sistema

### 1. Editar Archivo de Configuración

Abrir: `servicios/config_email.php`

```php
<?php
class ConfigEmail {
    
    // Cambiar a 'produccion' cuando esté listo
    const MODO = 'produccion';
    
    // Proveedor SMTP
    const SMTP_PROVIDER = 'gmail'; // gmail, outlook, sendgrid, mailgun, custom
    
    // Configuración SMTP
    const SMTP_HOST = 'smtp.gmail.com';
    const SMTP_PORT = 587;
    const SMTP_SECURE = 'tls';
    
    // Credenciales
    const SMTP_USERNAME = 'tu_email@gmail.com';
    const SMTP_PASSWORD = 'tu_contraseña_app';
    
    // Remitente
    const FROM_EMAIL = 'noreply@arco.com';
    const FROM_NAME = 'Sistema ARCO';
}
```

---

## 🔐 Configuración por Proveedor

### Gmail

**Paso 1: Habilitar Verificación en 2 Pasos**
1. Ir a: https://myaccount.google.com/security
2. Activar "Verificación en 2 pasos"

**Paso 2: Generar Contraseña de Aplicación**
1. Ir a: https://myaccount.google.com/apppasswords
2. Seleccionar "Correo" y "Otro (nombre personalizado)"
3. Escribir "Sistema ARCO"
4. Copiar la contraseña generada (16 caracteres)

**Paso 3: Configurar en ARCO**
```php
const SMTP_PROVIDER = 'gmail';
const SMTP_HOST = 'smtp.gmail.com';
const SMTP_PORT = 587;
const SMTP_SECURE = 'tls';
const SMTP_USERNAME = 'tu_email@gmail.com';
const SMTP_PASSWORD = 'xxxx xxxx xxxx xxxx'; // Contraseña de aplicación
```

---

### Outlook / Hotmail

**Configuración:**
```php
const SMTP_PROVIDER = 'outlook';
const SMTP_HOST = 'smtp-mail.outlook.com';
const SMTP_PORT = 587;
const SMTP_SECURE = 'tls';
const SMTP_USERNAME = 'tu_email@outlook.com';
const SMTP_PASSWORD = 'tu_contraseña_normal';
```

**Nota:** Usar tu contraseña normal de Outlook/Hotmail.

---

### Office 365

**Configuración:**
```php
const SMTP_PROVIDER = 'office365';
const SMTP_HOST = 'smtp.office365.com';
const SMTP_PORT = 587;
const SMTP_SECURE = 'tls';
const SMTP_USERNAME = 'tu_email@tuempresa.com';
const SMTP_PASSWORD = 'tu_contraseña';
```

---

### SendGrid (Servicio Profesional)

**Paso 1: Crear Cuenta**
1. Ir a: https://sendgrid.com
2. Crear cuenta gratuita (100 emails/día)

**Paso 2: Generar API Key**
1. Ir a Settings > API Keys
2. Crear nueva API Key
3. Copiar la clave

**Paso 3: Configurar en ARCO**
```php
const SMTP_PROVIDER = 'sendgrid';
const SMTP_HOST = 'smtp.sendgrid.net';
const SMTP_PORT = 587;
const SMTP_SECURE = 'tls';
const SMTP_USERNAME = 'apikey'; // Literalmente "apikey"
const SMTP_PASSWORD = 'SG.xxxxxxxxxxxxxxxxx'; // Tu API Key
```

---

### Mailgun (Servicio Profesional)

**Paso 1: Crear Cuenta**
1. Ir a: https://mailgun.com
2. Crear cuenta (5,000 emails gratis/mes)

**Paso 2: Obtener Credenciales**
1. Ir a Sending > Domain Settings
2. Copiar credenciales SMTP

**Paso 3: Configurar en ARCO**
```php
const SMTP_PROVIDER = 'mailgun';
const SMTP_HOST = 'smtp.mailgun.org';
const SMTP_PORT = 587;
const SMTP_SECURE = 'tls';
const SMTP_USERNAME = 'postmaster@tu-dominio.mailgun.org';
const SMTP_PASSWORD = 'tu_contraseña_mailgun';
```

---

### Servidor SMTP Personalizado

Si tienes tu propio servidor SMTP:

```php
const SMTP_PROVIDER = 'custom';
const SMTP_HOST = 'mail.tudominio.com';
const SMTP_PORT = 587; // o 465 para SSL
const SMTP_SECURE = 'tls'; // o 'ssl'
const SMTP_USERNAME = 'noreply@tudominio.com';
const SMTP_PASSWORD = 'tu_contraseña';
```

---

## 🧪 Probar la Configuración

### 1. Acceder a la Página de Prueba

Abrir en el navegador:
```
http://localhost/ARCO/servicios/test_email.php
```

### 2. Verificar Estado

La página mostrará:
- ✅ Estado de PHPMailer (disponible/no disponible)
- ✅ Modo actual (desarrollo/producción)
- ✅ Configuración SMTP
- ✅ Método recomendado

### 3. Enviar Email de Prueba

1. Ingresar tu email en el formulario
2. Hacer clic en "Enviar Email de Prueba"
3. Verificar que llegue el correo

### 4. Revisar Logs

Si hay problemas, revisar logs del servidor:
- Laragon: `C:\laragon\bin\apache\apache-x.x.x\logs\error.log`
- XAMPP: `C:\xampp\apache\logs\error.log`
- WAMP: `C:\wamp64\logs\apache_error.log`

---

## 🔍 Solución de Problemas

### Problema: PHPMailer no se detecta

**Solución:**
1. Verificar que los archivos estén en `servicios/PHPMailer/`
2. Verificar permisos de lectura
3. Reiniciar servidor web

### Problema: Error de autenticación SMTP

**Solución Gmail:**
- Verificar que la verificación en 2 pasos esté activa
- Usar contraseña de aplicación, no contraseña normal
- Verificar que el email sea correcto

**Solución Outlook:**
- Verificar que SMTP esté habilitado en la cuenta
- Usar contraseña normal
- Verificar configuración de seguridad

### Problema: Timeout de conexión

**Solución:**
1. Verificar firewall del servidor
2. Verificar que el puerto SMTP esté abierto
3. Probar con puerto alternativo (465 para SSL)

### Problema: Email no llega

**Verificar:**
1. Carpeta de spam/correo no deseado
2. Logs del servidor para errores
3. Configuración del remitente (FROM_EMAIL)
4. Límites de envío del proveedor

---

## 📊 Comparación de Proveedores

| Proveedor | Emails Gratis | Facilidad | Velocidad | Recomendado Para |
|-----------|---------------|-----------|-----------|------------------|
| Gmail | Limitado | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | Desarrollo/Pequeño |
| Outlook | Limitado | ⭐⭐⭐⭐ | ⭐⭐⭐ | Desarrollo/Pequeño |
| SendGrid | 100/día | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | Producción |
| Mailgun | 5,000/mes | ⭐⭐⭐ | ⭐⭐⭐⭐⭐ | Producción |
| Custom | Depende | ⭐⭐ | Variable | Empresarial |

---

## 🚀 Modo Desarrollo vs Producción

### Modo Desarrollo
- Muestra link directo en la interfaz si el email falla
- Logs detallados en consola
- Permite usar mail() nativo como fallback
- Ideal para pruebas locales

### Modo Producción
- Usa PHPMailer con SMTP
- Logs mínimos (solo errores)
- No muestra información sensible
- Optimizado para rendimiento

**Cambiar modo:**
```php
// En servicios/config_email.php
const MODO = 'produccion'; // o 'desarrollo'
```

---

## 📝 Checklist de Configuración

- [ ] PHPMailer instalado en `servicios/PHPMailer/`
- [ ] Archivo `config_email.php` editado con credenciales
- [ ] Modo cambiado a 'produccion'
- [ ] Proveedor SMTP seleccionado
- [ ] Credenciales SMTP configuradas
- [ ] Email de remitente configurado
- [ ] Prueba realizada con `test_email.php`
- [ ] Email de prueba recibido exitosamente
- [ ] Logs revisados sin errores
- [ ] Recuperación de contraseña probada

---

## 🔒 Seguridad

### Proteger Credenciales

**Opción 1: Variables de Entorno (Recomendado)**

Crear archivo `.env` en la raíz:
```env
SMTP_USERNAME=tu_email@gmail.com
SMTP_PASSWORD=tu_contraseña_app
```

Modificar `config_email.php`:
```php
const SMTP_USERNAME = $_ENV['SMTP_USERNAME'] ?? 'default@email.com';
const SMTP_PASSWORD = $_ENV['SMTP_PASSWORD'] ?? 'default_password';
```

**Opción 2: Archivo de Configuración Externo**

Crear `servicios/config_email_local.php` (no subir a Git):
```php
<?php
return [
    'smtp_username' => 'tu_email@gmail.com',
    'smtp_password' => 'tu_contraseña_app'
];
```

Agregar a `.gitignore`:
```
servicios/config_email_local.php
```

---

## 📞 Soporte

Si tienes problemas:

1. Revisar logs del servidor
2. Probar con `test_email.php`
3. Verificar configuración del proveedor
4. Consultar documentación del proveedor SMTP
5. Revisar `SOLUCION_PROBLEMAS.md`

---

## 📚 Referencias

- PHPMailer: https://github.com/PHPMailer/PHPMailer
- Gmail App Passwords: https://myaccount.google.com/apppasswords
- SendGrid: https://sendgrid.com/docs/
- Mailgun: https://documentation.mailgun.com/

---

**Última actualización:** Diciembre 2025  
**Sistema ARCO v2.0**
