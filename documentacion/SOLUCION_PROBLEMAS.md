# 🔧 Solución de Problemas - Sistema ARCO

## 🚨 Problema: "Error de conexión" en Recuperación de Contraseña

### Solución Rápida

1. **Verificar que la tabla existe:**
   ```sql
   USE arco_bdd;
   SHOW TABLES LIKE 'password_resets';
   ```

2. **Si no existe, crearla:**
   ```bash
   mysql -u root -p arco_bdd < base-datos/crear_tabla_password_resets.sql
   ```

3. **O ejecutar manualmente:**
   ```sql
   USE arco_bdd;
   
   CREATE TABLE IF NOT EXISTS password_resets (
       id INT AUTO_INCREMENT PRIMARY KEY,
       usuario_id INT NOT NULL,
       token VARCHAR(64) NOT NULL UNIQUE,
       expira_en DATETIME NOT NULL,
       usado TINYINT(1) DEFAULT 0,
       creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
       INDEX idx_token (token),
       INDEX idx_expira (expira_en)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
   ```

4. **Verificar conexión a BD:**
   - Abrir `servicios/conexion.php`
   - Verificar que el nombre de BD sea correcto: `arco_bdd`
   - Verificar usuario y contraseña de MySQL

### Modo Desarrollo

El sistema ahora incluye **modo desarrollo** que muestra el link de recuperación directamente en la interfaz cuando el email no se puede enviar.

**Pasos:**
1. Ir a `http://localhost/ARCO/ARCO/vistas/recuperar-contra.php`
2. Ingresar un correo válido registrado en el sistema
3. Hacer clic en "Enviar Instrucciones"
4. Si el email no se envía, aparecerá un cuadro amarillo con el link directo
5. Hacer clic en el link o copiarlo al navegador

### Ver Logs del Servidor

Los logs muestran información útil:

**En Windows (XAMPP/WAMP):**
```
C:\xampp\apache\logs\error.log
C:\wamp64\logs\php_error.log
```

**En Linux:**
```bash
tail -f /var/log/apache2/error.log
```

**Buscar en los logs:**
```
=== RECUPERACIÓN DE CONTRASEÑA ===
Link de recuperación: http://...
```

---

## 🚨 Problema: "El enlace ha expirado"

### Causa
Los enlaces de recuperación expiran en 1 hora por seguridad.

### Solución
1. Solicitar un nuevo enlace de recuperación
2. Usar el enlace dentro de la hora siguiente

---

## 🚨 Problema: Email no llega

### Diagnóstico Rápido

1. **Verificar configuración de email:**
   ```
   http://localhost/ARCO/ARCO/servicios/test_email.php
   ```

2. **Ver estado del sistema:**
   - PHPMailer disponible: ✅ o ❌
   - SMTP configurado: ✅ o ❌
   - Modo actual: desarrollo o producción

### Soluciones

**1. Instalar PHPMailer (Recomendado para Producción):**

```bash
# Opción A: Con Composer
composer require phpmailer/phpmailer

# Opción B: Script automático (Windows)
instalar_phpmailer.bat

# Opción C: Script automático (Linux/Mac)
chmod +x instalar_phpmailer.sh
./instalar_phpmailer.sh

# Opción D: Manual
# Descargar: https://github.com/PHPMailer/PHPMailer/releases
# Extraer en: servicios/PHPMailer/
```

**2. Configurar credenciales SMTP:**

Editar `servicios/config_email.php`:

```php
const MODO = 'produccion';
const SMTP_PROVIDER = 'gmail';
const SMTP_USERNAME = 'tu_email@gmail.com';
const SMTP_PASSWORD = 'tu_contraseña_app'; // Contraseña de aplicación
```

**3. Generar contraseña de aplicación (Gmail):**

1. Ir a: https://myaccount.google.com/security
2. Activar "Verificación en 2 pasos"
3. Ir a: https://myaccount.google.com/apppasswords
4. Crear contraseña para "Sistema ARCO"
5. Copiar la contraseña generada (16 caracteres)
6. Usar en `SMTP_PASSWORD`

**4. Probar configuración:**

```
http://localhost/ARCO/ARCO/servicios/test_email.php
```

**5. Usar modo desarrollo (Alternativa):**
- El sistema automáticamente muestra el link en la interfaz
- También se guarda en los logs del servidor
- Útil para desarrollo local sin configurar SMTP

### Proveedores SMTP Soportados

| Proveedor | Host | Puerto | Configuración |
|-----------|------|--------|---------------|
| Gmail | smtp.gmail.com | 587 | Requiere contraseña de aplicación |
| Outlook | smtp-mail.outlook.com | 587 | Usar contraseña normal |
| Office 365 | smtp.office365.com | 587 | Usar contraseña normal |
| SendGrid | smtp.sendgrid.net | 587 | Usar API Key como contraseña |
| Mailgun | smtp.mailgun.org | 587 | Usar credenciales de Mailgun |

### Verificar Logs

**Ver logs del servidor:**

```bash
# Windows (Laragon)
C:\laragon\bin\apache\apache-x.x.x\logs\error.log

# Windows (XAMPP)
C:\xampp\apache\logs\error.log

# Windows (WAMP)
C:\wamp64\logs\apache_error.log

# Linux
tail -f /var/log/apache2/error.log
```

**Buscar en los logs:**
```
=== RECUPERACIÓN DE CONTRASEÑA ===
Email enviado: SÍ/NO
Método de envío: phpmailer/mail_nativo
```

### Documentación Completa

Ver: `documentacion/configuracion_email_produccion.md`

---

## 🚨 Problema: "Contraseña no cumple requisitos"

### Requisitos de Contraseña

