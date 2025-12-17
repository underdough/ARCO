# 🔧 Solución de Problemas Comunes - Módulo de Anomalías

## ❌ Problema 1: Impacto muestra "-" en lugar del valor

### Causa:
El campo `impacto` no existe en tu tabla `anomalias`.

### Solución:

#### Opción A: Verificar primero
1. Abre en tu navegador: `http://localhost/ARCO/verificar_campos_anomalias.php`
2. Verás qué campos faltan en tu tabla
3. Sigue las instrucciones que aparecen en pantalla

#### Opción B: Ejecutar SQL directamente
1. Abre phpMyAdmin
2. Selecciona la base de datos `arco_bdd`
3. Ve a la pestaña "SQL"
4. Ejecuta el script: `base-datos/agregar_campos_anomalias.sql`

#### Opción C: Ejecutar manualmente
```sql
ALTER TABLE anomalias 
ADD COLUMN IF NOT EXISTS impacto ENUM('bajo', 'medio', 'alto', 'critico') DEFAULT 'medio';

ALTER TABLE anomalias 
ADD COLUMN IF NOT EXISTS codigo_seguimiento VARCHAR(20) DEFAULT NULL;

ALTER TABLE anomalias 
ADD COLUMN IF NOT EXISTS materiales_afectados TEXT DEFAULT NULL;

ALTER TABLE anomalias 
ADD COLUMN IF NOT EXISTS responsable_asignado INT DEFAULT NULL;

-- Generar códigos para anomalías existentes
UPDATE anomalias 
SET codigo_seguimiento = CONCAT('ANO-', YEAR(fecha_creacion), '-', LPAD(id, 6, '0'))
WHERE codigo_seguimiento IS NULL;
```

---

## ❌ Problema 2: Responsable Asignado solo muestra "Sin asignar"

### Causa:
El servicio `listar_usuarios.php` no está devolviendo JSON cuando se llama desde JavaScript.

### Solución:

#### Ya está corregido en el código, pero si persiste:

1. **Verifica que tienes usuarios activos:**
```sql
SELECT id_usuarios, nombre, apellido, rol, estado 
FROM usuarios 
WHERE estado = 'activo';
```

2. **Si no hay usuarios activos, activa algunos:**
```sql
UPDATE usuarios 
SET estado = 'activo' 
WHERE id_usuarios IN (1, 2, 3);
```

3. **Prueba el servicio directamente:**
   - Abre: `http://localhost/ARCO/servicios/listar_usuarios.php`
   - Deberías ver una tabla HTML
   - Ahora abre la consola del navegador en `anomalias.php`
   - Busca el mensaje: "Usuarios cargados: X"

4. **Si ves errores en la consola:**
   - Revisa que el archivo `servicios/listar_usuarios.php` tenga el código actualizado
   - Verifica que no haya errores PHP en el archivo

---

## ❌ Problema 3: Código de seguimiento no se genera

### Causa:
El campo `codigo_seguimiento` no existe o el trigger no está creado.

### Solución:

1. **Agregar el campo:**
```sql
ALTER TABLE anomalias 
ADD COLUMN IF NOT EXISTS codigo_seguimiento VARCHAR(20) DEFAULT NULL;
```

2. **Generar códigos para anomalías existentes:**
```sql
UPDATE anomalias 
SET codigo_seguimiento = CONCAT('ANO-', YEAR(fecha_creacion), '-', LPAD(id, 6, '0'))
WHERE codigo_seguimiento IS NULL OR codigo_seguimiento = '';
```

3. **Crear trigger para nuevas anomalías:**
```sql
DELIMITER //
CREATE TRIGGER IF NOT EXISTS tr_anomalias_codigo_seguimiento
    BEFORE INSERT ON anomalias
    FOR EACH ROW
BEGIN
    IF NEW.codigo_seguimiento IS NULL OR NEW.codigo_seguimiento = '' THEN
        SET NEW.codigo_seguimiento = CONCAT('ANO-', YEAR(NOW()), '-', LPAD((SELECT COALESCE(MAX(id), 0) + 1 FROM anomalias), 6, '0'));
    END IF;
END//
DELIMITER ;
```

