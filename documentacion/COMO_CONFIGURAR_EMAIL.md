# 📧 Cómo Configurar Email - Guía Visual

## 🎯 Guía Paso a Paso para Configurar Emails en ARCO

Esta guía te llevará de la mano para configurar el envío de emails en el Sistema ARCO.

---

## 🚀 Opción 1: Configuración Rápida (Gmail)

### ⏱️ Tiempo estimado: 10 minutos

### Paso 1: Preparar tu Cuenta de Gmail

1. **Abrir Google Account Security**
   ```
   https://myaccount.google.com/security
   ```

2. **Activar Verificación en 2 Pasos**
   - Buscar "Verificación en 2 pasos"
   - Hacer clic en "Activar"
   - Seguir las instrucciones de Google

3. **Generar Contraseña de Aplicación**
   ```
   https://myaccount.google.com/apppasswords
   ```
   - Seleccionar "Correo"
   - Seleccionar "Otro (nombre personalizado)"
   - Escribir: "Sistema ARCO"
   - Hacer clic en "Generar"
   - **COPIAR** la contraseña de 16 caracteres (ejemplo: `abcd efgh ijkl mnop`)

### Paso 2: Instalar PHPMailer

**Opción A: Con Script Automático (Recomendado)**

En Windows, hacer doble clic en:
```
instalar_phpmailer.bat
```

**Opción B: Con Composer**

Abrir terminal en la carpeta del proyecto y ejecutar:
```bash
composer require phpmailer/phpmailer
```

### Paso 3: Configurar Credenciales

1. **Abrir el archivo de configuración:**
   ```
   servicios/config_email.php
   ```

2. **Editar estas líneas:**
   ```php
   const MODO = 'produccion';  // Cambiar de 'desarrollo' a 'produccion'
   const SMTP_PROVIDER = 'gmail';
   const SMTP_USERNAME = 'tu_email@gmail.com';  // Tu email de Gmail
   const SMTP_PASSWORD = 'abcd efgh ijkl mnop';  // La contraseña de aplicación
   ```

3. **Guardar el archivo**

### Paso 4: Probar la Configuración

1. **Abrir en el navegador:**
   ```
   http://localhost/ARCO/ARCO/servicios/test_email.php
   ```

2. **Verificar el estado:**
   - PHPMailer disponible: ✅
   - SMTP configurado: ✅
   - Modo: PRODUCCION

3. **Enviar email de prueba:**
   - Ingresar tu email
   - Hacer clic en "Enviar Email de Prueba"
   - Verificar que llegue el correo

### Paso 5: Probar Recuperación de Contraseña

1. **Ir a la página de login:**
   ```
   http://localhost/ARCO/ARCO/login.html
   ```

2. **Hacer clic en "¿Olvidaste tu contraseña?"**

3. **Ingresar un email registrado**

4. **Verificar que llegue el email**

5. **Hacer clic en el link del email**

6. **Restablecer la contraseña**

### ✅ ¡Listo! El sistema está configurado

---

## 🚀 Opción 2: Configuración con Outlook

### ⏱️ Tiempo estimado: 5 minutos

### Paso 1: Instalar PHPMailer

(Igual que Gmail - Paso 2)

### Paso 2: Configurar Credenciales

1. **Abrir:**
   ```
   servicios/config_email.php
   ```

2. **Editar:**
   ```php
   const MODO = 'produccion';
   const SMTP_PROVIDER = 'outlook';
   const SMTP_USERNAME = 'tu_email@outlook.com';  // Tu email de Outlook
   const SMTP_PASSWORD = 'tu_contraseña';  // Tu contraseña NORMAL de Outlook
   ```

3. **Guardar**

### Paso 3: Probar

(Igual que Gmail - Pasos 4 y 5)

---

## 🚀 Opción 3: Usar sin Configurar (Modo Desarrollo)

### ⏱️ Tiempo estimado: 0 minutos

### ¿Qué hace?

El sistema funciona automáticamente sin configurar nada. Cuando intentas recuperar contraseña:

1. **Muestra el link directamente en la pantalla**
   - Aparece en un cuadro amarillo
   - Puedes hacer clic o copiar el link

2. **Guarda el link en los logs del servidor**
   - Puedes verlo en los logs de Apache/PHP

### ¿Cuándo usar esto?

- ✅ Para desarrollo local
- ✅ Para pruebas rápidas
- ✅ Cuando no quieres configurar SMTP
- ❌ NO para producción

### Cómo usar:

1. **Ir a recuperación de contraseña:**
   ```
   http://localhost/ARCO/ARCO/vistas/recuperar-contra.php
   ```

2. **Ingresar un email registrado**

3. **Ver el cuadro amarillo con el link**

4. **Hacer clic en el link**

5. **Restablecer contraseña**

---

## 🔍 Verificar que Todo Funciona

### Checklist de Verificación

- [ ] PHPMailer instalado (verificar en `test_email.php`)
- [ ] Credenciales configuradas en `config_email.php`
- [ ] Modo cambiado a 'produccion'
- [ ] Email de prueba enviado exitosamente
- [ ] Email de prueba recibido en bandeja de entrada
- [ ] Recuperación de contraseña probada
- [ ] Email de recuperación recibido
- [ ] Link de recuperación funciona
- [ ] Contraseña restablecida exitosamente

