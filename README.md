# 📦 Sistema ARCO - Gestión de Inventarios

**Administración y Registro de Control de Operaciones**

Sistema web moderno y profesional para la gestión integral de inventarios empresariales.

[![Versión](https://img.shields.io/badge/versión-2.0.0-blue.svg)](https://github.com/arco-sistema)
[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1.svg)](https://mysql.com)
[![Licencia](https://img.shields.io/badge/licencia-MIT-green.svg)](LICENSE)

---

## 📋 Descripción

ARCO es un sistema web completo para la gestión integral de inventarios empresariales, desarrollado con arquitectura MVC en PHP. Incluye funcionalidades avanzadas de seguridad, autenticación de dos factores, gestión de roles y auditoría completa.

## ✨ Características Principales

### 🔐 Seguridad Avanzada
- **Autenticación de Dos Factores (2FA)** por email o SMS
- **Gestión de Roles y Permisos** granular
- **Encriptación de contraseñas** con Argon2ID
- **Protección CSRF** y validación de entrada
- **Auditoría completa** de acciones de usuarios

### 📦 Gestión de Inventarios
- Control completo de productos y categorías
- Registro de movimientos de entrada y salida
- Alertas automáticas de stock bajo
- Gestión de órdenes de compra y devoluciones
- Trazabilidad completa de materiales

### 📊 Reportes y Análisis
- Generación de reportes personalizados
- Exportación en múltiples formatos (PDF, Excel, CSV)
- Dashboard con métricas en tiempo real
- Comprobantes de movimientos mejorados

### 👥 Administración de Usuarios
- Cinco roles predefinidos con permisos específicos
- Interfaz de administración intuitiva
- Gestión de sesiones seguras
- Registro de actividad detallado

## 🏗️ Arquitectura del Sistema

### Estructura de Directorios

```
/arco-sistema/
├── /aplicacion/                    # Código fuente principal
│   ├── /controladores/            # Controladores MVC
│   ├── /modelos/                  # Modelos de datos
│   ├── /vistas/                   # Interfaces de usuario
│   ├── /servicios/                # Servicios auxiliares
│   └── /middleware/               # Middleware de seguridad
├── /recursos/                     # Recursos estáticos
│   ├── /estilos/                 # Archivos CSS
│   ├── /scripts/                 # Archivos JavaScript
│   └── /imagenes/                # Recursos gráficos
├── /configuracion/               # Configuración del sistema
├── /documentacion/               # Documentación del proyecto
├── /base-datos/                 # Scripts de base de datos
├── /logs/                       # Archivos de registro
└── index.php                   # Punto de entrada
```

### Patrón MVC Implementado

- **Modelo**: Lógica de negocio y acceso a datos
- **Vista**: Interfaces de usuario responsive
- **Controlador**: Coordinación entre modelo y vista

## 🚀 Instalación

### Requisitos del Sistema

- **PHP**: 8.0 o superior
- **MySQL**: 8.0 o superior (o MariaDB 10.5+)
- **Servidor Web**: Apache 2.4+ o Nginx 1.18+
- **Extensiones PHP**: PDO, mysqli, mbstring, openssl

### Pasos de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/tu-usuario/arco-sistema.git
   cd arco-sistema
   ```

2. **Configurar la base de datos**
   ```bash
   # Crear base de datos
   mysql -u root -p -e "CREATE DATABASE arco_bdd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   
   # Importar esquema
   mysql -u root -p arco_bdd < base-datos/esquema-completo.sql
   ```

3. **Configurar el sistema**
   ```bash
   # Copiar archivo de configuración
   cp configuracion/aplicacion.ejemplo.php configuracion/aplicacion.php
   
   # Editar configuración de base de datos
   nano configuracion/base-datos.php
   ```

4. **Configurar permisos**
   ```bash
   # Dar permisos de escritura a directorios necesarios
   chmod 755 logs/ cache/ respaldos/
   chown -R www-data:www-data logs/ cache/ respaldos/
   ```

5. **Configurar servidor web**
   
   **Apache (.htaccess incluido)**
   ```apache
   <VirtualHost *:80>
       DocumentRoot /ruta/al/arco-sistema
       ServerName arco.local
       
       <Directory /ruta/al/arco-sistema>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

## 🔧 Configuración

### Configuración de Base de Datos

Editar `configuracion/base-datos.php`:

```php
const SERVIDOR = 'localhost';
const PUERTO = 3306;
const NOMBRE_BD = 'arco_bdd';
const USUARIO = 'tu_usuario';
const CONTRASEÑA = 'tu_contraseña';
```

### Configuración de Email (2FA y Recuperación de Contraseña)

El sistema incluye un módulo completo de envío de emails con soporte para múltiples proveedores SMTP.

**Configuración Rápida:**

1. **Instalar PHPMailer** (recomendado para producción):
   ```bash
   composer require phpmailer/phpmailer
   ```

2. **Configurar credenciales** en `servicios/config_email.php`:
   ```php
   const MODO = 'produccion';
   const SMTP_PROVIDER = 'gmail';
   const SMTP_USERNAME = 'tu_email@gmail.com';
   const SMTP_PASSWORD = 'tu_contraseña_app';
   ```

3. **Probar configuración**:
   - Abrir: `http://localhost/ARCO/servicios/test_email.php`
   - Enviar email de prueba

**Proveedores Soportados:**
- Gmail (con contraseña de aplicación)
- Outlook/Hotmail
- Office 365
- SendGrid
- Mailgun
- Servidor SMTP personalizado

**Documentación Completa:**
Ver [Configuración de Email para Producción](documentacion/configuracion_email_produccion.md) para instrucciones detalladas.

### Configuración de SMS (2FA)

Para habilitar SMS, configurar proveedor en `aplicacion/servicios/notificaciones/ServicioSMS.php`:

```php
// Para Twilio
$this->configurarProveedor('twilio', [
    'api_key' => 'tu_account_sid',
    'api_secret' => 'tu_auth_token',
    'remitente' => 'tu_numero_twilio'
]);
```

## 👤 Roles de Usuario

### Administrador del Sistema
- **Permisos**: Acceso completo al sistema
- **Funciones**: Configuración, gestión de usuarios, auditoría

### Administrador de Almacén
- **Permisos**: Gestión completa del inventario
- **Funciones**: Productos, movimientos, reportes, usuarios básicos

### Supervisor
- **Permisos**: Supervisión y control
- **Funciones**: Revisión de movimientos, aprobaciones, reportes

### Almacenista
- **Permisos**: Operaciones de almacén
- **Funciones**: Registro de movimientos, consultas de inventario

### Funcionario de Almacén
- **Permisos**: Consultas básicas
- **Funciones**: Ver inventarios y reportes básicos

## 📚 Documentación

### 📖 Índice Completo

**[Ver Índice de Documentación Completo](INDICE_DOCUMENTACION.md)** - Navegación rápida por toda la documentación

### Documentos Principales

- [**Inicio Rápido**](INICIO_RAPIDO.md) - Configuración en 5 minutos ⚡
- [**Cómo Configurar Email**](COMO_CONFIGURAR_EMAIL.md) - Guía visual paso a paso 📧
- [**Especificación de Requerimientos (SRS)**](documentacion/especificacion_requerimientos_software.md) - IEEE 830
- [**Arquitectura del Sistema**](documentacion/arquitectura_sistema.md) - Diseño técnico
- [**Configuración de Email para Producción**](documentacion/configuracion_email_produccion.md) - Guía completa de SMTP
- [**Solución de Problemas**](SOLUCION_PROBLEMAS.md) - Troubleshooting
- [**Guía del Sistema**](SISTEMA_ARCO_GUIA.md) - Guía general

### API y Endpoints

El sistema incluye endpoints REST para integración:

```
GET    /api/inventario          # Listar productos
POST   /api/inventario          # Crear producto
PUT    /api/inventario/{id}     # Actualizar producto
DELETE /api/inventario/{id}     # Eliminar producto

GET    /api/movimientos         # Listar movimientos
POST   /api/movimientos         # Registrar movimiento

GET    /api/reportes            # Generar reportes
```

## 🔒 Seguridad

### Medidas Implementadas

- **Contraseñas**: Hash Argon2ID con salt
- **Sesiones**: Tokens seguros con expiración
- **2FA**: Códigos de 6 dígitos con expiración de 10 minutos
- **CSRF**: Tokens únicos por formulario
- **XSS**: Sanitización y escape de datos
- **SQL Injection**: Prepared statements
- **Headers**: Configuración de seguridad automática

### Configuración de Seguridad

```php
// Configurar en configuracion/seguridad.php
const INTENTOS_MAXIMOS_LOGIN = 5;
const TIEMPO_BLOQUEO_LOGIN = 900; // 15 minutos
const LONGITUD_MINIMA_CONTRASEÑA = 8;
const REQUIERE_MAYUSCULAS = true;
const REQUIERE_NUMEROS = true;
const REQUIERE_SIMBOLOS = true;
```

## 📊 Monitoreo y Logs

### Archivos de Log

- `logs/aplicacion.log` - Eventos generales del sistema
- `logs/errores.log` - Errores y excepciones
- `logs/auditoria.log` - Acciones de usuarios
- `logs/autenticacion-2fa.log` - Eventos de 2FA
- `logs/email.log` - Envío de correos
- `logs/sms.log` - Envío de SMS

### Verificación de Salud

```bash
# Verificar estado del sistema
curl http://tu-dominio/inicio/salud

# Respuesta esperada
{
  "estado_general": "operativo",
  "sistema": { "estado": "operativo" },
  "base_datos": { "estado": "saludable" },
  "seguridad": { "headers_aplicados": true }
}
```

## 🧪 Pruebas

### Ejecutar Pruebas

```bash
# Pruebas unitarias
php pruebas/unitarias/ejecutar.php

# Pruebas de integración
php pruebas/integracion/ejecutar.php

# Pruebas funcionales
php pruebas/funcionales/ejecutar.php
```

## 🚀 Despliegue en Producción

### Lista de Verificación

- [ ] Cambiar `ENTORNO` a `'produccion'` en configuración
- [ ] Configurar SSL/HTTPS
- [ ] Establecer contraseñas seguras de BD
- [ ] Configurar respaldos automáticos
- [ ] Configurar monitoreo de logs
- [ ] Probar funcionalidad de 2FA
- [ ] Verificar permisos de archivos
- [ ] Configurar firewall

### Respaldos Automáticos

```bash
# Configurar cron para respaldos diarios
0 2 * * * /usr/bin/php /ruta/al/arco/scripts/respaldo-diario.php
```

## 🤝 Contribución

### Cómo Contribuir

1. Fork del repositorio
2. Crear rama para nueva funcionalidad (`git checkout -b feature/nueva-funcionalidad`)
3. Commit de cambios (`git commit -am 'Agregar nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Crear Pull Request

### Estándares de Código

- **PSR-12**: Estándar de codificación PHP
- **Documentación**: PHPDoc en todas las funciones
- **Pruebas**: Cobertura mínima del 80%
- **Seguridad**: Validación en todas las entradas

## 📞 Soporte

### Contacto

- **Email**: soporte@arco-sistema.com
- **Documentación**: [Wiki del proyecto](https://github.com/tu-usuario/arco-sistema/wiki)
- **Issues**: [GitHub Issues](https://github.com/tu-usuario/arco-sistema/issues)

### Problemas Comunes

**Error de conexión a BD**
```bash
# Verificar credenciales en configuracion/base-datos.php
# Verificar que MySQL esté ejecutándose
sudo systemctl status mysql
```

**Problemas con 2FA**
```bash
# Verificar logs de autenticación
tail -f logs/autenticacion-2fa.log

# Verificar configuración de email
tail -f logs/email.log
```

## 📄 Licencia

Este proyecto está licenciado bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para detalles.

## 🙏 Agradecimientos

- Equipo de desarrollo ARCO
- Comunidad PHP
- Contribuidores del proyecto

---

**Sistema ARCO v2.0** - Desarrollado con ❤️ para la gestión eficiente de inventarios
