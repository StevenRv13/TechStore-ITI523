<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar login
if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../../config/database.php';

// Obtener productos del carrito desde base de datos
$userId = $_SESSION['user_id'];
$cartItems = [];
$total = 0;

try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT c.id as cart_id, c.cantidad, p.id, p.nombre, p.precio, p.imagen, p.stock,
               (c.cantidad * p.precio) as subtotal
        FROM carrito c
        JOIN productos p ON c.producto_id = p.id
        WHERE c.usuario_id = ? AND p.activo = 1
    ");
    $stmt->execute([$userId]);
    $cartItems = $stmt->fetchAll();
    
    foreach ($cartItems as $item) {
        $total += $item['subtotal'];
    }
} catch (Exception $e) {
    $error = "Error al cargar el carrito: " . $e->getMessage();
}

$impuestos = $total * 0.13;
$envio = $total >= 50000 ? 0 : 2500;
$totalFinal = $total + $impuestos + $envio;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - TechStore</title>
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
                        <h4><i class="fas fa-laptop me-2 text-primary"></i>TechStore</h4>
                    </a>
                </div>
                <div class="col-md-6 text-end">
                    <span class="me-3">Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="../../index.php" class="btn btn-outline-light btn-sm">Inicio</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container my-4">
        <h2><i class="fas fa-shopping-cart me-2"></i>Mi Carrito de Compras</h2>
        
        <?php if (empty($cartItems)): ?>
            <!-- Carrito vacío -->
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
                <h3>Tu carrito está vacío</h3>
                <a href="../products/catalog.php" class="btn btn-primary">Ir de Compras</a>
            </div>
        <?php else: ?>
            <div class="row">
                <!-- Productos -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="row align-items-center py-3 border-bottom">
                                    <div class="col-md-2">
                                        <img src="../../assets/images/products/<?php echo $item['imagen']; ?>" 
                                             class="img-fluid" alt="<?php echo $item['nombre']; ?>" 
                                             style="height: 80px; object-fit: cover;">
                                    </div>
                                    <div class="col-md-4">
                                        <h6><?php echo htmlspecialchars($item['nombre']); ?></h6>
                                        <small class="text-muted">₡<?php echo number_format($item['precio']); ?> c/u</small>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="input-group input-group-sm">
                                            <button class="btn btn-outline-secondary" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, <?php echo $item['cantidad'] - 1; ?>)">-</button>
                                            <input type="text" class="form-control text-center" value="<?php echo $item['cantidad']; ?>" readonly>
                                            <button class="btn btn-outline-secondary" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, <?php echo $item['cantidad'] + 1; ?>)">+</button>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <strong>₡<?php echo number_format($item['subtotal']); ?></strong>
                                    </div>
                                    <div class="col-md-2">
                                        <button class="btn btn-outline-danger btn-sm" onclick="removeItem(<?php echo $item['cart_id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Resumen -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Resumen del Pedido</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <span>Subtotal:</span>
                                <span>₡<?php echo number_format($total); ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>IVA (13%):</span>
                                <span>₡<?php echo number_format($impuestos); ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Envío:</span>
                                <span><?php echo $envio == 0 ? 'GRATIS' : '₡' . number_format($envio); ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Total:</strong>
                                <strong class="text-primary">₡<?php echo number_format($totalFinal); ?></strong>
                            </div>
                            
                            <button class="btn btn-success w-100 mt-3" onclick="checkout()">
                                <i class="fas fa-credit-card me-2"></i>Proceder al Pago
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <script>
        function updateQuantity(cartId, newQuantity) {
            if (newQuantity < 1) {
                removeItem(cartId);
                return;
            }
            
            $.post('../../php/cart.php', {
                action: 'update_quantity',
                cart_item_id: cartId,
                quantity: newQuantity
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }, 'json');
        }
        
        function removeItem(cartId) {
            if (confirm('¿Eliminar este producto?')) {
                $.post('../../php/cart.php', {
                    action: 'remove_item',
                    cart_item_id: cartId
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                }, 'json');
            }
        }
        
        function checkout() {
            window.location.href = 'checkout.php';
        }
    </script>
</body>
</html>