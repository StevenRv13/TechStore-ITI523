<?php
session_start();
require_once '../config/database.php';

// Verificar que sea administrador
if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
    header('Location: ../pages/auth/login.php');
    exit;
}

$db = getDB();

// Verificar permisos de admin
$stmt = $db->prepare("SELECT es_admin FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || !$user['es_admin']) {
    die('<div class="alert alert-danger">Acceso denegado. Solo administradores.</div>');
}

// Obtener estadísticas
try {
    // Total de productos
    $stmt = $db->query("SELECT COUNT(*) as total FROM productos WHERE activo = 1");
    $totalProductos = $stmt->fetch()['total'];
    
    // Total de usuarios
    $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE activo = 1");
    $totalUsuarios = $stmt->fetch()['total'];
    
    // Total de pedidos
    $stmt = $db->query("SELECT COUNT(*) as total FROM pedidos");
    $totalPedidos = $stmt->fetch()['total'];
    
    // Ventas totales
    $stmt = $db->query("SELECT COALESCE(SUM(total), 0) as total FROM pedidos");
    $ventasTotales = $stmt->fetch()['total'];
    
    // Productos más vendidos
    $stmt = $db->query("
        SELECT p.nombre, SUM(dp.cantidad) as vendidos 
        FROM detalles_pedido dp 
        JOIN productos p ON dp.producto_id = p.id 
        GROUP BY p.id 
        ORDER BY vendidos DESC 
        LIMIT 5
    ");
    $productosVendidos = $stmt->fetchAll();
    
    // Pedidos recientes
    $stmt = $db->query("
        SELECT p.numero_pedido, p.total, p.estado, p.fecha_pedido,
               CONCAT(u.nombre, ' ', u.apellido) as cliente
        FROM pedidos p
        JOIN usuarios u ON p.usuario_id = u.id
        ORDER BY p.fecha_pedido DESC
        LIMIT 10
    ");
    $pedidosRecientes = $stmt->fetchAll();
    
    // Productos con stock bajo
    $stmt = $db->query("
        SELECT nombre, stock 
        FROM productos 
        WHERE stock < 10 AND activo = 1 
        ORDER BY stock ASC
    ");
    $stockBajo = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = "Error al cargar estadísticas: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - TechStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Header Admin -->
    <header class="bg-danger text-white py-3">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">
                        <i class="fas fa-cogs me-2"></i>Panel de Administración - TechStore
                    </h4>
                </div>
                <div class="col-md-6 text-end">
                    <span class="me-3">Admin: <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <a href="../index.php" class="btn btn-outline-light btn-sm me-2">Ver Tienda</a>
                    <a href="../php/auth.php?action=logout" class="btn btn-light btn-sm">Salir</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container-fluid my-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0">Menú Admin</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="dashboard.php" class="list-group-item list-group-item-action active">
                            <i class="fas fa-chart-line me-2"></i>Dashboard
                        </a>
                        <a href="products-admin.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-box me-2"></i>Productos
                        </a>
                        <a href="orders-admin.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-shopping-cart me-2"></i>Pedidos
                        </a>
                        <a href="users-admin.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-users me-2"></i>Usuarios
                        </a>
                        <a href="reports.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-bar me-2"></i>Reportes
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Estadísticas Principales -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Productos</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $totalProductos ?? 0; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-box fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Ventas Totales</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            ₡<?php echo number_format($ventasTotales ?? 0); ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Total Pedidos</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $totalPedidos ?? 0; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Total Usuarios</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php echo $totalUsuarios ?? 0; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Productos Más Vendidos -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-chart-pie me-2"></i>Productos Más Vendidos
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($productosVendidos)): ?>
                                    <?php foreach ($productosVendidos as $producto): ?>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span><?php echo htmlspecialchars($producto['nombre']); ?></span>
                                            <span class="badge bg-primary"><?php echo $producto['vendidos']; ?> vendidos</span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">No hay datos de ventas aún</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Bajo -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>Stock Bajo (< 10)
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($stockBajo)): ?>
                                    <?php foreach ($stockBajo as $producto): ?>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span><?php echo htmlspecialchars($producto['nombre']); ?></span>
                                            <span class="badge bg-warning"><?php echo $producto['stock']; ?> unidades</span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-success">Todos los productos tienen stock suficiente</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pedidos Recientes -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-list me-2"></i>Pedidos Recientes
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($pedidosRecientes)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Número</th>
                                            <th>Cliente</th>
                                            <th>Total</th>
                                            <th>Estado</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pedidosRecientes as $pedido): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($pedido['numero_pedido']); ?></td>
                                                <td><?php echo htmlspecialchars($pedido['cliente']); ?></td>
                                                <td>₡<?php echo number_format($pedido['total']); ?></td>
                                                <td>
                                                    <span class="badge bg-warning"><?php echo ucfirst($pedido['estado']); ?></span>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No hay pedidos registrados</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>