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

// Generar reportes
try {
    // Ventas por mes (últimos 6 meses)
    $ventasPorMes = $db->query("
        SELECT 
            DATE_FORMAT(fecha_pedido, '%Y-%m') as mes,
            COUNT(*) as pedidos,
            SUM(total) as ventas
        FROM pedidos 
        WHERE fecha_pedido >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(fecha_pedido, '%Y-%m')
        ORDER BY mes DESC
    ")->fetchAll();
    
    // Productos más vendidos
    $topProductos = $db->query("
        SELECT 
            p.nombre,
            SUM(dp.cantidad) as vendidos,
            SUM(dp.subtotal) as ingresos
        FROM detalles_pedido dp
        JOIN productos p ON dp.producto_id = p.id
        GROUP BY p.id
        ORDER BY vendidos DESC
        LIMIT 10
    ")->fetchAll();
    
    // Ventas por categoría
    $ventasPorCategoria = $db->query("
        SELECT 
            c.nombre as categoria,
            COUNT(DISTINCT pe.id) as pedidos,
            SUM(dp.subtotal) as ventas
        FROM detalles_pedido dp
        JOIN productos p ON dp.producto_id = p.id
        JOIN categorias c ON p.categoria_id = c.id
        JOIN pedidos pe ON dp.pedido_id = pe.id
        GROUP BY c.id
        ORDER BY ventas DESC
    ")->fetchAll();
    
    // Estadísticas generales
    $stats = [
        'total_ventas' => $db->query("SELECT COALESCE(SUM(total), 0) as total FROM pedidos")->fetch()['total'],
        'pedidos_mes' => $db->query("SELECT COUNT(*) as total FROM pedidos WHERE MONTH(fecha_pedido) = MONTH(NOW())")->fetch()['total'],
        'promedio_pedido' => $db->query("SELECT COALESCE(AVG(total), 0) as promedio FROM pedidos")->fetch()['promedio'],
        'clientes_activos' => $db->query("SELECT COUNT(DISTINCT usuario_id) as total FROM pedidos")->fetch()['total']
    ];
    
} catch (Exception $e) {
    $error = "Error al generar reportes: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes de Ventas - Admin TechStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header class="bg-danger text-white py-3">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Reportes de Ventas - TechStore Admin
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
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Estadísticas Resumen -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Ventas Totales</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    ₡<?php echo number_format($stats['total_ventas']); ?>
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
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Pedidos Este Mes</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $stats['pedidos_mes']; ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar fa-2x text-gray-300"></i>
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
                                    Promedio por Pedido</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    ₡<?php echo number_format($stats['promedio_pedido']); ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chart-pie fa-2x text-gray-300"></i>
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
                                    Clientes Activos</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?php echo $stats['clientes_activos']; ?>
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
            <!-- Gráfico de Ventas -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Ventas por Mes</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="ventasChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Ventas por Categoría -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Ventas por Categoría</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($ventasPorCategoria)): ?>
                            <?php foreach ($ventasPorCategoria as $categoria): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><?php echo htmlspecialchars($categoria['categoria']); ?></span>
                                    <span class="fw-bold">₡<?php echo number_format($categoria['ventas']); ?></span>
                                </div>
                                <div class="progress mb-3" style="height: 10px;">
                                    <div class="progress-bar" style="width: <?php echo min(100, ($categoria['ventas'] / max(array_column($ventasPorCategoria, 'ventas'))) * 100); ?>%"></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No hay datos de ventas por categoría</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Productos Más Vendidos -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Top 10 - Productos Más Vendidos</h6>
            </div>
            <div class="card-body">
                <?php if (!empty($topProductos)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Posición</th>
                                    <th>Producto</th>
                                    <th>Unidades Vendidas</th>
                                    <th>Ingresos Generados</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topProductos as $index => $producto): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-<?php echo $index < 3 ? ['success', 'warning', 'info'][$index] : 'secondary'; ?>">
                                                #<?php echo $index + 1; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                        <td><?php echo $producto['vendidos']; ?> unidades</td>
                                        <td class="fw-bold">₡<?php echo number_format($producto['ingresos']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No hay datos de ventas de productos</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Botones de Exportación -->
        <div class="card-body">
  <div class="row">
    <div class="col-md-6">
      <a class="btn btn-danger mb-2" href="reports-pdf.php" target="_blank" rel="noopener">
        <i class="fas fa-file-pdf me-2"></i>Exportar a PDF
      </a>
    </div>
    <div class="col-md-6">
      <button class="btn btn-info mb-2" onclick="window.print()">
        <i class="fas fa-print me-2"></i>Imprimir Reporte
      </button>
    </div>
  </div>
</div>

    <script>
        // Gráfico de ventas por mes
        const ctx = document.getElementById('ventasChart').getContext('2d');
        const ventasChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [
                    <?php if (!empty($ventasPorMes)): ?>
                        <?php foreach ($ventasPorMes as $venta): ?>
                            '<?php echo $venta['mes']; ?>',
                        <?php endforeach; ?>
                    <?php else: ?>
                        '<?php echo date('Y-m'); ?>'
                    <?php endif; ?>
                ],
                datasets: [{
                    label: 'Ventas (₡)',
                    data: [
                        <?php if (!empty($ventasPorMes)): ?>
                            <?php foreach ($ventasPorMes as $venta): ?>
                                <?php echo $venta['ventas']; ?>,
                            <?php endforeach; ?>
                        <?php else: ?>
                            0
                        <?php endif; ?>
                    ],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₡' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Función para exportar CSV (simulada)
        function exportCSV() {
            alert('Funcionalidad de exportación CSV en desarrollo.\nEn una versión completa, esto generaría un archivo CSV con todos los datos de ventas.');
        }
    </script>
</body>
</html>