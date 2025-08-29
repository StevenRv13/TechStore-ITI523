
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../../config/database.php';

$userId = $_SESSION['user_id'];
$db = getDB();

// Obtener items del carrito
$stmt = $db->prepare("
    SELECT c.*, p.nombre, p.precio, p.stock,
           (c.cantidad * p.precio) as subtotal
    FROM carrito c
    JOIN productos p ON c.producto_id = p.id
    WHERE c.usuario_id = ? AND p.activo = 1
");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    header('Location: shopping-cart.php');
    exit;
}

$subtotal = array_sum(array_column($cartItems, 'subtotal'));
$impuestos = $subtotal * 0.13;
$envio = $subtotal >= 50000 ? 0 : 2500;
$total = $subtotal + $impuestos + $envio;

// Obtener datos del usuario
$stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$userId]);
$usuario = $stmt->fetch();

// Procesar pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['procesar_pedido'])) {
    try {
        $db->beginTransaction();
        
        // Generar número de pedido
        $numeroPedido = 'TS' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Crear pedido
        $stmt = $db->prepare("
            INSERT INTO pedidos (usuario_id, numero_pedido, total, metodo_pago, direccion_envio)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $numeroPedido,
            $total,
            $_POST['metodo_pago'],
            $_POST['direccion_envio']
        ]);
        
        $pedidoId = $db->lastInsertId();
        
        // Crear detalles del pedido y reducir stock
        foreach ($cartItems as $item) {
            // Insertar detalle
            $stmt = $db->prepare("
                INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $pedidoId,
                $item['producto_id'],
                $item['cantidad'],
                $item['precio'],
                $item['subtotal']
            ]);
            
            // Reducir stock
            $stmt = $db->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$item['cantidad'], $item['producto_id']]);
        }
        
        // Vaciar carrito
        $stmt = $db->prepare("DELETE FROM carrito WHERE usuario_id = ?");
        $stmt->execute([$userId]);
        
        $db->commit();
        
        // Redirigir a confirmación
        header("Location: order-success.php?pedido=$numeroPedido");
        exit;
        
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Error al procesar el pedido: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - TechStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <header class="bg-dark text-white py-3">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <a href="../../index.php" class="text-white text-decoration-none">
                        <h4><i class="fas fa-laptop me-2 text-primary"></i>TechStore</h4>
                    </a>
                </div>
                <div class="col-md-6 text-end">
                    <span>Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                </div>
            </div>
        </div>
    </header>

    <div class="container my-4">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../../index.php">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="shopping-cart.php">Carrito</a></li>
                        <li class="breadcrumb-item active">Checkout</li>
                    </ol>
                </nav>
                
                <h2><i class="fas fa-credit-card me-2"></i>Finalizar Compra</h2>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="row">
                <!-- Información del pedido -->
                <div class="col-lg-8">
                    <!-- Datos de envío -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-truck me-2"></i>Información de Envío</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nombre completo</label>
                                    <input type="text" class="form-control" 
                                           value="<?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" 
                                           value="<?php echo htmlspecialchars($usuario['email']); ?>" readonly>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Dirección de envío *</label>
                                <textarea class="form-control" name="direccion_envio" required rows="3" 
                                          placeholder="Dirección completa para el envío"><?php echo htmlspecialchars($usuario['direccion'] ?? ''); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" 
                                       value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Método de pago -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-credit-card me-2"></i>Método de Pago</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="metodo_pago" 
                                       value="tarjeta_credito" id="tarjeta" checked>
                                <label class="form-check-label" for="tarjeta">
                                    <i class="fas fa-credit-card me-2"></i>Tarjeta de Crédito
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="metodo_pago" 
                                       value="paypal" id="paypal">
                                <label class="form-check-label" for="paypal">
                                    <i class="fab fa-paypal me-2"></i>PayPal
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="metodo_pago" 
                                       value="transferencia" id="transferencia">
                                <label class="form-check-label" for="transferencia">
                                    <i class="fas fa-university me-2"></i>Transferencia Bancaria
                                </label>
                            </div>
                            
                            <div class="alert alert-info">
                                <small><i class="fas fa-info-circle me-1"></i>
                                Los pagos se procesan de forma segura. Para este demo, el pago se simula como exitoso.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumen del pedido -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-receipt me-2"></i>Resumen del Pedido</h5>
                        </div>
                        <div class="card-body">
                            <!-- Productos -->
                            <div class="mb-3">
                                <h6>Productos (<?php echo count($cartItems); ?>)</h6>
                                <?php foreach ($cartItems as $item): ?>
                                    <div class="d-flex justify-content-between mb-2">
                                        <small><?php echo htmlspecialchars($item['nombre']); ?> x<?php echo $item['cantidad']; ?></small>
                                        <small>₡<?php echo number_format($item['subtotal']); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <hr>
                            
                            <!-- Totales -->
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>₡<?php echo number_format($subtotal); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>IVA (13%):</span>
                                <span>₡<?php echo number_format($impuestos); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Envío:</span>
                                <span><?php echo $envio == 0 ? 'GRATIS' : '₡' . number_format($envio); ?></span>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-4">
                                <strong>Total a Pagar:</strong>
                                <strong class="text-primary h5">₡<?php echo number_format($total); ?></strong>
                            </div>
                            
                            <button type="submit" name="procesar_pedido" class="btn btn-success w-100 btn-lg">
                                <i class="fas fa-lock me-2"></i>Procesar Pedido
                            </button>
                            
                            <div class="text-center mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt me-1"></i>Pago 100% seguro
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>