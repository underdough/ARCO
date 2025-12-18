# 🚀 Chatbot Local - Inicio Rápido

## ⚡ Instalación en 3 Pasos

### Paso 1: Verificar Archivos

Los siguientes archivos ya están creados:

```
✅ servicios/chatbot_api.php          (Backend)
✅ componentes/chatbot.css            (Estilos)
✅ componentes/chatbot.js             (Lógica)
✅ servicios/incluir_chatbot.php      (Integración)
✅ documentacion/CHATBOT_GUIA.md      (Documentación)
✅ tests/test_chatbot.php             (Pruebas)
```

### Paso 2: Agregar a una Vista

Antes de `</body>` en cualquier archivo PHP:

```html
<!-- Chatbot Widget -->
<link rel="stylesheet" href="../componentes/chatbot.css">
<script src="../componentes/chatbot.js"></script>
```

**Ejemplo en dashboard.php:**
```html
    </script>
    
    <!-- Chatbot Widget -->
    <link rel="stylesheet" href="../componentes/chatbot.css">
    <script src="../componentes/chatbot.js"></script>
</body>
</html>
```

### Paso 3: ¡Listo!

- Abre cualquier módulo del sistema
- Busca el botón flotante en la esquina inferior derecha
- Haz clic para abrir el chatbot
- ¡Comienza a hacer preguntas!

---

## 🧪 Probar el Chatbot

### Opción 1: Prueba Automática

1. Accede a: `http://localhost/ARCO/tests/test_chatbot.php`
2. Haz clic en "Ejecutar Pruebas"
3. Verifica que todas las pruebas sean exitosas

### Opción 2: Prueba Manual

1. Abre cualquier módulo del sistema
2. Haz clic en el botón del chatbot
3. Escribe una pregunta, ej: "¿Cómo registro un movimiento?"
4. Presiona Enter

---

## 💬 Preguntas de Ejemplo

Prueba estas preguntas:

- "Hola"
- "¿Qué es el Dashboard?"
- "¿Cómo creo una categoría?"
- "¿Cómo registro un movimiento?"
- "¿Qué es 2FA?"
- "¿Cuáles son los roles?"
- "¿Cómo uso los filtros?"
- "¿Qué son las anomalías?"

---

## 🎨 Personalización Rápida

### Cambiar Color

En `componentes/chatbot.css`, línea ~10:

**Antes:**
```css
background: linear-gradient(135deg, #395886 0%, #638ECB 100%);
```

**Después (ejemplo con rojo):**
```css
background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
```

### Cambiar Posición

En `componentes/chatbot.css`, línea ~5:

```css
.chatbot-widget {
    bottom: 20px;  /* Cambiar a 50px, 100px, etc. */
    right: 20px;   /* Cambiar a 50px, 100px, etc. */
}
```

### Cambiar Tamaño

En `componentes/chatbot.css`, línea ~30:

```css
.chatbot-container {
    width: 380px;   /* Cambiar ancho */
    height: 500px;  /* Cambiar alto */
}
```

---

## 📋 Checklist de Integración

- [ ] Archivos creados correctamente
- [ ] Incluir CSS y JS en vistas
- [ ] Probar en dashboard.php
- [ ] Probar en otras vistas
- [ ] Verificar respuestas del chatbot
- [ ] Personalizar colores (opcional)
- [ ] Documentar cambios

---

## 🔍 Solución de Problemas

### El chatbot no aparece
```
1. Verifica que estés autenticado
2. Abre consola (F12) y busca errores
3. Verifica que los archivos CSS y JS se carguen
4. Limpia caché del navegador (Ctrl+Shift+Del)
```

### El chatbot no responde
```
1. Verifica que chatbot_api.php sea accesible
2. Revisa la consola del navegador (F12)
3. Comprueba la conexión a internet
4. Ejecuta las pruebas: tests/test_chatbot.php
```

### Estilos no se aplican
```
1. Limpia caché (Ctrl+Shift+Del)
2. Verifica que chatbot.css se cargue
3. Abre consola (F12) y busca errores de CSS
4. Comprueba que no haya conflictos de CSS
```

---

## 📚 Documentación Completa

Para más información, consulta:

- [CHATBOT_GUIA.md](documentacion/CHATBOT_GUIA.md) - Guía completa
- [CHATBOT_README.md](componentes/CHATBOT_README.md) - Documentación técnica
- [CHATBOT_IMPLEMENTACION.md](CHATBOT_IMPLEMENTACION.md) - Detalles de implementación

---

## 🎯 Próximos Pasos

1. **Integrar en todas las vistas**
   - Agregar a: categorias.php, productos.php, movimientos.php, etc.

2. **Personalizar respuestas**
   - Editar `servicios/chatbot_api.php`
   - Agregar más palabras clave

3. **Agregar estadísticas**
   - Guardar preguntas frecuentes
   - Analizar patrones de uso

4. **Integración con IA** (Futuro)
   - Usar OpenAI para respuestas más inteligentes

---

## 📞 Soporte

Si tienes problemas:

1. Consulta la documentación
2. Ejecuta las pruebas
3. Revisa la consola del navegador
4. Contacta al equipo de desarrollo

---

**Chatbot ARCO v1.0** - ¡Listo para usar! 🚀

Fecha: Diciembre 2025  
Estado: Implementado ✅  
Versión: 1.0.0
