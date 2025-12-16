#!/bin/bash
# Script de instalación de PHPMailer para Sistema ARCO
# Linux/Mac Shell Script

echo "========================================"
echo "  Sistema ARCO - Instalador PHPMailer"
echo "========================================"
echo ""

# Verificar si Composer está instalado
if ! command -v composer &> /dev/null; then
    echo "[ERROR] Composer no está instalado"
    echo ""
    echo "Por favor instala Composer desde: https://getcomposer.org/download/"
    echo ""
    echo "Alternativa: Instalación manual"
    echo "1. Descargar: https://github.com/PHPMailer/PHPMailer/releases"
    echo "2. Extraer en: servicios/PHPMailer/"
    echo ""
    exit 1
fi

echo "[OK] Composer detectado"
echo ""

# Verificar si ya existe composer.json
if [ ! -f composer.json ]; then
    echo "[INFO] Creando composer.json..."
    echo ""
fi

# Instalar PHPMailer
echo "[INFO] Instalando PHPMailer..."
echo ""
composer require phpmailer/phpmailer

if [ $? -eq 0 ]; then
    echo ""
    echo "========================================"
    echo "  Instalación Completada"
    echo "========================================"
    echo ""
    echo "[OK] PHPMailer instalado correctamente"
    echo ""
    echo "Siguientes pasos:"
    echo "1. Editar: servicios/config_email.php"
    echo "2. Configurar credenciales SMTP"
    echo "3. Probar en: servicios/test_email.php"
    echo ""
    echo "Documentación completa:"
    echo "- documentacion/configuracion_email_produccion.md"
    echo ""
else
    echo ""
    echo "[ERROR] Error durante la instalación"
    echo ""
    echo "Intenta instalación manual:"
    echo "1. Descargar: https://github.com/PHPMailer/PHPMailer/releases"
    echo "2. Extraer en: servicios/PHPMailer/"
    echo ""
fi
