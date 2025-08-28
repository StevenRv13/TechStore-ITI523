<?php
session_start();
require_once '../../config/database.php';

// Parámetros de búsqueda y filtros
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'nombre';

// Construir consulta SQL
$sql = "SELECT p.*, c.nombre as categoria_nombre FROM productos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        WHERE p.activo = 1";
$params = [];

if ($search) {
    $sql .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category) {
    $sql .= " AND c.nombre = ?";
    $params[] = $category;
}

if ($min_price) {
    $sql .= " AND p.precio >= ?";
    $params[] = $min_price;
}

if ($max_price) {
    $sql .= " AND p.precio <= ?";
    $params[] = $max_price;
}

// Ordenamiento
switch ($sort) {
    case 'precio_asc':
        $sql .= " ORDER BY p.precio ASC";
        break;
    case 'precio_desc':
        $sql .= " ORDER BY p.precio DESC";
        break;
    case 'nombre':
    default:
        $sql .= " ORDER BY p.nombre ASC";
        break;
}

try {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $productos = $stmt->fetchAll();
    
    // Obtener categorías para filtros
    $categorias_stmt = $db->query("SELECT DISTINCT nombre FROM categorias WHERE activo = 1");
    $categorias = $categorias_stmt->fetchAll();
    
} catch (Exception $e) {
    $productos = [];
    $categorias = [];
    $error = "Error al cargar productos: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - TechStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Header simplificado -->
    <header class="bg-dark text-white py-3">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <a href="../../index.php" class="text-white text-decoration-none">
                        <h4 class="mb-0">
                            <i class="fas fa-laptop me-2 text-primary"></i>TechStore
                        </h4>
                    </a>
                </div>
                <div class="col-md-6 text-end">
                    <?php if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in']): ?>
                        <span class="me-3">Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <a href="../cart/shopping-cart.php" class="btn btn-primary btn-sm me-2">
                            <i class="fas fa-shopping-cart"></i> Carrito
                        </a>
                        <a href="../../php/auth.php?action=logout" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-sign-out-alt"></i> Salir
                        </a>
                    <?php else: ?>
                        <a href="../auth/login.php" class="btn btn-outline-light btn-sm me-2">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="../auth/register.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-plus"></i> Registro
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <div class="container my-4">
        <div class="row">
            <!-- Filtros -->
            <div class="col-lg-3 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-filter me-2"></i>Filtros
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="filterForm" method="GET">
                            <!-- Búsqueda -->
                            <div class="mb-3">
                                <label class="form-label">Buscar producto</label>
                                <input type="text" class="form-control" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Nombre del producto...">
                            </div>
                            
                            <!-- Categorías -->
                            <div class="mb-3">
                                <label class="form-label">Categoría</label>
                                <select class="form-select" name="category">
                                    <option value="">Todas las categorías</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?php echo htmlspecialchars($cat['nombre']); ?>" 
                                                <?php echo $category === $cat['nombre'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Rango de precios -->
                            <div class="mb-3">
                                <label class="form-label">Precio mínimo (₡)</label>
                                <input type="number" class="form-control" name="min_price" 
                                       value="<?php echo htmlspecialchars($min_price); ?>" 
                                       placeholder="0">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Precio máximo (₡)</label>
                                <input type="number" class="form-control" name="max_price" 
                                       value="<?php echo htmlspecialchars($max_price); ?>" 
                                       placeholder="999999">
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                            
                            <a href="catalog.php" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-2"></i>Limpiar
                            </a>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Productos -->
            <div class="col-lg-9">
                <!-- Header del catálogo -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2>Catálogo de Productos</h2>
                        <p class="text-muted mb-0">
                            <?php echo count($productos); ?> productos encontrados
                            <?php if ($search): ?>
                                para "<?php echo htmlspecialchars($search); ?>"
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <!-- Ordenamiento -->
                    <div>
                        <form method="GET" class="d-inline-block">
                            <?php foreach (['search', 'category', 'min_price', 'max_price'] as $param): ?>
                                <?php if (!empty($_GET[$param])): ?>
                                    <input type="hidden" name="<?php echo $param; ?>" value="<?php echo htmlspecialchars($_GET[$param]); ?>">
                                <?php endif; ?>
                            <?php endforeach; ?>
                            
                            <select name="sort" class="form-select" onchange="this.form.submit()">
                                <option value="nombre" <?php echo $sort === 'nombre' ? 'selected' : ''; ?>>
                                    Ordenar por nombre
                                </option>
                                <option value="precio_asc" <?php echo $sort === 'precio_asc' ? 'selected' : ''; ?>>
                                    Precio: menor a mayor
                                </option>
                                <option value="precio_desc" <?php echo $sort === 'precio_desc' ? 'selected' : ''; ?>>
                                    Precio: mayor a menor
                                </option>
                            </select>
                        </form>
                    </div>
                </div>
                
                <!-- Productos grid -->
                <?php if (!empty($productos)): ?>
                    <div class="row g-4">
                        <?php foreach ($productos as $producto): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 product-card">
                                    <img src="../../assets/images/products/<?php echo htmlspecialchars($producto['imagen']); ?>" 
                                         class="card-img-top" alt="<?php echo htmlspecialchars($producto['nombre']); ?>" 
                                         style="height: 250px; object-fit: cover;">
                                    
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                                        <p class="card-text text-muted flex-grow-1">
                                            <?php echo htmlspecialchars($producto['descripcion']); ?>
                                        </p>
                                        
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-tag me-1"></i>
                                                <?php echo htmlspecialchars($producto['categoria_nombre']); ?>
                                            </small>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-boxes me-1"></i>
                                                Stock: <?php echo $producto['stock']; ?>
                                            </small>
                                        </div>
                                        
                                        <div class="price-section mb-3">
                                            <h4 class="text-primary fw-bold">
                                                ₡<?php echo number_format($producto['precio'], 0, ',', '.'); ?>
                                            </h4>
                                        </div>
                                        
                                        <?php if ($producto['stock'] > 0): ?>
                                            <button class="btn btn-primary add-to-cart" 
                                                    data-id="<?php echo $producto['id']; ?>" 
                                                    data-name="<?php echo htmlspecialchars($producto['nombre']); ?>" 
                                                    data-price="<?php echo $producto['precio']; ?>">
                                                <i class="fas fa-cart-plus me-2"></i>Agregar al Carrito
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-secondary" disabled>
                                                <i class="fas fa-times me-2"></i>Sin Stock
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4>No se encontraron productos</h4>
                        <p class="text-muted">Intenta ajustar los filtros de búsqueda</p>
                        <a href="catalog.php" class="btn btn-primary">Ver todos los productos</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../../assets/js/main.js"></script>
    
    <<script>
$(document).ready(function() {
    // Agregar al carrito
    $('.add-to-cart').on('click', function(e) {
        e.preventDefault();
        
        const productId = $(this).data('id');
        const productName = $(this).data('name');
        const button = $(this);
        
        console.log('Agregando producto:', productId, productName); // Debug
        
        button.prop('disabled', true);
        button.html('<i class="fas fa-spinner fa-spin"></i> Agregando...');
        
        $.ajax({
            url: '../../php/cart.php',
            type: 'POST',
            data: {
                action: 'add_to_cart',
                product_id: productId,
                quantity: 1
            },
            dataType: 'json',
            success: function(response) {
                console.log('Respuesta:', response); // Debug
                if (response.success) {
                    alert('✅ Producto agregado al carrito: ' + productName);
                } else {
                    alert('❌ Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX:', xhr.responseText);
                alert('Error de conexión: ' + error);
            },
            complete: function() {
                button.prop('disabled', false);
                button.html('<i class="fas fa-cart-plus me-2"></i>Agregar al Carrito');
            }
        });
    });
});
</script>
</body>
</html>