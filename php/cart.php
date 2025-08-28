<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__FILE__) . '/../config/database.php';

// Debug temporal - agregar después de la línea 3
error_log("Cart.php accessed: " . print_r($_POST, true));
class CartManager {
    private $db;
    private $userId;
    
    public function __construct($userId = null) {
        $this->db = getDB();
        $this->userId = $userId ?? $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Agregar producto al carrito
     */
    public function addToCart($productId, $quantity = 1) {
        if (!$this->userId) {
            return ['success' => false, 'message' => 'Usuario no autenticado'];
        }
        
        try {
            // Verificar que el producto existe y tiene stock
            $stmt = $this->db->prepare("SELECT * FROM productos WHERE id = ? AND activo = 1");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if (!$product) {
                return ['success' => false, 'message' => 'Producto no encontrado'];
            }
            
            if ($product['stock'] < $quantity) {
                return ['success' => false, 'message' => 'Stock insuficiente'];
            }
            
            // Verificar si el producto ya está en el carrito
            $stmt = $this->db->prepare("SELECT * FROM carrito WHERE usuario_id = ? AND producto_id = ?");
            $stmt->execute([$this->userId, $productId]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Actualizar cantidad
                $newQuantity = $existing['cantidad'] + $quantity;
                
                if ($product['stock'] < $newQuantity) {
                    return ['success' => false, 'message' => 'Stock insuficiente para esa cantidad'];
                }
                
                $stmt = $this->db->prepare("UPDATE carrito SET cantidad = ? WHERE id = ?");
                $stmt->execute([$newQuantity, $existing['id']]);
            } else {
                // Agregar nuevo producto
                $stmt = $this->db->prepare(
                    "INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (?, ?, ?)"
                );
                $stmt->execute([$this->userId, $productId, $quantity]);
            }
            
            return ['success' => true, 'message' => 'Producto agregado al carrito'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al agregar al carrito: ' . $e->getMessage()];
        }
    }
    
    /**
     * Obtener productos del carrito
     */
    public function getCartItems() {
        if (!$this->userId) {
            return [];
        }
        
        try {
            $sql = "SELECT c.*, p.nombre, p.precio, p.imagen, p.stock,
                           (c.cantidad * p.precio) as subtotal
                    FROM carrito c
                    JOIN productos p ON c.producto_id = p.id
                    WHERE c.usuario_id = ? AND p.activo = 1
                    ORDER BY c.fecha_agregado DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->userId]);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            throw new Exception("Error al obtener carrito: " . $e->getMessage());
        }
    }
    
    /**
     * Actualizar cantidad de producto en carrito
     */
    public function updateQuantity($cartItemId, $quantity) {
        if (!$this->userId) {
            return ['success' => false, 'message' => 'Usuario no autenticado'];
        }
        
        try {
            // Verificar que el item pertenece al usuario
            $stmt = $this->db->prepare(
                "SELECT c.*, p.stock FROM carrito c 
                 JOIN productos p ON c.producto_id = p.id 
                 WHERE c.id = ? AND c.usuario_id = ?"
            );
            $stmt->execute([$cartItemId, $this->userId]);
            $item = $stmt->fetch();
            
            if (!$item) {
                return ['success' => false, 'message' => 'Item no encontrado'];
            }
            
            if ($quantity <= 0) {
                return $this->removeFromCart($cartItemId);
            }
            
            if ($item['stock'] < $quantity) {
                return ['success' => false, 'message' => 'Stock insuficiente'];
            }
            
            $stmt = $this->db->prepare("UPDATE carrito SET cantidad = ? WHERE id = ?");
            $stmt->execute([$quantity, $cartItemId]);
            
            return ['success' => true, 'message' => 'Cantidad actualizada'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()];
        }
    }
    
    /**
     * Eliminar producto del carrito
     */
    public function removeFromCart($cartItemId) {
        if (!$this->userId) {
            return ['success' => false, 'message' => 'Usuario no autenticado'];
        }
        
        try {
            $stmt = $this->db->prepare("DELETE FROM carrito WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$cartItemId, $this->userId]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Producto eliminado del carrito'];
            } else {
                return ['success' => false, 'message' => 'Item no encontrado'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()];
        }
    }
    
    /**
     * Vaciar carrito completo
     */
    public function clearCart() {
        if (!$this->userId) {
            return ['success' => false, 'message' => 'Usuario no autenticado'];
        }
        
        try {
            $stmt = $this->db->prepare("DELETE FROM carrito WHERE usuario_id = ?");
            $stmt->execute([$this->userId]);
            
            return ['success' => true, 'message' => 'Carrito vaciado'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al vaciar carrito: ' . $e->getMessage()];
        }
    }
    
    /**
     * Obtener totales del carrito
     */
    public function getCartTotals() {
        $items = $this->getCartItems();
        $subtotal = 0;
        $totalItems = 0;
        
        foreach ($items as $item) {
            $subtotal += $item['subtotal'];
            $totalItems += $item['cantidad'];
        }
        
        $impuestos = $subtotal * 0.13; // IVA 13%
        $envio = $subtotal >= 50000 ? 0 : 2500; // Envío gratis sobre ₡50,000
        $total = $subtotal + $impuestos + $envio;
        
        return [
            'subtotal' => $subtotal,
            'impuestos' => $impuestos,
            'envio' => $envio,
            'total' => $total,
            'total_items' => $totalItems,
            'items_count' => count($items)
        ];
    }
    
    /**
     * Obtener cantidad de productos en carrito
     */
    public function getCartItemCount() {
        if (!$this->userId) {
            return 0;
        }
        
        try {
            $stmt = $this->db->prepare("SELECT SUM(cantidad) as total FROM carrito WHERE usuario_id = ?");
            $stmt->execute([$this->userId]);
            $result = $stmt->fetch();
            
            return $result['total'] ?? 0;
            
        } catch (Exception $e) {
            return 0;
        }
    }
}

// Procesar peticiones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $cartManager = new CartManager();
    $response = ['success' => false, 'message' => 'Acción no válida'];
    
    switch ($_POST['action']) {
        case 'add_to_cart':
            $productId = $_POST['product_id'] ?? 0;
            $quantity = $_POST['quantity'] ?? 1;
            $response = $cartManager->addToCart($productId, $quantity);
            break;
            
        case 'update_quantity':
            $cartItemId = $_POST['cart_item_id'] ?? 0;
            $quantity = $_POST['quantity'] ?? 1;
            $response = $cartManager->updateQuantity($cartItemId, $quantity);
            break;
            
        case 'remove_item':
            $cartItemId = $_POST['cart_item_id'] ?? 0;
            $response = $cartManager->removeFromCart($cartItemId);
            break;
            
        case 'clear_cart':
            $response = $cartManager->clearCart();
            break;
            
        case 'get_cart_count':
            $count = $cartManager->getCartItemCount();
            $response = ['success' => true, 'count' => $count];
            break;
    }
    
    echo json_encode($response);
    exit;
}

// Para peticiones GET
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    $cartManager = new CartManager();
    
    switch ($_GET['action']) {
        case 'get_cart_items':
            $items = $cartManager->getCartItems();
            echo json_encode(['success' => true, 'items' => $items]);
            break;
            
        case 'get_cart_totals':
            $totals = $cartManager->getCartTotals();
            echo json_encode(['success' => true, 'totals' => $totals]);
            break;
            
        case 'get_cart_count':
            $count = $cartManager->getCartItemCount();
            echo json_encode(['success' => true, 'count' => $count]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    exit;
}

// Para incluir en otras páginas
$cartManager = new CartManager();
?>