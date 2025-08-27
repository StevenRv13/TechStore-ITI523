/**
 * TECHSTORE - JavaScript Principal
 * Funcionalidades principales de la tienda virtual
 * Proyecto: ITI-523 - Tecnologías y Sistemas Web II
 */

// ================================
// VARIABLES GLOBALES
// ================================
let cart = [];
let products = [];

// ================================
// INICIALIZACIÓN DE LA APLICACIÓN
// ================================
$(document).ready(function() {
    console.log('TechStore cargado correctamente');
    
    // Inicializar funcionalidades
    initializeApp();
    loadCart();
    updateCartCount();
    
    // Event listeners
    setupEventListeners();
    
    // Cargar productos destacados
    loadFeaturedProducts();
    
    // Animaciones de entrada
    animateOnScroll();
});

// ================================
// CONFIGURACIÓN INICIAL
// ================================
function initializeApp() {
    // Configurar tooltips de Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Configurar popovers de Bootstrap
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    console.log('Aplicación inicializada correctamente');
}

// ================================
// EVENT LISTENERS
// ================================
function setupEventListeners() {
    // Botón de búsqueda
    $('#searchBtn').on('click', handleSearch);
    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            handleSearch();
        }
    });
    
    // Botones de agregar al carrito
    $(document).on('click', '.add-to-cart', handleAddToCart);
    
    // Newsletter
    $('#newsletterBtn').on('click', handleNewsletter);
    $('#newsletterEmail').on('keypress', function(e) {
        if (e.which === 13) {
            handleNewsletter();
        }
    });
    
    // Smooth scroll para enlaces internos
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $($(this).attr('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 80
            }, 800);
        }
    });
    
    console.log('Event listeners configurados');
}

// ================================
// GESTIÓN DEL CARRITO
// ================================

/**
 * Cargar carrito desde localStorage
 */
function loadCart() {
    try {
        const savedCart = localStorage.getItem('techstore_cart');
        if (savedCart) {
            cart = JSON.parse(savedCart);
            console.log('Carrito cargado:', cart);
        }
    } catch (error) {
        console.error('Error al cargar el carrito:', error);
        cart = [];
    }
}

/**
 * Guardar carrito en localStorage
 */
function saveCart() {
    try {
        localStorage.setItem('techstore_cart', JSON.stringify(cart));
        console.log('Carrito guardado correctamente');
    } catch (error) {
        console.error('Error al guardar el carrito:', error);
    }
}

/**
 * Agregar producto al carrito
 */
function addToCart(productId, productName, productPrice) {
    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: productId,
            name: productName,
            price: parseFloat(productPrice),
            quantity: 1,
            dateAdded: new Date().toISOString()
        });
    }
    
    saveCart();
    updateCartCount();
    showNotification(`${productName} agregado al carrito`, 'success');
    
    console.log('Producto agregado al carrito:', productName);
}

/**
 * Actualizar contador del carrito
 */
function updateCartCount() {
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    $('#cartCount').text(totalItems);
    
    // Animar el contador si hay cambios
    if (totalItems > 0) {
        $('#cartCount').addClass('animate__animated animate__pulse');
        setTimeout(() => {
            $('#cartCount').removeClass('animate__animated animate__pulse');
        }, 1000);
    }
}

/**
 * Obtener total del carrito
 */
function getCartTotal() {
    return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
}

// ================================
// MANEJO DE EVENTOS
// ================================

/**
 * Manejar agregar producto al carrito
 */
function handleAddToCart(e) {
    e.preventDefault();
    
    const button = $(this);
    const productId = button.data('id');
    const productName = button.data('name');
    const productPrice = button.data('price');
    
    // Animación del botón
    button.addClass('loading');
    button.prop('disabled', true);
    
    // Simular tiempo de procesamiento
    setTimeout(() => {
        addToCart(productId, productName, productPrice);
        
        // Restaurar botón
        button.removeClass('loading');
        button.prop('disabled', false);
        
        // Efecto visual
        button.addClass('btn-success');
        button.html('<i class="fas fa-check me-2"></i>Agregado');
        
        setTimeout(() => {
            button.removeClass('btn-success');
            button.html('<i class="fas fa-cart-plus me-2"></i>Agregar al Carrito');
        }, 2000);
        
    }, 500);
}

/**
 * Manejar búsqueda de productos
 */
