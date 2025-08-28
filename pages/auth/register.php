<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse - TechStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100 py-4">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-success text-white text-center py-3">
                        <h3 class="mb-0">
                            <i class="fas fa-laptop me-2"></i>TechStore
                        </h3>
                        <p class="mb-0">Crear Cuenta Nueva</p>
                    </div>
                    
                    <div class="card-body p-4">
                        <!-- Mensajes -->
                        <div id="errorMessage" class="alert alert-danger d-none">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span id="errorText"></span>
                        </div>
                        
                        <div id="successMessage" class="alert alert-success d-none">
                            <i class="fas fa-check-circle me-2"></i>
                            <span id="successText"></span>
                        </div>
                        
                        <form id="registerForm">
                            <input type="hidden" name="action" value="register">
                            <input type="hidden" name="ajax" value="1">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nombre" class="form-label">
                                        <i class="fas fa-user me-2"></i>Nombre *
                                    </label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required 
                                           placeholder="Tu nombre">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="apellido" class="form-label">
                                        <i class="fas fa-user me-2"></i>Apellido *
                                    </label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" required 
                                           placeholder="Tu apellido">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Correo Electrónico *
                                </label>
                                <input type="email" class="form-control" id="email" name="email" required 
                                       placeholder="tu@email.com">
                                <div class="form-text">Usaremos este email para enviarte confirmaciones de pedidos.</div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Contraseña *
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" 
                                               required placeholder="Mínimo 6 caracteres">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="confirmPassword" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Confirmar Contraseña *
                                    </label>
                                    <input type="password" class="form-control" id="confirmPassword" 
                                           required placeholder="Repetir contraseña">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="telefono" class="form-label">
                                    <i class="fas fa-phone me-2"></i>Teléfono
                                </label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" 
                                       placeholder="2222-3333 o 8888-9999">
                                <div class="form-text">Opcional - Para contacto sobre pedidos</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="direccion" class="form-label">
                                    <i class="fas fa-map-marker-alt me-2"></i>Dirección
                                </label>
                                <textarea class="form-control" id="direccion" name="direccion" rows="2" 
                                          placeholder="Dirección completa para envíos"></textarea>
                                <div class="form-text">Opcional - Puedes agregarla después</div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    Acepto los <a href="#" target="_blank">términos y condiciones</a> y 
                                    <a href="#" target="_blank">política de privacidad</a> *
                                </label>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="newsletter">
                                <label class="form-check-label" for="newsletter">
                                    Deseo recibir ofertas y noticias por correo electrónico
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100 mb-3" id="registerBtn">
                                <i class="fas fa-user-plus me-2"></i>Crear Cuenta
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-0">¿Ya tienes cuenta?</p>
                            <a href="login.php" class="btn btn-link">
                                <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                            </a>
                        </div>
                        
                        <hr>
                        
                        <div class="text-center">
                            <a href="../../index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-home me-2"></i>Volver al Inicio
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Toggle password visibility
            $('#togglePassword').on('click', function() {
                const password = $('#password');
                const icon = $(this).find('i');
                
                if (password.attr('type') === 'password') {
                    password.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    password.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
            
            // Password confirmation validation
            $('#confirmPassword').on('input', function() {
                const password = $('#password').val();
                const confirmPassword = $(this).val();
                
                if (confirmPassword && password !== confirmPassword) {
                    $(this).addClass('is-invalid');
                    $(this).next('.invalid-feedback').remove();
                    $(this).after('<div class="invalid-feedback">Las contraseñas no coinciden</div>');
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).next('.invalid-feedback').remove();
                }
            });
            
            // Email validation
            $('#email').on('blur', function() {
                const email = $(this).val();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (email && !emailRegex.test(email)) {
                    $(this).addClass('is-invalid');
                    $(this).next('.invalid-feedback').remove();
                    $(this).after('<div class="invalid-feedback">Formato de email inválido</div>');
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).next('.invalid-feedback').remove();
                }
            });
            
            // Password strength indicator
            $('#password').on('input', function() {
                const password = $(this).val();
                let strength = 0;
                let message = '';
                
                if (password.length >= 6) strength++;
                if (password.length >= 8) strength++;
                if (/[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^A-Za-z0-9]/.test(password)) strength++;
                
                $(this).next('.password-strength').remove();
                
                if (password.length > 0) {
                    if (strength < 2) {
                        message = '<small class="text-danger password-strength">Contraseña débil</small>';
                    } else if (strength < 4) {
                        message = '<small class="text-warning password-strength">Contraseña regular</small>';
                    } else {
                        message = '<small class="text-success password-strength">Contraseña fuerte</small>';
                    }
                    $(this).parent().after(message);
                }
            });
            
            // Form submission
            $('#registerForm').on('submit', function(e) {
                e.preventDefault();
                
                const password = $('#password').val();
                const confirmPassword = $('#confirmPassword').val();
                
                // Validations
                if (password !== confirmPassword) {
                    $('#errorText').text('Las contraseñas no coinciden');
                    $('#errorMessage').removeClass('d-none');
                    return;
                }
                
                if (password.length < 6) {
                    $('#errorText').text('La contraseña debe tener al menos 6 caracteres');
                    $('#errorMessage').removeClass('d-none');
                    return;
                }
                
                const btn = $('#registerBtn');
                const originalText = btn.html();
                
                // Show loading
                btn.prop('disabled', true);
                btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Registrando...');
                
                // Hide messages
                $('#errorMessage, #successMessage').addClass('d-none');
                
                $.ajax({
                    url: '../../php/auth.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#successText').text(response.message);
                            $('#successMessage').removeClass('d-none');
                            
                            // Clear form
                            $('#registerForm')[0].reset();
                            
                            // Redirect after success
                            setTimeout(function() {
                                window.location.href = 'login.php?registered=1';
                            }, 2000);
                        } else {
                            $('#errorText').text(response.message);
                            $('#errorMessage').removeClass('d-none');
                        }
                    },
                    error: function() {
                        $('#errorText').text('Error de conexión. Por favor intenta de nuevo.');
                        $('#errorMessage').removeClass('d-none');
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                        btn.html(originalText);
                    }
                });
            });
            
            // Auto-focus on first field
            $('#nombre').focus();
        });
    </script>
</body>
</html>