---

## ❌ Problema 4: No se guardan materiales afectados

### Causa:
El campo `materiales_afectados` no existe en la tabla.

### Solución:
```sql
ALTER TABLE anomalias 
ADD COLUMN IF NOT EXISTS materiales_afectados TEXT DEFAULT NULL;
```

---

## ❌ Problema 5: Las notificaciones no se envían

### Causa:
Las tablas de notificaciones no existen.

### Solución:

1. **Verificar si existe la tabla:**
```sql
SHOW TABLES LIKE 'anomalias_notificaciones';
```

2. **Si no existe, crearla:**
```sql
CREATE TABLE IF NOT EXISTS anomalias_notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    anomalia_id INT NOT NULL,
    usuario_id INT NOT NULL,
    tipo_notificacion ENUM('creacion', 'asignacion', 'actualizacion', 'resolucion', 'vencimiento') NOT NULL,
    mensaje TEXT NOT NULL,
    leida BOOLEAN DEFAULT FALSE,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_lectura TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (anomalia_id) REFERENCES anomalias(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuarios) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 🔍 Script de Verificación Completa

Ejecuta este script para verificar todo:

```sql
-- 1. Verificar estructura de anomalias
DESCRIBE anomalias;

-- 2. Verificar tablas relacionadas
SHOW TABLES LIKE 'anomalias_%';

-- 3. Verificar usuarios activos
SELECT COUNT(*) as usuarios_activos FROM usuarios WHERE estado = 'activo';

-- 4. Verificar anomalías con campos completos
SELECT 
    id,
    titulo,
    codigo_seguimiento,
    impacto,
    responsable_asignado,
    materiales_afectados
FROM anomalias
LIMIT 5;

-- 5. Verificar notificaciones
SELECT COUNT(*) as total_notificaciones FROM anomalias_notificaciones;

-- 6. Verificar auditoría
SELECT COUNT(*) as total_auditoria FROM anomalias_auditoria;
```

---

## 📋 Checklist de Verificación

Antes de reportar un problema, verifica:

- [ ] ✅ Ejecuté el script `verificar_campos_anomalias.php`
- [ ] ✅ Todos los campos necesarios existen en la tabla
- [ ] ✅ Tengo usuarios activos en el sistema
- [ ] ✅ Las tablas de notificaciones y auditoría existen
- [ ] ✅ Limpié la caché del navegador (Ctrl + F5)
- [ ] ✅ Revisé la consola del navegador (F12) en busca de errores
- [ ] ✅ Verifiqué los logs de Apache/PHP

---

## 🆘 Si Nada Funciona

1. **Respalda tu base de datos:**
```sql
mysqldump -u root -p arco_bdd > backup_arco_bdd.sql
```

2. **Ejecuta el script completo:**
```
base-datos/actualizar_anomalias_avanzado.sql
```

3. **Reinicia Apache:**
   - En XAMPP: Stop y Start Apache

4. **Limpia caché del navegador:**
   - Chrome: Ctrl + Shift + Delete
   - Firefox: Ctrl + Shift + Delete

5. **Verifica permisos de MySQL:**
```sql
SHOW GRANTS FOR 'root'@'localhost';
```

---

## 📞 Información de Debug

Si necesitas ayuda, proporciona esta información:

1. **Resultado de verificar_campos_anomalias.php**
2. **Errores en consola del navegador (F12)**
3. **Versión de PHP:** `<?php echo phpversion(); ?>`
4. **Versión de MySQL:** `SELECT VERSION();`
5. **Logs de Apache:** `xampp/apache/logs/error.log`

---

**Última actualización:** 17 de diciembre de 2024  
**Versión:** 1.0