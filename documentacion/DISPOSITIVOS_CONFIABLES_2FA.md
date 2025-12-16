# Sistema de Dispositivos Confiables para 2FA

## Descripción

El sistema 2FA ahora incluye la funcionalidad de **recordar dispositivos confiables**. Esto significa que el código de verificación solo se solicitará la primera vez que un usuario inicie sesión desde un dispositivo/navegador específico.

## Funcionamiento

### Primera vez desde un dispositivo
1. Usuario ingresa credenciales (documento y contraseña)
2. Sistema solicita código 2FA
3. Usuario ingresa código recibido por email/SMS
4. Sistema marca el dispositivo como confiable
5. Usuario accede al sistema

### Siguientes veces desde el mismo dispositivo
1. Usuario ingresa credenciales (documento y contraseña)
2. Sistema detecta que el dispositivo ya es confiable
3. Usuario accede directamente sin código 2FA

## Identificación de Dispositivos

El sistema identifica dispositivos usando:
- **Dirección IP**: IP desde donde se conecta el usuario
- **User Agent**: Información del navegador y sistema operativo
- **Huella Digital**: Hash SHA-256 único generado con IP + User Agent

### Ejemplo de huella digital
```
IP: 192.168.1.100
User Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)...
Fingerprint: a3f5b8c9d2e1f4a7b6c5d8e9f1a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d7e8f9a0
```

## Cambios en la Base de Datos

### Nuevas columnas en `verification_codes`

```sql
- verified (TINYINT): Indica si el código fue verificado exitosamente
- ip_address (VARCHAR): Dirección IP del dispositivo
- user_agent (VARCHAR): User Agent del navegador
- device_fingerprint (VARCHAR): Huella digital única del dispositivo
```

### Aplicar actualización

Ejecutar el script SQL:
```bash
mysql -u usuario -p nombre_base_datos < base-datos/actualizar_verification_codes_dispositivos.sql
```

O desde phpMyAdmin:
1. Abrir phpMyAdmin
2. Seleccionar la base de datos
3. Ir a la pestaña "SQL"
4. Copiar y ejecutar el contenido de `actualizar_verification_codes_dispositivos.sql`

## Archivos Modificados

### 1. `servicios/two_factor_auth.php`
**Nuevos métodos:**
- `generateDeviceFingerprint()`: Genera huella digital del dispositivo
- `isDeviceTrusted($userId)`: Verifica si el dispositivo es confiable
- `markDeviceAsTrusted($userId)`: Marca dispositivo como confiable

**Métodos modificados:**
- `saveVerificationCode()`: Ahora guarda información del dispositivo

### 2. `servicios/autenticador.php`
**Cambios:**
- Verifica si el dispositivo es confiable antes de solicitar 2FA
- Si es confiable, permite acceso directo
- Si es nuevo, solicita código 2FA

### 3. `servicios/procesar-2fa.php`
**Cambios:**
- Marca el dispositivo como confiable después de verificación exitosa

## Seguridad

### Ventajas
- ✅ Mejor experiencia de usuario (menos interrupciones)
- ✅ Mantiene seguridad en dispositivos nuevos
- ✅ Registro de todos los dispositivos usados

### Consideraciones
- Si un usuario cambia de navegador, se solicitará 2FA nuevamente
- Si la IP cambia (redes móviles), puede solicitarse 2FA
- Los dispositivos confiables se mantienen por 1 año

## Gestión de Dispositivos Confiables

### Ver dispositivos de un usuario
```sql
SELECT 
    user_id,
    ip_address,
    LEFT(user_agent, 50) as navegador,
    device_fingerprint,
    created_at as ultimo_uso
FROM verification_codes
WHERE user_id = ? AND verified = 1
ORDER BY created_at DESC;
```

### Eliminar dispositivo confiable
```sql
DELETE FROM verification_codes 
WHERE user_id = ? 
AND device_fingerprint = ? 
AND verified = 1;
```

### Eliminar todos los dispositivos de un usuario
```sql
DELETE FROM verification_codes 
WHERE user_id = ? 
AND verified = 1;
```

## Casos de Uso

### Usuario en computadora de trabajo
- Primera vez: Solicita 2FA ✅
- Días siguientes: Acceso directo ✅

### Usuario en computadora personal
- Primera vez: Solicita 2FA ✅
- Días siguientes: Acceso directo ✅

### Usuario en computadora pública/café internet
- Primera vez: Solicita 2FA ✅
- Días siguientes: Solicita 2FA ✅ (diferente IP/navegador)

### Usuario cambia de navegador
- Chrome → Firefox: Solicita 2FA ✅
- Modo normal → Modo incógnito: Solicita 2FA ✅

## Auditoría

El sistema registra en la tabla `auditoria`:
- Login desde dispositivo confiable
- Login con 2FA (dispositivo nuevo)

```sql
SELECT 
    u.nombre,
    u.apellido,
    a.accion,
    a.descripcion,
    a.ip_address,
    a.fecha_hora
FROM auditoria a
JOIN usuarios u ON a.usuario_id = u.id_usuarios
WHERE a.accion IN ('login', 'login_2fa')
ORDER BY a.fecha_hora DESC;
```

## Mantenimiento

### Limpiar dispositivos antiguos (más de 1 año)
```sql
DELETE FROM verification_codes 
WHERE verified = 1 
AND created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

### Estadísticas de uso
```sql
SELECT 
    COUNT(DISTINCT user_id) as usuarios_con_2fa,
    COUNT(*) as total_dispositivos,
    AVG(dispositivos_por_usuario) as promedio_dispositivos
FROM (
    SELECT user_id, COUNT(*) as dispositivos_por_usuario
    FROM verification_codes
    WHERE verified = 1
    GROUP BY user_id
) as stats;
```

## Soporte

Si un usuario necesita restablecer sus dispositivos confiables:
1. Acceder como administrador
2. Ejecutar: `DELETE FROM verification_codes WHERE user_id = [ID] AND verified = 1`
3. El usuario deberá verificar 2FA en su próximo inicio de sesión
