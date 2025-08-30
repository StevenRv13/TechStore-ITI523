<?php
session_start();
require_once '../config/database.php';

// --- Seguridad admin ---
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

// --- Datos para el reporte ---
try {
    // Ventas por mes (últimos 6 meses)
    $ventasPorMes = $db->query("
        SELECT 
            DATE_FORMAT(fecha_pedido, '%Y-%m') as mes,
            COUNT(*) as pedidos,
            COALESCE(SUM(total),0) as ventas
        FROM pedidos 
        WHERE fecha_pedido >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(fecha_pedido, '%Y-%m')
        ORDER BY mes DESC
    ")->fetchAll();

    // Productos más vendidos
    $topProductos = $db->query("
        SELECT 
            p.nombre,
            COALESCE(SUM(dp.cantidad),0) as vendidos,
            COALESCE(SUM(dp.subtotal),0) as ingresos
        FROM detalles_pedido dp
        JOIN productos p ON dp.producto_id = p.id
        GROUP BY p.id, p.nombre
        ORDER BY vendidos DESC
        LIMIT 10
    ")->fetchAll();

    // Ventas por Categoría
    $ventasPorCategoria = $db->query("
        SELECT 
            c.nombre as categoria,
            COUNT(DISTINCT pe.id) as pedidos,
            COALESCE(SUM(dp.subtotal),0) as ventas
        FROM detalles_pedido dp
        JOIN productos p ON dp.producto_id = p.id
        JOIN categorias c ON p.categoria_id = c.id
        JOIN pedidos pe ON dp.pedido_id = pe.id
        GROUP BY c.id, c.nombre
        ORDER BY ventas DESC
    ")->fetchAll();

    // KPIs
    $stats = [
        'total_ventas'     => $db->query("SELECT COALESCE(SUM(total),0) as total FROM pedidos")->fetch()['total'] ?? 0,
        'pedidos_mes'      => $db->query("SELECT COUNT(*) as total FROM pedidos WHERE MONTH(fecha_pedido) = MONTH(NOW()) AND YEAR(fecha_pedido)=YEAR(NOW())")->fetch()['total'] ?? 0,
        'promedio_pedido'  => $db->query("SELECT COALESCE(AVG(total),0) as promedio FROM pedidos")->fetch()['promedio'] ?? 0,
        'clientes_activos' => $db->query("SELECT COUNT(DISTINCT usuario_id) as total FROM pedidos")->fetch()['total'] ?? 0,
    ];
} catch (Exception $e) {
    die('Error al generar datos: ' . $e->getMessage());
}

// --- Render HTML ---
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Ventas - TechStore</title>
<style>
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; color:#222; }
    h1, h2, h3 { margin: 0 0 8px; }
    .header { display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #e5e5e5; padding-bottom:8px; margin-bottom:12px; }
    .brand { font-size: 18px; font-weight: 700; color:#d9230f; }
    .small { color:#666; font-size: 11px; }
    .grid { display:flex; gap:10px; }
    .kpi { flex:1; border:1px solid #ddd; border-radius:6px; padding:10px; }
    .kpi-title { font-size:11px; color:#666; text-transform:uppercase; letter-spacing: .5px; }
    .kpi-value { font-size:18px; font-weight:700; margin-top:6px; }
    table { width:100%; border-collapse: collapse; margin:8px 0 16px; }
    th, td { border:1px solid #ddd; padding:6px 8px; }
    th { background:#f7f7f7; text-align:left; }
    .bar-wrap { background:#f0f0f0; border-radius:4px; height:10px; }
    .bar { background:#2a9d8f; height:10px; border-radius:4px; }
    .section { margin-top: 10px; }
    .right { text-align:right; }
    .muted { color:#777; }
    .footer { position: fixed; bottom: 10px; left: 0; right: 0; text-align: center; font-size: 10px; color:#888;}
</style>
</head>
<body>
    <div class="header">
        <div>
            <div class="brand">TechStore — Reporte de Ventas</div>
            <div class="small">Generado: <?php echo date('Y-m-d H:i'); ?></div>
        </div>
        <div class="small">Administrador: <?php echo htmlspecialchars($_SESSION['user_name'] ?? '—'); ?></div>
    </div>

    <!-- KPIs -->
    <div class="grid">
        <div class="kpi">
            <div class="kpi-title">Ventas Totales</div>
            <div class="kpi-value">₡<?php echo number_format($stats['total_ventas']); ?></div>
        </div>
        <div class="kpi">
            <div class="kpi-title">Pedidos este mes</div>
            <div class="kpi-value"><?php echo (int)$stats['pedidos_mes']; ?></div>
        </div>
        <div class="kpi">
            <div class="kpi-title">Promedio por pedido</div>
            <div class="kpi-value">₡<?php echo number_format($stats['promedio_pedido']); ?></div>
        </div>
        <div class="kpi">
            <div class="kpi-title">Clientes activos</div>
            <div class="kpi-value"><?php echo (int)$stats['clientes_activos']; ?></div>
        </div>
    </div>

    <!-- Ventas por Mes -->
    <div class="section">
        <h2>Ventas por mes (últimos 6)</h2>
        <?php if (!empty($ventasPorMes)): ?>
        <table>
            <thead>
                <tr>
                    <th>Mes</th>
                    <th class="right">Pedidos</th>
                    <th class="right">Ventas (₡)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventasPorMes as $fila): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fila['mes']); ?></td>
                        <td class="right"><?php echo (int)$fila['pedidos']; ?></td>
                        <td class="right">₡<?php echo number_format($fila['ventas']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="muted">No hay datos de ventas por mes.</p>
        <?php endif; ?>
    </div>

    <!-- Ventas por Categoría -->
    <div class="section">
        <h2>Ventas por categoría</h2>
        <?php if (!empty($ventasPorCategoria)): ?>
        <?php
            $maxVentas = max(array_column($ventasPorCategoria, 'ventas'));
            $maxVentas = $maxVentas > 0 ? $maxVentas : 1;
        ?>
        <table>
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th class="right">Pedidos</th>
                    <th class="right">Ventas (₡)</th>
                    <th>Proporción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventasPorCategoria as $cat): 
                    $pct = round(($cat['ventas'] / $maxVentas) * 100);
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cat['categoria']); ?></td>
                        <td class="right"><?php echo (int)$cat['pedidos']; ?></td>
                        <td class="right">₡<?php echo number_format($cat['ventas']); ?></td>
                        <td>
                            <div class="bar-wrap">
                                <div class="bar" style="width: <?php echo $pct; ?>%;"></div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="muted">No hay datos por categoría.</p>
        <?php endif; ?>
    </div>

    <!-- Top 10 Productos -->
    <div class="section">
        <h2>Top 10 productos más vendidos</h2>
        <?php if (!empty($topProductos)): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Producto</th>
                    <th class="right">Unidades</th>
                    <th class="right">Ingresos (₡)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topProductos as $i => $p): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                    <td class="right"><?php echo (int)$p['vendidos']; ?></td>
                    <td class="right">₡<?php echo number_format($p['ingresos']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="muted">No hay datos de productos vendidos.</p>
        <?php endif; ?>
    </div>

    <div class="footer">TechStore · Reporte generado automáticamente</div>
</body>
</html>
<?php
$html = ob_get_clean();

// --- Generar PDF con Dompdf ---
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true); // por si luego incrustas imágenes remotas
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Forzar descarga con nombre del archivo
$filename = 'reporte_ventas_' . date('Ymd_His') . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]); // true = descargar; false = abrir en navegador
exit;
