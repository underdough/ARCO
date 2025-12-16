<?php
/**
 * Página de Verificación 2FA - Sistema ARCO
 * Interfaz para ingresar código de verificación de dos factores
 * 
 * @version 2.0
 * @since 2025-12-15
 */

session_start();

// Verificar que hay una sesión temporal de 2FA
if (!isset($_SESSION['temp_user_id'])) {
    header("Location: ../login.html?error=" . urlencode("Sesión expirada. Por favor inicie sesión nuevamente."));
    exit;
}

$usuario = $_SESSION['temp_user_data'];
$metodo = $usuario['two_factor_method'] ?? 'email';

// Ocultar parcialmente el destino del código
function ocultarEmail($email) {
    $partes = explode('@', $email);
    $usuario = $partes[0];
    $dominio = $partes[1];
    $usuarioOculto = substr($usuario, 0, 2) . str_repeat('*', strlen($usuario) - 2);
    return $usuarioOculto . '@' . $dominio;
}

function ocultarTelefono($telefono) {
    $longitud = strlen($telefono);
    return substr($telefono, 0, 3) . str_repeat('*', $longitud - 6) . substr($telefono, -3);
}

$destino = $metodo === 'email' ? ocultarEmail($usuario['correo']) : ocultarTelefono($usuario['num_telefono']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Dos Factores - ARCO</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/login-pure.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .verification-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .verification-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .verification-header i {
            font-size: 48px;
            color: #2563eb;
            margin-bottom: 15px;
        }
        
        .verification-info {
            background: #f8fafc;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .code-input {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }
        
        .code-input input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .code-input input:focus {
            border-color: #2563eb;
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .resend-code {
            text-align: center;
            margin-top: 20px;
        }
        
        .resend-code button {
            background: none;
            border: none;
            color: #2563eb;
            cursor: pointer;
            text-decoration: underline;
        }
        
        .resend-code button:disabled {
            color: #9ca3af;
            cursor: not-allowed;
        }
        
        .timer {
            color: #6b7280;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="cont-auten">
        <div class="img-auten">
            <div>
                <h2>Sistema ARCO</h2>
                <p>Verificación de Seguridad</p>
                <img src="../componentes/img/logo2.png" alt="Logo ARCO" style="max-width: 150px;">
            </div>
        </div>
        
        <div class="form-auten">
            <div id="alertContainer"></div>
            
            <div class="verification-header">
                <i class="fas fa-shield-alt"></i>
                <h3>Verificación de Dos Factores</h3>
            </div>
            
            <div class="verification-info">
                <p><strong>Hola, <?= htmlspecialchars($usuario['nombre']) ?></strong></p>
                <p>Hemos enviado un código de verificación de 6 dígitos a:</p>
                <p><strong><?= $metodo === 'email' ? '📧' : '📱' ?> <?= htmlspecialchars($destino) ?></strong></p>
                <p class="timer">El código expira en <span id="countdown">10:00</span> minutos</p>
            </div>
            
            <form id="form2FA" action="procesar-2fa.php" method="POST">
                <div class="code-input">
                    <input type="text" maxlength="1" pattern="[0-9]" id="digit1" name="digit1" required autofocus>
                    <input type="text" maxlength="1" pattern="[0-9]" id="digit2" name="digit2" required>
                    <input type="text" maxlength="1" pattern="[0-9]" id="digit3" name="digit3" required>
                    <input type="text" maxlength="1" pattern="[0-9]" id="digit4" name="digit4" required>
                    <input type="text" maxlength="1" pattern="[0-9]" id="digit5" name="digit5" required>
                    <input type="text" maxlength="1" pattern="[0-9]" id="digit6" name="digit6" required>
                </div>
                
                <input type="hidden" name="codigo" id="codigoCompleto">
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Verificar Código
                    </button>
                </div>
            </form>
            
            <div class="resend-code">
                <p>¿No recibiste el código?</p>
                <button id="btnReenviar" onclick="reenviarCodigo()">
                    <i class="fas fa-redo"></i> Reenviar código
                </button>
                <p id="resendTimer" style="display: none; color: #6b7280; font-size: 14px;">
                    Podrás reenviar en <span id="resendCountdown">60</span> segundos
                </p>
            </div>
            
            <div class="text-center mt-3">
                <a href="../login.html" class="txt-olvidado">
                    <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Función para mostrar alertas
        function showAlert(message, type = 'danger') {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                ${message}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
            `;
            alertContainer.appendChild(alert);
            
            setTimeout(() => {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }, 5000);
        }
        
        // Manejo de inputs de código
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.code-input input');
            
            inputs.forEach((input, index) => {
                // Auto-avanzar al siguiente input
                input.addEventListener('input', function(e) {
                    if (this.value.length === 1 && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                    
                    // Actualizar código completo
                    actualizarCodigoCompleto();
                });
                
                // Retroceder con backspace
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && this.value === '' && index > 0) {
                        inputs[index - 1].focus();
                    }
                });
                
                // Pegar código completo
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const pastedData = e.clipboardData.getData('text').replace(/\D/g, '');
                    
                    if (pastedData.length === 6) {
                        inputs.forEach((inp, i) => {
                            inp.value = pastedData[i] || '';
                        });
                        inputs[5].focus();
                        actualizarCodigoCompleto();
                    }
                });
            });
            
            // Enviar formulario
            document.getElementById('form2FA').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const codigo = Array.from(inputs).map(inp => inp.value).join('');
                
                if (codigo.length !== 6) {
                    showAlert('Por favor ingrese el código completo de 6 dígitos', 'danger');
                    return;
                }
                
                document.getElementById('codigoCompleto').value = codigo;
                this.submit();
            });
            
            // Countdown timer
            let tiempoRestante = 600; // 10 minutos en segundos
            const countdownElement = document.getElementById('countdown');
            
            const timer = setInterval(() => {
                tiempoRestante--;
                
                const minutos = Math.floor(tiempoRestante / 60);
                const segundos = tiempoRestante % 60;
                
                countdownElement.textContent = `${minutos}:${segundos.toString().padStart(2, '0')}`;
                
                if (tiempoRestante <= 0) {
                    clearInterval(timer);
                    showAlert('El código ha expirado. Por favor solicite uno nuevo.', 'warning');
                    document.getElementById('btnReenviar').disabled = false;
                }
            }, 1000);
            
            // Timer para reenvío
            iniciarTimerReenvio();
            
            // Verificar mensajes de error en URL
            const urlParams = new URLSearchParams(window.location.search);
            const errorMsg = urlParams.get('error');
            
            if (errorMsg) {
                showAlert(decodeURIComponent(errorMsg), 'danger');
            }
        });
        
        function actualizarCodigoCompleto() {
            const inputs = document.querySelectorAll('.code-input input');
            const codigo = Array.from(inputs).map(inp => inp.value).join('');
            document.getElementById('codigoCompleto').value = codigo;
        }
        
        function reenviarCodigo() {
            const btnReenviar = document.getElementById('btnReenviar');
            btnReenviar.disabled = true;
            
            fetch('reenviar-codigo-2fa.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Código reenviado exitosamente', 'success');
                    iniciarTimerReenvio();
                } else {
                    showAlert(data.message || 'Error al reenviar código', 'danger');
                    btnReenviar.disabled = false;
                }
            })
            .catch(error => {
                showAlert('Error al reenviar código', 'danger');
                btnReenviar.disabled = false;
            });
        }
        
        function iniciarTimerReenvio() {
            const btnReenviar = document.getElementById('btnReenviar');
            const resendTimer = document.getElementById('resendTimer');
            const resendCountdown = document.getElementById('resendCountdown');
            
            let tiempoReenvio = 60;
            btnReenviar.style.display = 'none';
            resendTimer.style.display = 'block';
            
            const timerReenvio = setInterval(() => {
                tiempoReenvio--;
                resendCountdown.textContent = tiempoReenvio;
                
                if (tiempoReenvio <= 0) {
                    clearInterval(timerReenvio);
                    btnReenviar.style.display = 'inline';
                    btnReenviar.disabled = false;
                    resendTimer.style.display = 'none';
                }
            }, 1000);
        }
    </script>
</body>
</html>