---

## ❌ Problemas Comunes y Soluciones

### Problema 1: "PHPMailer no disponible"

**Solución:**
```bash
# Ejecutar en la carpeta del proyecto
composer require phpmailer/phpmailer

# O usar el script
instalar_phpmailer.bat
```

### Problema 2: "Error de autenticación SMTP"

**Para Gmail:**
- ✅ Verificar que usas contraseña de aplicación (no contraseña normal)
- ✅ Verificar que 2FA esté activo
- ✅ Verificar que el email sea correcto

**Para Outlook:**
- ✅ Usar contraseña normal (no de aplicación)
- ✅ Verificar que el email sea correcto

### Problema 3: "Email no llega"

**Solución:**
1. Revisar carpeta de spam
2. Verificar en `test_email.php` que todo esté OK
3. Revisar logs del servidor
4. Probar con otro email

### Problema 4: "Página test_email.php no carga"

**Solución:**
```
Verificar la ruta correcta:
http://localhost/ARCO/ARCO/servicios/test_email.php

O si tu instalación es diferente:
http://localhost/ARCO/servicios/test_email.php
```

---

## 📊 Comparación de Opciones

| Opción | Dificultad | Tiempo | Para Producción | Recomendado |
|--------|------------|--------|-----------------|-------------|
| Gmail | ⭐⭐⭐ | 10 min | ✅ Sí | ✅ Sí |
| Outlook | ⭐⭐ | 5 min | ✅ Sí | ✅ Sí |
| Modo Desarrollo | ⭐ | 0 min | ❌ No | Solo desarrollo |
| SendGrid | ⭐⭐⭐⭐ | 15 min | ✅ Sí | Para alto volumen |
| Mailgun | ⭐⭐⭐⭐ | 15 min | ✅ Sí | Para alto volumen |

---

## 🎓 Preguntas Frecuentes

### ¿Necesito pagar por enviar emails?

**No.** Gmail y Outlook son gratuitos para uso normal. SendGrid y Mailgun tienen planes gratuitos generosos.

### ¿Cuántos emails puedo enviar?

- **Gmail:** ~500 por día (uso normal)
- **Outlook:** ~300 por día (uso normal)
- **SendGrid:** 100 por día (plan gratuito)
- **Mailgun:** 5,000 por mes (plan gratuito)

### ¿Es seguro guardar mi contraseña en el archivo?

**Sí**, si:
- Usas contraseña de aplicación (no tu contraseña real)
- El archivo está en `.gitignore` (no se sube a Git)
- El servidor está protegido

**Mejor aún:** Usar variables de entorno (ver documentación avanzada)

### ¿Puedo usar mi propio servidor SMTP?

**Sí.** Configurar:
```php
const SMTP_PROVIDER = 'custom';
const SMTP_HOST = 'mail.tudominio.com';
const SMTP_PORT = 587;
const SMTP_USERNAME = 'noreply@tudominio.com';
const SMTP_PASSWORD = 'tu_contraseña';
```

### ¿Qué pasa si no configuro nada?

El sistema funciona en **modo desarrollo**:
- Muestra links directos en la pantalla
- Guarda links en logs
- Útil para desarrollo
- NO recomendado para producción

---

## 📚 Documentación Adicional

### Para Más Información

- **Guía Completa:** `documentacion/configuracion_email_produccion.md`
- **Solución de Problemas:** `SOLUCION_PROBLEMAS.md`
- **Inicio Rápido:** `INICIO_RAPIDO.md`

### Proveedores Específicos

- **Gmail:** Ver sección "Gmail" en `configuracion_email_produccion.md`
- **Outlook:** Ver sección "Outlook" en `configuracion_email_produccion.md`
- **SendGrid:** Ver sección "SendGrid" en `configuracion_email_produccion.md`
- **Mailgun:** Ver sección "Mailgun" en `configuracion_email_produccion.md`

---

## 🎉 ¡Felicidades!

Si llegaste hasta aquí y todo funciona, ¡ya tienes el sistema de emails configurado!

### Próximos Pasos

1. ✅ Cambiar contraseña por defecto del admin
2. ✅ Crear usuarios para tu equipo
3. ✅ Configurar datos de la empresa
4. ✅ Empezar a usar el sistema

---

## 📞 ¿Necesitas Ayuda?

### Recursos Disponibles

1. **Página de Prueba:**
   ```
   http://localhost/ARCO/ARCO/servicios/test_email.php
   ```

2. **Documentación:**
   - `documentacion/configuracion_email_produccion.md`
   - `SOLUCION_PROBLEMAS.md`

3. **Logs del Servidor:**
   - Windows (Laragon): `C:\laragon\bin\apache\apache-x.x.x\logs\error.log`
   - Windows (XAMPP): `C:\xampp\apache\logs\error.log`
   - Windows (WAMP): `C:\wamp64\logs\apache_error.log`

---

**Sistema ARCO v2.0**  
**Configuración de Email Simplificada**  
**¡Listo para Usar!** 🚀