La nueva contraseña debe tener:
- ✅ Mínimo 8 caracteres
- ✅ Al menos una letra mayúscula
- ✅ Al menos una letra minúscula
- ✅ Al menos un número

**Ejemplo válido:** `MiClave123`

---

## 🚨 Problema: Alertas de Stock no aparecen

### Solución

1. **Verificar que el script esté incluido:**
   ```html
   <script src="../recursos/scripts/alertas-stock.js"></script>
   ```

2. **Verificar en consola del navegador (F12):**
   - Buscar errores JavaScript
   - Verificar que se ejecute la petición a `verificar_stock_bajo.php`

3. **Verificar que haya productos con stock bajo:**
   ```sql
   SELECT * FROM productos WHERE stock <= stock_minimo;
   ```

4. **Verificar configuración de notificaciones:**
   - Ir a Configuración → Preferencias de Notificaciones
   - Activar "Notificaciones de Stock Bajo"

---

## 🚨 Problema: Error 404 en servicios

### Causa
Ruta incorrecta en las peticiones.

### Solución

**Verificar la estructura de carpetas:**
```
ARCO/
├── ARCO/              ← Carpeta interna
│   ├── servicios/
│   ├── vistas/
│   └── ...
```

**Ajustar rutas en los archivos:**
```javascript
// Si está en /ARCO/ARCO/
fetch('../servicios/recuperar_contrasena.php')

// Si está en /ARCO/
fetch('servicios/recuperar_contrasena.php')
```

---

## 🚨 Problema: Sesión expirada constantemente

### Solución

1. **Aumentar tiempo de sesión:**
   ```php
   // En servicios/autenticador.php o al inicio de cada página
   ini_set('session.gc_maxlifetime', 3600); // 1 hora
   session_start();
   ```

2. **Verificar cookies:**
   - Limpiar cookies del navegador
   - Verificar que las cookies estén habilitadas

---

## 🚨 Problema: 2FA no funciona

### Solución

1. **Verificar tabla verification_codes:**
   ```sql
   SHOW TABLES LIKE 'verification_codes';
   ```

2. **Crear tabla si no existe:**
   ```sql
   CREATE TABLE IF NOT EXISTS verification_codes (
       id INT AUTO_INCREMENT PRIMARY KEY,
       user_id INT NOT NULL,
       code VARCHAR(6) NOT NULL,
       type VARCHAR(10) DEFAULT 'email',
       expires_at DATETIME NOT NULL,
       created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
       attempts INT DEFAULT 0,
       INDEX idx_user_code (user_id, code),
       INDEX idx_expires (expires_at)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
   ```

3. **Verificar configuración en usuarios:**
   ```sql
   SELECT two_factor_enabled, two_factor_method FROM usuarios WHERE id_usuarios = 1;
   ```

---

## 🚨 Problema: Error de permisos en archivos

### Solución (Linux/Mac)

```bash
# Dar permisos a directorios
chmod 755 logs/ respaldos/ recursos/

# Dar permisos a archivos
chmod 644 servicios/*.php vistas/*.php

# Cambiar propietario (si es necesario)
chown -R www-data:www-data /ruta/al/arco/
```

### Solución (Windows)

1. Clic derecho en la carpeta → Propiedades
2. Pestaña "Seguridad"
3. Dar permisos de lectura/escritura al usuario de Apache/IIS

---

## 🚨 Problema: Base de datos no se conecta

### Diagnóstico

```php
<?php
// Crear archivo test_conexion.php en la raíz
require_once 'servicios/conexion.php';

try {
    $conexion = ConectarDB();
    if ($conexion) {
        echo "✅ Conexión exitosa a la base de datos<br>";
        echo "Servidor: " . $conexion->host_info . "<br>";
        
        $result = $conexion->query("SELECT DATABASE()");
        $db = $result->fetch_row();
        echo "Base de datos actual: " . $db[0];
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
```

### Soluciones comunes

1. **Verificar que MySQL esté ejecutándose:**
   ```bash
   # Windows
   net start MySQL
   
   # Linux
   sudo systemctl start mysql
   sudo systemctl status mysql
   ```

2. **Verificar credenciales:**
   ```php
   // En servicios/conexion.php
   $host = "localhost";
   $user = "root";
   $db   = "arco_bdd";
   ```

3. **Crear base de datos si no existe:**
   ```sql
   CREATE DATABASE IF NOT EXISTS arco_bdd 
   CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

---

## 📞 Obtener Ayuda Adicional

### Información útil para reportar problemas

1. **Versión de PHP:**
   ```bash
   php -v
   ```

2. **Versión de MySQL:**
   ```bash
   mysql --version
   ```

3. **Logs del servidor:**
   - Copiar los últimos errores del log
   - Incluir fecha y hora del error

4. **Navegador y versión:**
   - Chrome, Firefox, Edge, etc.
   - Versión del navegador

5. **Sistema operativo:**
   - Windows 10/11
   - Linux (distribución)
   - macOS

---

## ✅ Checklist de Verificación

Antes de reportar un problema, verificar:

- [ ] MySQL está ejecutándose
- [ ] Base de datos `arco_bdd` existe
- [ ] Tabla `password_resets` existe
- [ ] Tabla `usuarios` tiene datos
- [ ] Archivo `servicios/conexion.php` tiene credenciales correctas
- [ ] No hay errores en consola del navegador (F12)
- [ ] Logs del servidor no muestran errores críticos
- [ ] Permisos de archivos son correctos
- [ ] Ruta de acceso es correcta (localhost/ARCO/ARCO/ o localhost/ARCO/)

---

**Sistema ARCO** - Soporte Técnico 🛠️