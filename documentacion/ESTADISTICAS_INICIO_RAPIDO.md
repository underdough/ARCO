# Estadísticas - Inicio Rápido

## 🚀 Instalación en 3 Pasos

### 1. Ejecutar SQL
Abre phpMyAdmin o tu cliente MySQL y ejecuta:
```bash
mysql -u root -p arco_bdd < documentacion/instalar_estadisticas.sql
```

O copia y pega el contenido de `instalar_estadisticas.sql` en phpMyAdmin.

### 2. Verificar Archivos
Confirma que existen estos archivos:
- ✅ `vistas/estadisticas.php`
- ✅ `servicios/estadisticas_data.php`
- ✅ `servicios/menu_dinamico.php` (actualizado)

### 3. Acceder al Módulo
1. Inicia sesión como **Administrador**, **Gerente** o **Supervisor**
2. Busca "Estadísticas" en el menú lateral
3. ¡Listo! Ya puedes ver tus estadísticas

---

## 📊 ¿Qué Puedes Ver?

### Tarjetas Principales
- **Total Productos**: Cantidad total con % de cambio
- **Movimientos del Mes**: Actividad mensual
- **Stock Total**: Inventario actual
- **Alertas de Stock**: Productos con stock bajo

### Gráficos Interactivos
1. **Movimientos por Mes** (líneas)
   - Filtra por año: 2023, 2024, 2025
   
2. **Productos por Categoría** (dona)
   - Top 10 categorías
   
3. **Stock por Categoría** (barras)
   - Distribución de inventario
   
4. **Movimientos por Tipo** (pastel)
   - Últimos 7, 30 o 90 días

---

## 🔧 Solución Rápida de Problemas

### ❌ No veo el módulo en el menú
```sql
-- Ejecuta esto en MySQL
SELECT * FROM modulos WHERE nombre = 'estadisticas';
-- Si no aparece nada, ejecuta instalar_estadisticas.sql
```

### ❌ Error 403 (Sin permisos)
Tu usuario no tiene el rol correcto. Solo pueden acceder:
- Administrador
- Gerente  
- Supervisor

### ❌ Los gráficos no cargan
1. Abre la consola del navegador (F12)
2. Ve a la pestaña "Network"
3. Busca errores en `estadisticas_data.php`
4. Verifica que la base de datos tiene datos en `materiales` y `movimientos`

### ❌ Página en blanco
Verifica que `servicios/estadisticas_data.php` existe y tiene permisos de lectura.

---

## 💡 Consejos de Uso

### Mejor Experiencia
- Usa Chrome, Firefox o Edge (última versión)
- Pantalla mínima recomendada: 1024px de ancho
- Funciona perfectamente en tablets y móviles

### Actualizar Datos
Los datos se cargan automáticamente. Para refrescar:
- Cambia los filtros de año o período
- Recarga la página (F5)

### Exportar Datos
Próximamente: Exportación a PDF y Excel

---

## 📱 Responsive

El módulo se adapta a:
- 💻 **Escritorio**: Vista completa con todos los gráficos
- 📱 **Tablet**: Gráficos apilados, navegación optimizada
- 📱 **Móvil**: Una columna, controles táctiles

---

## 🎨 Colores del Sistema

Los gráficos usan la paleta ARCO:
- **Primario**: #395886 (azul oscuro)
- **Secundario**: #638ECB (azul medio)
- **Terciario**: #8AAEE0 (azul claro)
- **Éxito**: #10b981 (verde)
- **Advertencia**: #f59e0b (naranja)
- **Peligro**: #ef4444 (rojo)

---

## 🔐 Seguridad

- ✅ Verificación de sesión
- ✅ Control de acceso por rol
- ✅ Protección SQL injection
- ✅ Validación de permisos en backend
- ✅ Headers de seguridad

---

## 📞 Soporte

Si tienes problemas:
1. Revisa `documentacion/INSTALACION_ESTADISTICAS.md`
2. Verifica los logs de PHP en tu servidor
3. Consulta la consola del navegador (F12)

---

**¡Disfruta de tus estadísticas!** 📈
