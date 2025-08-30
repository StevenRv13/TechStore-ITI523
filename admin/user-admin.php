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

// Procesar cambios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toggle_status'])) {
        $usuarioId = $_POST['usuario_id'];
        $stmt = $db->prepare("UPDATE usuarios SET activo = NOT activo WHERE id = ?");
        $stmt->execute([$usuarioId]);
        $mensaje = "Estado del usuario actualizado";
    }
}

// Obtener usuarios
$stmt = $db->query("
    SELECT 
        u.*,
        COUNT(p.id) as total_pedidos,
        COALESCE(SUM(p.total), 0) as total_gastado
    FROM usuarios u
    LEFT JOIN pedidos p ON u.id = p.usuario_id
    GROUP BY u.id
    ORDER BY u.fecha_registro DESC
");
$usuarios = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Admin TechStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="bg-danger text-white py-3">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">
                        <i class="fas fa-users me-2"></i>Gestión de Usuarios - TechStore Admin
                    </h4>
                </div>
                <div class="col-md-6 text-end">
                    <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
                    <a href="orders-admin.php" class="btn btn-outline-light btn-sm">Pedidos</a>
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
                <h5 class="mb-0">Lista de Usuarios Registrados</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Pedidos</th>
                                <th>Total Gastado</th>
                                <th>Registro</th>
                                <th>Estado</th>
                                <th>Rol</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><?php echo $usuario['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['telefono'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $usuario['total_pedidos']; ?></span>
                                    </td>
                                    <td>
                                        <strong>₡<?php echo number_format($usuario['total_gastado']); ?></strong>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></td>
                                    <td>
                                        <?php if ($usuario['activo']): ?>
                                            <span class="badge bg-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($usuario['es_admin']): ?>
                                            <span class="badge bg-danger">Administrador</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">Cliente</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!$usuario['es_admin']): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                                                <button type="submit" name="toggle_status" 
                                                        class="btn btn-sm <?php echo $usuario['activo'] ? 'btn-warning' : 'btn-success'; ?>">
                                                    <i class="fas <?php echo $usuario['activo'] ? 'fa-pause' : 'fa-play'; ?>"></i>
                                                    <?php echo $usuario['activo'] ? 'Desactivar' : 'Activar'; ?>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">Administrador</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Estadísticas de usuarios -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-primary"><?php echo count($usuarios); ?></h5>
                        <p class="card-text">Total Usuarios</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-success">
                            <?php echo count(array_filter($usuarios, fn($u) => $u['activo'])); ?>
                        </h5>
                        <p class="card-text">Usuarios Activos</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-info">
                            <?php echo count(array_filter($usuarios, fn($u) => $u['total_pedidos'] > 0)); ?>
                        </h5>
                        <p class="card-text">Clientes con Compras</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-warning">
                            <?php echo count(array_filter($usuarios, fn($u) => $u['es_admin'])); ?>
                        </h5>
                        <p class="card-text">Administradores</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>