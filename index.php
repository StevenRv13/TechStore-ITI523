<?php
session_start();
require_once 'config/database.php';

// Verificar si está logueado
$isLoggedIn = isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'];
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';

// Obtener contador de carrito si está logueado
$cartCount = 0;
if ($isLoggedIn) {
    require_once 'php/cart.php';
    $cartManager = new CartManager();
    $cartCount = $cartManager->getCartItemCount();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TechStore - Tu tienda de tecnología favorita. Los mejores productos tech al mejor precio.">
    <meta name="keywords" content="tecnología, electrónicos, smartphones, laptops, tablets, accesorios">
    <title>TechStore - Tu Tienda de Tecnología</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="bg-dark text-white sticky-top">
        <div class="container-fluid">
            <!-- Top Bar -->
            <div class="row bg-primary py-2">
                <div class="col-md-6">
                    <small>
                        <i class="fas fa-phone me-2"></i>+506 2222-3333
                        <i class="fas fa-envelope ms-3 me-2"></i>info@techstore.cr
                    </small>
                </div>
                <div class="col-md-6 text-md-end">
                    <small>
                        <?php if ($isLoggedIn): ?>
                            <span class="text-white me-3">
                                <i class="fas fa-user me-1"></i>Hola, <?php echo htmlspecialchars($userName); ?>
                            </span>
                            <a href="php/auth.php?action=logout" class="text-white text-decoration-none">
                                <i class="fas fa-sign-out-alt me-1"></i>Cerrar Sesión
                            </a>
                        <?php else: ?>
                            <a href="pages/auth/login.php" class="text-white text-decoration-none me-3">
                                <i class="fas fa-sign-in-alt me-1"></i>Iniciar Sesión
                            </a>
                            <a href="pages/auth/register.php" class="text-white text-decoration-none">
                                <i class="fas fa-user-plus me-1"></i>Registrarse
                            </a>
                        <?php endif; ?>
                    </small>
                </div>
            </div>
            
            <!-- Main Navigation -->
            <nav class="navbar navbar-expand-lg navbar-dark">
                <div class="container">
                    <!-- Logo -->
                    <a class="navbar-brand fw-bold fs-3" href="index.php">
                        <i class="fas fa-laptop me-2 text-primary"></i>TechStore
                    </a>
                    
                    <!-- Mobile Toggle -->
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    
                    <!-- Navigation Menu -->
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                            <li class="nav-item">
                                <a class="nav-link active" href="index.php">
                                    <i class="fas fa-home me-1"></i>Inicio
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-th-large me-1"></i>Categorías
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="pages/products/catalog.php?category=Smartphones">
                                        <i class="fas fa-mobile-alt me-2"></i>Smartphones
                                    </a></li>
                                    <li><a class="dropdown-item" href="pages/products/catalog.php?category=Laptops">
                                        <i class="fas fa-laptop me-2"></i>Laptops
                                    </a></li>
                                    <li><a class="dropdown-item" href="pages/products/catalog.php?category=Tablets">
                                        <i class="fas fa-tablet-alt me-2"></i>Tablets
                                    </a></li>
                                    <li><a class="dropdown-item" href="pages/products/catalog.php?category=Accesorios">
                                        <i class="fas fa-headphones me-2"></i>Accesorios
                                    </a></li>
                                </ul>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="pages/products/catalog.php">
                                    <i class="fas fa-search me-1"></i>Todos los Productos
                                </a>
                            </li>
                        </ul>
                        
                        <!-- Search Bar -->
                        <div class="d-flex me-3">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Buscar productos..." id="searchInput">
                                <button class="btn btn-primary" type="button" id="searchBtn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Cart -->
                        <?php if ($isLoggedIn): ?>
                            <a href="pages/cart/shopping-cart.php" class="btn btn-outline-primary position-relative">
                                <i class="fas fa-shopping-cart me-1"></i>Carrito
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cartCount">
                                    <?php echo $cartCount; ?>
                                </span>
                            </a>
                        <?php else: ?>
                            <a href="pages/auth/login.php" class="btn btn-outline-primary position-relative">
                                <i class="fas fa-shopping-cart me-1"></i>Carrito
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cartCount">
                                    0
                                </span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section bg-gradient-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">La Mejor Tecnología a Tu Alcance</h1>
                    <p class="lead mb-4">Descubre los últimos smartphones, laptops, tablets y accesorios tech con los mejores precios de Costa Rica.</p>
                    <div class="d-flex gap-3">
                        <a href="pages/products/catalog.php" class="btn btn-light btn-lg">
                            <i class="fas fa-shopping-bag me-2"></i>Comprar Ahora
                        </a>
                        <a href="#productos-destacados" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-star me-2"></i>Ver Destacados
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="assets/images/banners/hero-tech.png" alt="Tecnología" class="img-fluid rounded-3" style="max-height: 400px;">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3 text-center">
                    <div class="feature-card p-4">
                        <i class="fas fa-shipping-fast fa-3x text-primary mb-3"></i>
                        <h5>Envío Gratis</h5>
                        <p class="text-muted">En compras mayores a ₡50,000</p>
                    </div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="feature-card p-4">
                        <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                        <h5>Garantía Extendida</h5>
                        <p class="text-muted">Hasta 2 años en productos seleccionados</p>
                    </div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="feature-card p-4">
                        <i class="fas fa-credit-card fa-3x text-info mb-3"></i>
                        <h5>Pago Seguro</h5>
                        <p class="text-muted">PayPal, tarjetas y transferencias</p>
                    </div>
                </div>
                <div class="col-md-3 text-center">
                    <div class="feature-card p-4">
                        <i class="fas fa-headset fa-3x text-warning mb-3"></i>
                        <h5>Soporte 24/7</h5>
                        <p class="text-muted">Atención al cliente especializada</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section id="productos-destacados" class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12 text-center">
                    <h2 class="display-5 fw-bold">Productos Destacados</h2>
                    <p class="lead text-muted">Los más vendidos y mejor valorados por nuestros clientes</p>
                </div>
            </div>
            
            <div class="row g-4" id="featuredProducts">
                <!-- Producto 1 -->
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 shadow-sm product-card">
                        <div class="position-relative">
                            <img src="assets/images/products/iphone-15.jpg" class="card-img-top" alt="iPhone 15 Pro" style="height: 250px; object-fit: cover;">
                            <span class="badge bg-danger position-absolute top-0 end-0 m-2">-15%</span>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">iPhone 15 Pro</h5>
                            <p class="card-text text-muted flex-grow-1">128GB - Titanio Natural</p>
                            <div class="mb-2">
                                <div class="stars">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star-half-alt text-warning"></i>
                                    <small class="text-muted ms-1">(4.5)</small>
                                </div>
                            </div>
                            <div class="price-section mb-3">
                                <span class="text-muted text-decoration-line-through">₡850,000</span>
                                <h4 class="text-primary fw-bold">₡722,500</h4>
                            </div>
                            <?php if ($isLoggedIn): ?>
                                <button class="btn btn-primary add-to-cart" data-id="1" data-name="iPhone 15 Pro" data-price="722500">
                                    <i class="fas fa-cart-plus me-2"></i>Agregar al Carrito
                                </button>
                            <?php else: ?>
                                <a href="pages/auth/login.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login para Comprar
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Producto 2 -->
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 shadow-sm product-card">
                        <img src="assets/images/products/macbook-air.jpg" class="card-img-top" alt="MacBook Air M2" style="height: 250px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">MacBook Air M2</h5>
                            <p class="card-text text-muted flex-grow-1">13" - 256GB SSD - 8GB RAM</p>
                            <div class="mb-2">
                                <div class="stars">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <small class="text-muted ms-1">(5.0)</small>
                                </div>
                            </div>
                            <div class="price-section mb-3">
                                <h4 class="text-primary fw-bold">₡1,299,000</h4>
                            </div>
                            <?php if ($isLoggedIn): ?>
                                <button class="btn btn-primary add-to-cart" data-id="2" data-name="MacBook Air M2" data-price="1299000">
                                    <i class="fas fa-cart-plus me-2"></i>Agregar al Carrito
                                </button>
                            <?php else: ?>
                                <a href="pages/auth/login.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login para Comprar
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Producto 3 -->
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 shadow-sm product-card">
                        <div class="position-relative">
                            <img src="assets/images/products/samsung-s24.jpg" class="card-img-top" alt="Samsung Galaxy S24" style="height: 250px; object-fit: cover;">
                            <span class="badge bg-success position-absolute top-0 end-0 m-2">Nuevo</span>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">Samsung Galaxy S24</h5>
                            <p class="card-text text-muted flex-grow-1">256GB - Phantom Black</p>
                            <div class="mb-2">
                                <div class="stars">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="far fa-star text-warning"></i>
                                    <small class="text-muted ms-1">(4.2)</small>
                                </div>
                            </div>
                            <div class="price-section mb-3">
                                <h4 class="text-primary fw-bold">₡695,000</h4>
                            </div>
                            <?php if ($isLoggedIn): ?>
                                <button class="btn btn-primary add-to-cart" data-id="3" data-name="Samsung Galaxy S24" data-price="695000">
                                    <i class="fas fa-cart-plus me-2"></i>Agregar al Carrito
                                </button>
                            <?php else: ?>
                                <a href="pages/auth/login.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login para Comprar
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Producto 4 -->
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 shadow-sm product-card">
                        <img src="assets/images/products/ipad-pro.jpg" class="card-img-top" alt="iPad Pro" style="height: 250px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">iPad Pro 11"</h5>
                            <p class="card-text text-muted flex-grow-1">128GB - Wi-Fi - Space Gray</p>
                            <div class="mb-2">
                                <div class="stars">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <small class="text-muted ms-1">(4.8)</small>
                                </div>
                            </div>
                            <div class="price-section mb-3">
                                <h4 class="text-primary fw-bold">₡899,000</h4>
                            </div>
                            <?php if ($isLoggedIn): ?>
                                <button class="btn btn-primary add-to-cart" data-id="4" data-name="iPad Pro 11" data-price="899000">
                                    <i class="fas fa-cart-plus me-2"></i>Agregar al Carrito
                                </button>
                            <?php else: ?>
                                <a href="pages/auth/login.php" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login para Comprar
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-5">
                <a href="pages/products/catalog.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-eye me-2"></i>Ver Todos los Productos
                </a>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="bg-dark text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h3 class="fw-bold">¡Mantente al día con TechStore!</h3>
                    <p class="mb-0">Recibe ofertas exclusivas y las últimas novedades en tecnología.</p>
                </div>
                <div class="col-lg-6">
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Tu correo electrónico" id="newsletterEmail">
                        <button class="btn btn-primary" type="button" id="newsletterBtn">
                            <i class="fas fa-paper-plane me-2"></i>Suscribirse
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-light py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-laptop me-2 text-primary"></i>TechStore
                    </h5>
                    <p class="text-muted">Tu tienda de tecnología de confianza en Costa Rica. Los mejores productos tech al mejor precio.</p>
                    <div class="social-links">
                        <a href="#" class="text-muted me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-muted me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-muted me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-muted"><i class="fab fa-whatsapp fa-lg"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="fw-bold mb-3">Categorías</h6>
                    <ul class="list-unstyled">
                        <li><a href="pages/products/catalog.php?category=Smartphones" class="text-muted text-decoration-none">Smartphones</a></li>
                        <li><a href="pages/products/catalog.php?category=Laptops" class="text-muted text-decoration-none">Laptops</a></li>
                        <li><a href="pages/products/catalog.php?category=Tablets" class="text-muted text-decoration-none">Tablets</a></li>
                        <li><a href="pages/products/catalog.php?category=Accesorios" class="text-muted text-decoration-none">Accesorios</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="fw-bold mb-3">Ayuda</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">Centro de Ayuda</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Política de Devoluciones</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Términos y Condiciones</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Privacidad</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h6 class="fw-bold mb-3">Mi Cuenta</h6>
                    <ul class="list-unstyled">
                        <?php if ($isLoggedIn): ?>
                            <li><a href="pages/cart/shopping-cart.php" class="text-muted text-decoration-none">Mi Carrito</a></li>
                            <li><a href="pages/user/orders.php" class="text-muted text-decoration-none">Mis Pedidos</a></li>
                            <li><a href="pages/user/profile-edit.php" class="text-muted text-decoration-none">Mi Perfil</a></li>
                            <li><a href="php/auth.php?action=logout" class="text-muted text-decoration-none">Cerrar Sesión</a></li>
                        <?php else: ?>
                            <li><a href="pages/auth/login.php" class="text-muted text-decoration-none">Iniciar Sesión</a></li>
                            <li><a href="pages/auth/register.php" class="text-muted text-decoration-none">Registrarse</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h6 class="fw-bold mb-3">Contacto</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                            <small>San José, Costa Rica</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone me-2 text-primary"></i>
                            <small>+506 2222-3333</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2 text-primary"></i>
                            <small>info@techstore.cr</small>
                        </li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small class="text-muted">&copy; 2025 TechStore. Todos los derechos reservados.</small>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">Desarrollado para ITI-523 - Universidad Técnica Nacional</small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>

    <script>
        // Agregar funcionalidad AJAX para carrito
        $(document).on('click', '.add-to-cart', function(e) {
            e.preventDefault();
            
            const productId = $(this).data('id');
            const productName = $(this).data('name');
            const productPrice = $(this).data('price');
            const button = $(this);
            
            // Mostrar loading
            button.prop('disabled', true);
            const originalText = button.html();
            button.html('<i class="fas fa-spinner fa-spin me-2"></i>Agregando...');
            
            $.ajax({
                url: 'php/cart.php',
                method: 'POST',
                data: {
                    action: 'add_to_cart',
                    product_id: productId,
                    quantity: 1
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Actualizar contador
                        $('#cartCount').text(parseInt($('#cartCount').text()) + 1);
                        
                        // Mostrar éxito
                        button.removeClass('btn-primary').addClass('btn-success');
                        button.html('<i class="fas fa-check me-2"></i>Agregado');
                        
                        // Mostrar notificación
                        if (typeof showNotification === 'function') {
                            showNotification(productName + ' agregado al carrito', 'success');
                        }
                        
                        // Restaurar botón después de 2 segundos
                        setTimeout(() => {
                            button.removeClass('btn-success').addClass('btn-primary');
                            button.html(originalText);
                            button.prop('disabled', false);
                        }, 2000);
                    } else {
                        alert('Error: ' + response.message);
                        button.prop('disabled', false);
                        button.html(originalText);
                    }
                },
                error: function() {
                    alert('Error de conexión');
                    button.prop('disabled', false);
                    button.html(originalText);
                }
            });
        });
    </script>
</body>
</html>