function handleSearch() {
    const searchTerm = $('#searchInput').val().trim();
    
    if (searchTerm.length < 2) {
        showNotification('Por favor ingresa al menos 2 caracteres para buscar', 'warning');
        return;
    }
    
    // Mostrar indicador de carga
    $('#searchBtn').addClass('loading');
    
    // Simular búsqueda
    setTimeout(() => {
        console.log('Buscando:', searchTerm);
        
        // Redirigir a página de catálogo con parámetro de búsqueda
        window.location.href = `pages/products/catalog.html?search=${encodeURIComponent(searchTerm)}`;
        
        $('#searchBtn').removeClass('loading');
    }, 800);
}

/**
 * Manejar suscripción al newsletter
 */
function handleNewsletter() {
    const email = $('#newsletterEmail').val().trim();
    
    if (!isValidEmail(email)) {
        showNotification('Por favor ingresa un correo electrónico válido', 'error');
        return;
    }
    
    // Mostrar indicador de carga
    $('#newsletterBtn').addClass('loading');
    $('#newsletterBtn').prop('disabled', true);
    
    // Simular suscripción
    setTimeout(() => {
        console.log('Suscribiendo email:', email);
        
        showNotification('¡Gracias por suscribirte! Pronto recibirás nuestras ofertas.', 'success');
        $('#newsletterEmail').val('');
        
        $('#newsletterBtn').removeClass('loading');
        $('#newsletterBtn').prop('disabled', false);
        
        // Guardar en localStorage para futuro uso
        try {
            localStorage.setItem('techstore_newsletter', email);
        } catch (error) {
            console.error('Error al guardar email:', error);
        }
        
    }, 1000);
}

// ================================
// PRODUCTOS Y CATÁLOGO
// ================================

/**
 * Cargar productos destacados
 */
function loadFeaturedProducts() {
    // Simular productos destacados (en versión real vendría del backend)
    products = [
        {
            id: 1,
            name: 'iPhone 15 Pro',
            price: 722500,
            originalPrice: 850000,
            image: 'assets/images/products/iphone-15.jpg',
            category: 'smartphones',
            rating: 4.5,
            inStock: true,
            featured: true
        },
        {
            id: 2,
            name: 'MacBook Air M2',
            price: 1299000,
            image: 'assets/images/products/macbook-air.jpg',
            category: 'laptops',
            rating: 5.0,
            inStock: true,
            featured: true
        },
        {
            id: 3,
            name: 'Samsung Galaxy S24',
            price: 695000,
            image: 'assets/images/products/samsung-s24.jpg',
            category: 'smartphones',
            rating: 4.2,
            inStock: true,
            featured: true,
            isNew: true
        },
        {
            id: 4,
            name: 'iPad Pro 11"',
            price: 899000,
            image: 'assets/images/products/ipad-pro.jpg',
            category: 'tablets',
            rating: 4.8,
            inStock: true,
            featured: true
        }
    ];
    
    console.log('Productos destacados cargados:', products.length);
}

// ================================
// UTILIDADES
// ================================

/**
 * Validar email
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Formatear precio en colones
 */
function formatPrice(price) {
    return new Intl.NumberFormat('es-CR', {
        style: 'currency',
        currency: 'CRC',
        minimumFractionDigits: 0
    }).format(price);
}

/**
 * Mostrar notificación
 */
function showNotification(message, type = 'info') {
    // Crear elemento de notificación
    const notification = $(`
        <div class="alert alert-${type} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
            <i class="fas fa-${getIconForType(type)} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    // Agregar al body
    $('body').append(notification);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        notification.alert('close');
    }, 5000);
    
    console.log(`Notificación (${type}):`, message);
}

/**
 * Obtener icono según tipo de notificación
 */
function getIconForType(type) {
    const icons = {
        'success': 'check-circle',
        'error': 'exclamation-triangle',
        'warning': 'exclamation-circle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// ================================
// ANIMACIONES
// ================================

/**
 * Animar elementos al hacer scroll
 */
function animateOnScroll() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    // Observar elementos
    document.querySelectorAll('.product-card, .feature-card').forEach(el => {
        observer.observe(el);
    });
}

// ================================
// MANEJO DE ERRORES GLOBALES
// ================================
window.addEventListener('error', function(e) {
    console.error('Error global capturado:', e.error);
    showNotification('Ha ocurrido un error. Por favor recarga la página.', 'error');
});

// ================================
// FUNCIONES DISPONIBLES GLOBALMENTE
// ================================
window.TechStore = {
    cart,
    addToCart,
    updateCartCount,
    formatPrice,
    showNotification,
    products
};

console.log('TechStore JavaScript cargado completamente');