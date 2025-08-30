<?php
session_start();
require_once '../config/database.php';

// Verificar admin
if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
    header('Location: ../pages/auth/login.php');
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT es_admin FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || !$user['es_admin']) {
    die('Acceso denegado. Solo administradores.');
}

// Procesar cambios de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $pedidoId = $_POST['pedido_id'];
    $nuevoEstado = $_POST['nuevo_estado'];
    
    $stmt = $db->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
    $stmt->execute([$nuevoEstado, $pedidoId]);
    $mensaje = "Estado del pedido actualizado correctamente";
}

// Obtener pedidos
$stmt = $db->query("
    SELECT 
        p.*,
        CONCAT(u.nombre, ' ', u.apellido) as cliente_nombre,
        u.email as cliente_email,
        COUNT(dp.id) as total_productos
    FROM pedidos p
    JOIN usuarios u ON p.usuario_id = u.id
    LEFT JOIN detalles_pedido dp ON p.id = dp.pedido_id
    GROUP BY p.id
    ORDER BY p.fecha_pedido DESC
");
$pedidos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos - Admin TechStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="bg-danger text-white py-3">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">
                        <i class="fas fa-shopping-cart me-2"></i>Gestión de Pedidos - TechStore Admin
                    </h4>
                </div>
                <div class="col-md-6 text-end">
                    <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
                    <a href="products-admin.php" class="btn btn-outline-light btn-sm">Productos</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid my-4">
        <?php if (isset($mensaje)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Lista de Pedidos</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($pedidos)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Cliente</th>
                                    <th>Email</th>
                                    <th>Productos</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pedidos as $pedido): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($pedido['numero_pedido']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($pedido['cliente_nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($pedido['cliente_email']); ?></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $pedido['total_productos']; ?> productos</span>
                                        </td>
                                        <td>
                                            <strong>₡<?php echo number_format($pedido['total']); ?></strong>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                                                <select name="nuevo_estado" class="form-select form-select-sm" 
                                                        onchange="this.form.submit()" style="width: 150px;">
                                                    <option value="pendiente" <?php echo $pedido['estado'] === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                                    <option value="procesando" <?php echo $pedido['estado'] === 'procesando' ? 'selected' : ''; ?>>Procesando</option>
                                                    <option value="enviado" <?php echo $pedido['estado'] === 'enviado' ? 'selected' : ''; ?>>Enviado</option>
                                                    <option value="entregado" <?php echo $pedido['estado'] === 'entregado' ? 'selected' : ''; ?>>Entregado</option>
                                                    <option value="cancelado" <?php echo $pedido['estado'] === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#detalleModal<?php echo $pedido['id']; ?>">
                                                <i class="fas fa-eye"></i> Ver
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                        <h4>No hay pedidos registrados</h4>
                        <p class="text-muted">Los pedidos aparecerán aquí cuando los clientes realicen compras.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modales de detalle para cada pedido -->
    <?php foreach ($pedidos as $pedido): ?>
        <div class="modal fade" id="detalleModal<?php echo $pedido['id']; ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalle del Pedido <?php echo $pedido['numero_pedido']; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Información del Cliente</h6>
                                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($pedido['cliente_nombre']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($pedido['cliente_email']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Información del Pedido</h6>
                                <p><strong>Número:</strong> <?php echo htmlspecialchars($pedido['numero_pedido']); ?></p>
                                <p><strong>Estado:</strong> 
                                    <span class="badge bg-<?php 
                                        echo match($pedido['estado']) {
                                            'pendiente' => 'warning',
                                            'procesando' => 'info',
                                            'enviado' => 'primary',
                                            'entregado' => 'success',
                                            'cancelado' => 'danger',
                                            default => 'secondary'
                                        };
                                    ?>"><?php echo ucfirst($pedido['estado']); ?></span>
                                </p>
                                <p><strong>Método de Pago:</strong> <?php echo ucwords(str_replace('_', ' ', $pedido['metodo_pago'])); ?></p>
                                <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></p>
                            </div>
                        </div>
                        
                        <h6>Dirección de Envío</h6>
                        <p><?php echo nl2br(htmlspecialchars($pedido['direccion_envio'])); ?></p>
                        
                        <h6>Total del Pedido</h6>
                        <h4 class="text-primary">₡<?php echo number_format($pedido['total']); ?></h4>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>