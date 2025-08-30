<?php
session_start();
require_once '../config/database.php';

// Verificar admin
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

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toggle_status'])) {
        $productId = $_POST['product_id'];
        $stmt = $db->prepare("UPDATE productos SET activo = NOT activo WHERE id = ?");
        $stmt->execute([$productId]);
        $mensaje = "Estado del producto actualizado";
    }
    
    if (isset($_POST['update_stock'])) {
        $productId = $_POST['product_id'];
        $newStock = $_POST['new_stock'];
        $stmt = $db->prepare("UPDATE productos SET stock = ? WHERE id = ?");
        $stmt->execute([$newStock, $productId]);
        $mensaje = "Stock actualizado correctamente";
    }
}


$stmt = $db->query("
    SELECT p.*, c.nombre as categoria_nombre 
    FROM productos p 
    LEFT JOIN categorias c ON p.categoria_id = c.id 
    ORDER BY p.id DESC
");
$productos = $stmt->fetchAll();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - Admin TechStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="bg-danger text-white py-3">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">
                        <i class="fas fa-box me-2"></i>Gestión de Productos - TechStore Admin
                    </h4>
                </div>
                <div class="col-md-6 text-end">
                    <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
                    <a href="../index.php" class="btn btn-light btn-sm">Ver Tienda</a>
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lista de Productos</h5>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus me-2"></i>Agregar Producto
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Imagen</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $producto): ?>
                                <tr>
                                    <td><?php echo $producto['id']; ?></td>
                                    <td>
                                        <img src="../assets/images/products/<?php echo $producto['imagen']; ?>" 
                                             alt="<?php echo $producto['nombre']; ?>" 
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    </td>
                                    <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($producto['categoria_nombre']); ?></td>
                                    <td>₡<?php echo number_format($producto['precio']); ?></td>
                                    <td>
                                        <form method="POST" class="d-flex align-items-center" style="gap: 5px;">
                                            <input type="number" class="form-control form-control-sm" 
                                                   style="width: 80px;" name="new_stock" 
                                                   value="<?php echo $producto['stock']; ?>" min="0">
                                            <input type="hidden" name="product_id" value="<?php echo $producto['id']; ?>">
                                            <button type="submit" name="update_stock" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <?php if ($producto['activo']): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="product_id" value="<?php echo $producto['id']; ?>">
                                            <button type="submit" name="toggle_status" 
                                                    class="btn btn-sm <?php echo $producto['activo'] ? 'btn-warning' : 'btn-success'; ?>">
                                                <i class="fas <?php echo $producto['activo'] ? 'fa-pause' : 'fa-play'; ?>"></i>
                                                <?php echo $producto['activo'] ? 'Desactivar' : 'Activar'; ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Agregar Producto -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Nuevo Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Nota:</strong> Para este demo, solo se pueden actualizar productos existentes. 
                            La funcionalidad completa de agregar productos requiere manejo de imágenes.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nombre del Producto</label>
                                    <input type="text" class="form-control" name="nombre" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Precio</label>
                                    <input type="number" class="form-control" name="precio" step="0.01" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Stock Inicial</label>
                                    <input type="number" class="form-control" name="stock" value="0" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Categoría</label>
                                    <select class="form-select" name="categoria_id" required>
                                        <option value="">Seleccionar categoría</option>
                                        <option value="1">Smartphones</option>
                                        <option value="2">Laptops</option>
                                        <option value="3">Tablets</option>
                                        <option value="4">Accesorios</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="add_product" class="btn btn-primary" disabled>
                            Agregar Producto (Demo)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>