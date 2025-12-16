<?php
session_start();
require_once "../servicios/conexion.php";

$token = $_GET['token'] ?? '';
$tokenValido = false;
$mensaje = '';

if ($token) {
    $conexion = ConectarDB();
    $stmt = $conexion->prepare("
        SELECT pr.*, u.nombre, u.apellido, u.correo 
        FROM password_resets pr
        JOIN usuarios u ON pr.usuario_id = u.id_usuarios
        WHERE pr.token = ? AND pr.expira_en > NOW() AND pr.usado = 0
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $tokenValido = true;
        $datos = $result->fetch_assoc();
    } else {
        $mensaje = 'El enlace de recuperación es inválido o ha expirado.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - ARCO</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/login-pure.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .password-strength {
            margin-top: 0.5rem;
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            transition: all 0.3s;
            width: 0%;
        }
        
        .strength-weak { background: #ef4444; width: 33%; }
        .strength-medium { background: #f59e0b; width: 66%; }
        .strength-strong { background: #10b981; width: 100%; }
        
        .password-requirements {
            font-size: 0.85rem;
            color: var(--texto-secundario);
            margin-top: 0.5rem;
        }
        
        .requirement {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0.25rem 0;
        }
        
        .requirement i {
            font-size: 0.75rem;
        }
        
        .requirement.met {
            color: var(--color-correcto);
        }
    </style>
</head>
<body>
    <div class="cont-auten">
        <div class="img-auten">
            <div>
                <h2>ARCO</h2>
                <p>Sistema de Gestión de Inventarios</p>
                <img src="../componentes/img/logo2.png" alt="Logo ARCO" style="max-width: 150px;">
            </div>
        </div>
        
        <div class="form-auten">
            <div class="recovery-container" style="max-width: 500px; margin: 0 auto; padding: 2rem;">
                <div class="recovery-header" style="text-align: center; margin-bottom: 2rem;">
                    <i class="fas fa-lock" style="font-size: 48px; color: var(--color-primario); margin-bottom: 1rem;"></i>
                    <h2 style="color: var(--texto); margin-bottom: 0.5rem;">Restablecer Contraseña</h2>
                    <?php if ($tokenValido): ?>
                        <p style="color: var(--texto-secundario); font-size: 0.95rem;">
                            Hola, <?= htmlspecialchars($datos['nombre']) ?>. Ingresa tu nueva contraseña
                        </p>
                    <?php endif; ?>
                </div>
                
                <div id="alertContainer"></div>
                
                <?php if (!$tokenValido): ?>
                    <div class="alerta alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($mensaje) ?>
                    </div>
                    <div class="text-center mt-4">
                        <a href="recuperar-contra.php" class="btn btn-primary">
                            <i class="fas fa-redo"></i> Solicitar Nuevo Enlace
                        </a>
                    </div>
                <?php else: ?>
                    <form id="formRestablecer" action="../servicios/procesar_restablecer.php" method="POST">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                        
                        <div class="inputs-login">
                            <label for="nueva_contrasena" class="txt-form">Nueva Contraseña</label>
                            <div class="password-toggle">
                                <input type="password" class="input-form" id="nueva_contrasena" name="nueva_contrasena" 
                                       minlength="8" required>
                                <i class="toggle-icon fas fa-eye" onclick="togglePassword('nueva_contrasena', this)"></i>
                            </div>
                            <div class="password-strength">
                                <div id="strengthBar" class="password-strength-bar"></div>
                            </div>
                            <div class="password-requirements">
                                <div class="requirement" id="req-length">
                                    <i class="fas fa-circle"></i>
                                    <span>Mínimo 8 caracteres</span>
                                </div>
                                <div class="requirement" id="req-uppercase">
                                    <i class="fas fa-circle"></i>
                                    <span>Una letra mayúscula</span>
                                </div>
                                <div class="requirement" id="req-lowercase">
                                    <i class="fas fa-circle"></i>
                                    <span>Una letra minúscula</span>
                                </div>
                                <div class="requirement" id="req-number">
                                    <i class="fas fa-circle"></i>
                                    <span>Un número</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="inputs-login">
                            <label for="confirmar_contrasena" class="txt-form">Confirmar Contraseña</label>
                            <div class="password-toggle">
                                <input type="password" class="input-form" id="confirmar_contrasena" name="confirmar_contrasena" 
                                       minlength="8" required>
                                <i class="toggle-icon fas fa-eye" onclick="togglePassword('confirmar_contrasena', this)"></i>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Restablecer Contraseña
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
                
                <div class="text-center mt-3">
                    <a href="../login.html" class="txt-olvidado">
                        <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        function showAlert(message, type = 'danger') {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alerta alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                <i class="fas fa-${type === 'danger' ? 'exclamation-circle' : 'check-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()">×</button>
            `;
            alertContainer.appendChild(alert);
        }
        
        // Validación de fortaleza de contraseña
        const passwordInput = document.getElementById('nueva_contrasena');
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const strengthBar = document.getElementById('strengthBar');
                
                // Verificar requisitos
                const hasLength = password.length >= 8;
                const hasUppercase = /[A-Z]/.test(password);
                const hasLowercase = /[a-z]/.test(password);
                const hasNumber = /[0-9]/.test(password);
                
                // Actualizar indicadores visuales
                document.getElementById('req-length').classList.toggle('met', hasLength);
                document.getElementById('req-uppercase').classList.toggle('met', hasUppercase);
                document.getElementById('req-lowercase').classList.toggle('met', hasLowercase);
                document.getElementById('req-number').classList.toggle('met', hasNumber);
                
                // Calcular fortaleza
                const metRequirements = [hasLength, hasUppercase, hasLowercase, hasNumber].filter(Boolean).length;
                
                strengthBar.className = 'password-strength-bar';
                if (metRequirements <= 2) {
                    strengthBar.classList.add('strength-weak');
                } else if (metRequirements === 3) {
                    strengthBar.classList.add('strength-medium');
                } else if (metRequirements === 4) {
                    strengthBar.classList.add('strength-strong');
                }
            });
        }
        
        // Validación del formulario
        const form = document.getElementById('formRestablecer');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const password = document.getElementById('nueva_contrasena').value;
                const confirm = document.getElementById('confirmar_contrasena').value;
                
                if (password !== confirm) {
                    showAlert('Las contraseñas no coinciden', 'danger');
                    return;
                }
                
                if (password.length < 8) {
                    showAlert('La contraseña debe tener al menos 8 caracteres', 'danger');
                    return;
                }
                
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                
                const formData = new FormData(this);
                
                fetch('../servicios/procesar_restablecer.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        setTimeout(() => {
                            window.location.href = '../login.html?success=' + encodeURIComponent('Contraseña restablecida exitosamente');
                        }, 2000);
                    } else {
                        showAlert(data.message, 'danger');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-check"></i> Restablecer Contraseña';
                    }
                })
                .catch(error => {
                    showAlert('Error de conexión. Intente nuevamente.', 'danger');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-check"></i> Restablecer Contraseña';
                });
            });
        }
    </script>
</body>
</html>