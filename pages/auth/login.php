<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - TechStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h3 class="mb-0">
                            <i class="fas fa-laptop me-2"></i>TechStore
                        </h3>
                        <p class="mb-0">Iniciar Sesión</p>
                    </div>
                    
                    <div class="card-body p-4">
                        <!-- Mensaje de éxito si viene de registro -->
                        <?php if (isset($_GET['registered'])): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                ¡Registro exitoso! Ahora puedes iniciar sesión.
                            </div>
                        <?php endif; ?>
                        
                        <!-- Mensaje de error -->
                        <div id="errorMessage" class="alert alert-danger d-none">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <span id="errorText"></span>
                        </div>
                        
                        <!-- Mensaje de éxito -->
                        <div id="successMessage" class="alert alert-success d-none">
                            <i class="fas fa-check-circle me-2"></i>
                            <span id="successText"></span>
                        </div>
                        
                        <form id="loginForm">
                            <input type="hidden" name="action" value="login">
                            <input type="hidden" name="ajax" value="1">
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Correo Electrónico
                                </label>
                                <input type="email" class="form-control" id="email" name="email" required 
                                       placeholder="tu@email.com">
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Contraseña
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" 
                                           required placeholder="Tu contraseña">
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">
                                    Recordarme
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3" id="loginBtn">
                                <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-0">¿No tienes cuenta?</p>
                            <a href="register.php" class="btn btn-link">
                                <i class="fas fa-user-plus me-2"></i>Registrarse
                            </a>
                        </div>
                        
                        <hr>
                        
                        <div class="text-center">
                            <a href="../../index.php" class="btn btn-outline-secondary">
                                <i class="fas fa-home me-2"></i>Volver al Inicio
                            </a>
                        </div>
                        
                        <!-- Usuarios de prueba -->
                        <div class="mt-4">
                            <small class="text-muted">
                                <strong>Para pruebas:</strong><br>
                                Email: test@techstore.cr<br>
                                Contraseña: 123456
                            </small>
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
            
            // Form submission
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                const btn = $('#loginBtn');
                const originalText = btn.html();
                
                // Show loading
                btn.prop('disabled', true);
                btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Iniciando...');
                
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
                            
                            // Redirect after success
                            setTimeout(function() {
                                window.location.href = '../../index.php';
                            }, 1500);
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
            
            // Auto-focus on email field
            $('#email').focus();
        });
    </script>
</body>
</html>