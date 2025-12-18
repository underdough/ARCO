<?php
/**
 * Incluir Chatbot en todas las vistas
 * Agregar esta línea antes de </body> en cada vista
 */

// Solo mostrar chatbot si el usuario está autenticado
if (isset($_SESSION['usuario_id'])) {
    echo '<!-- Chatbot Widget -->' . "\n";
    echo '<link rel="stylesheet" href="../componentes/chatbot.css">' . "\n";
    echo '<script src="../componentes/chatbot.js"></script>' . "\n";
}
?>
