<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in'] || !isset($_GET['pedido'])) {
    header('Location: ../../index.php');
    exit;
}

require_once '../../config/database.php';

$numeroPedido = $_GET['pedido'];
$userId = $_SESSION['user_id'];
$db = getDB();

// Obtener información del pedido
$stmt = $db->prepare("
    SELECT p.*, 
           COUNT(dp.id) as total_productos,
           SUM(dp.cantidad) as total_items
    FROM pedidos p
    LEFT JOIN detalles_pedido dp ON p.id = dp.pedido_id
    WHERE p.numero_pedido = ? AND p.usuario_id = ?
    GROUP BY p.id
");
$stmt->execute([$numeroPedido, $userId]);
$pedido = $stmt->fetch();

if (!$pedido) {
    header('Location: ../../index.php');
    exit;
}

// Generar número de seguimiento simulado
$numeroSeguimiento = 'TS-' . date('Y') . '-' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado - TechStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <div class="success-icon mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                    </div>
                    <h1 class="text-success mb-3">¡Pedido Confirmado!</h1>
                    <p class="lead">Tu pedido ha sido procesado exitosamente</p>
                </div>

                <div class="card shadow-lg">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-receipt me-2"></i>Detalles del Pedido
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-muted">Número de Pedido</h6>
                                <p class="h5 text-primary"><?php echo htmlspecialchars($numeroPedido); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Número de Seguimiento</h6>
                                <p class="h5 text-info"><?php echo $numeroSeguimiento; ?></p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <h6 class="text-muted">Fecha del Pedido</h6>
                                <p><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></p>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-muted">Total de Productos</h6>
                                <p><?php echo $pedido['total_items']; ?> productos</p>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-muted">Método de Pago</h6>
                                <p class="text-capitalize"><?php echo str_replace('_', ' ', $pedido['metodo_pago']); ?></p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-muted">Dirección de Envío</h6>
                                <p><?php echo nl2br(htmlspecialchars($pedido['direccion_envio'])); ?></p>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <div class="row align-items-center">
                                <div class="col-md-1 text-center">
                                    <i class="fas fa-truck fa-2x text-primary"></i>
                                </div>
                                <div class="col-md-11">
                                    <h6 class="alert-heading">Información de Envío</h6>
                                    <p class="mb-1">Tu pedido será enviado en 2-3 días hábiles.</p>
                                    <p class="mb-0">Recibirás un email con la información de seguimiento.</p>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <h4 class="text-success mb-3">
                                Total Pagado: ₡<?php echo number_format($pedido['total']); ?>
                            </h4>
                            <p class="text-muted">Estado: <span class="badge bg-warning">En Procesamiento</span></p>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-email fa-2x text-primary mb-3"></i>
                                <h6>Confirmación por Email</h6>
                                <p class="small text-muted">Enviada a <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-headset fa-2x text-success mb-3"></i>
                                <h6>Soporte 24/7</h6>
                                <p class="small text-muted">¿Preguntas? Contáctanos</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-undo fa-2x text-info mb-3"></i>
                                <h6>30 Días Garantía</h6>
                                <p class="small text-muted">Devolución sin preguntas</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <a href="../../index.php" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-home me-2"></i>Volver al Inicio
                    </a>
                    <a href="../products/catalog.php" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-shopping-bag me-2"></i>Seguir Comprando
                    </a>
                </div>

                <div class="text-center mt-4">
                    <p class="text-muted">
                        <i class="fas fa-heart text-danger me-1"></i>
                        ¡Gracias por confiar en TechStore!
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>