# 🚀 Inicio Rápido - Sistema ARCO

Guía de configuración rápida para poner en marcha el Sistema ARCO en minutos.

---

## ⚡ Instalación Express (5 minutos)

### 1. Requisitos Previos

Asegúrate de tener instalado:
- ✅ PHP 8.0 o superior
- ✅ MySQL 8.0 o superior
- ✅ Servidor web (Apache/Nginx)
- ✅ Composer (opcional, para emails)

### 2. Configurar Base de Datos

```bash
# Crear base de datos
mysql -u root -p -e "CREATE DATABASE arco_bdd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Importar esquema
mysql -u root -p arco_bdd < arco_bdd\ \(4\).sql
```

### 3. Configurar Conexión

Editar `servicios/conexion.php`:

```php
$servidor = "localhost";
$usuario = "root";
$contraseña = "tu_contraseña";
$base_datos = "arco_bdd";
```

### 4. Acceder al Sistema

Abrir en el navegador:
```
http://localhost/ARCO/login.html
```

**Credenciales por defecto:**
- Usuario: `admin`
- Contraseña: `admin123`

---

## 📧 Configurar Emails (Opcional)

### Opción 1: Instalación Automática

**Windows:**
```bash
instalar_phpmailer.bat
```

**Linux/Mac:**
```bash
chmod +x instalar_phpmailer.sh
./instalar_phpmailer.sh
```

### Opción 2: Instalación Manual con Composer

```bash
composer require phpmailer/phpmailer
```

### Configurar Credenciales

Editar `servicios/config_email.php`:

```php
const MODO = 'produccion';
const SMTP_PROVIDER = 'gmail';
const SMTP_USERNAME = 'tu_email@gmail.com';
const SMTP_PASSWORD = 'tu_contraseña_app';
```

### Probar Configuración

Abrir en el navegador:
```
http://localhost/ARCO/servicios/test_email.php
```

---

## 🔐 Configurar Gmail (Recomendado)

### Paso 1: Habilitar Verificación en 2 Pasos
1. Ir a: https://myaccount.google.com/security
2. Activar "Verificación en 2 pasos"

### Paso 2: Generar Contraseña de Aplicación
1. Ir a: https://myaccount.google.com/apppasswords
2. Seleccionar "Correo" y "Otro"
3. Escribir "Sistema ARCO"
4. Copiar la contraseña generada

### Paso 3: Configurar en ARCO
```php
const SMTP_USERNAME = 'tu_email@gmail.com';
const SMTP_PASSWORD = 'xxxx xxxx xxxx xxxx'; // Contraseña de aplicación
```

---

## ✅ Verificación del Sistema

### 1. Verificar Base de Datos
```bash
mysql -u root -p arco_bdd -e "SHOW TABLES;"
```

Deberías ver tablas como: `usuarios`, `productos`, `categorias`, `movimientos`, etc.

### 2. Verificar Login
- Abrir: `http://localhost/ARCO/login.html`
- Iniciar sesión con credenciales por defecto
- Deberías ver el dashboard

### 3. Verificar Emails (si configuraste)
- Abrir: `http://localhost/ARCO/servicios/test_email.php`
- Enviar email de prueba
- Verificar que llegue el correo

### 4. Probar Recuperación de Contraseña
- Ir a login y hacer clic en "¿Olvidaste tu contraseña?"
- Ingresar un email registrado
- Verificar que llegue el email con el link

---

## 🎯 Funcionalidades Principales

### Dashboard
```
http://localhost/ARCO/vistas/dashboard.php
```
- Métricas en tiempo real
- Alertas de stock bajo
- Resumen de movimientos

### Gestión de Productos
```
http://localhost/ARCO/vistas/productos.php
```
- Crear, editar, eliminar productos
- Gestión de categorías
- Control de stock

### Movimientos
```
http://localhost/ARCO/vistas/movimientos.php
```
- Registrar entradas y salidas
- Filtrar por fecha y tipo
- Imprimir comprobantes

### Reportes
```
http://localhost/ARCO/vistas/reportes.php
```
- Generar reportes personalizados
- Exportar en PDF/Excel
- Guardar reportes favoritos

### Usuarios (Solo Admin)
```
http://localhost/ARCO/vistas/usuarios.php
```
- Crear usuarios
- Asignar roles
- Gestionar permisos

### Configuración (Solo Admin)
```
http://localhost/ARCO/vistas/configuracion.php
```
- Datos de la empresa
- Configuración de emails
- Respaldos de base de datos
- Configuración de 2FA

---

## 🔧 Solución de Problemas Comunes

### Error: "No se puede conectar a la base de datos"

**Solución:**
1. Verificar que MySQL esté ejecutándose
2. Verificar credenciales en `servicios/conexion.php`
3. Verificar que la base de datos `arco_bdd` exista

### Error: "Email no se envía"

**Solución:**
1. Verificar que PHPMailer esté instalado
2. Verificar credenciales en `servicios/config_email.php`
3. Probar con `servicios/test_email.php`
4. Revisar logs del servidor

### Error: "Página en blanco"

**Solución:**
1. Activar errores de PHP:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```
2. Revisar logs del servidor web
3. Verificar permisos de archivos

### Error: "Session already started"

**Solución:**
1. Verificar que no haya `session_start()` duplicado
2. Limpiar caché del navegador
3. Reiniciar servidor web

---

## 📚 Documentación Completa

Para más información, consultar:

- **Configuración de Email**: `documentacion/configuracion_email_produccion.md`
- **Solución de Problemas**: `SOLUCION_PROBLEMAS.md`
- **Guía del Sistema**: `SISTEMA_ARCO_GUIA.md`
- **Arquitectura**: `documentacion/arquitectura_sistema.md`
- **Requerimientos**: `documentacion/especificacion_requerimientos_software.md`

---

## 🎓 Próximos Pasos

1. **Cambiar contraseña por defecto** del usuario admin
2. **Crear usuarios** para tu equipo
3. **Configurar datos de la empresa** en Configuración
4. **Crear categorías** de productos
5. **Agregar productos** al inventario
6. **Registrar movimientos** de entrada/salida
7. **Generar reportes** para análisis

---

## 🆘 Soporte

Si tienes problemas:

1. Revisar `SOLUCION_PROBLEMAS.md`
2. Revisar logs del servidor
3. Verificar configuración de base de datos
4. Verificar configuración de emails

---

## 🎉 ¡Listo!

Tu Sistema ARCO está configurado y listo para usar.

**Acceso:**
```
http://localhost/ARCO/login.html
```

---

**Sistema ARCO v2.0** - Gestión de Inventarios Profesional
