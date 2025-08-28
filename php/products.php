<?php
session_start();
require_once '../config/database.php';

class ProductManager {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Obtener todos los productos activos con filtros
     */
    public function getProducts($filters = []) {
        $sql = "SELECT p.*, c.nombre as categoria_nombre 
                FROM productos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                WHERE p.activo = 1";
        $params = [];
        
        // Filtro por búsqueda
        if (!empty($filters['search'])) {
            $sql .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        // Filtro por categoría
        if (!empty($filters['categoria_id'])) {
            $sql .= " AND p.categoria_id = ?";
            $params[] = $filters['categoria_id'];
        }
        
        // Filtro por rango de precio
        if (!empty($filters['precio_min'])) {
            $sql .= " AND p.precio >= ?";
            $params[] = $filters['precio_min'];
        }
        
        if (!empty($filters['precio_max'])) {
            $sql .= " AND p.precio <= ?";
            $params[] = $filters['precio_max'];
        }
        
        // Filtro por disponibilidad
        if (!empty($filters['en_stock'])) {
            $sql .= " AND p.stock > 0";
        }
        
        // Ordenamiento
        $orderBy = $filters['order_by'] ?? 'p.nombre';
        $orderDir = $filters['order_dir'] ?? 'ASC';
        $sql .= " ORDER BY $orderBy $orderDir";
        
        // Límite para paginación
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . intval($filters['limit']);
            if (!empty($filters['offset'])) {
                $sql .= " OFFSET " . intval($filters['offset']);
            }
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception("Error al obtener productos: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener producto por ID
     */
    public function getProductById($id) {
        try {
            $sql = "SELECT p.*, c.nombre as categoria_nombre 
                    FROM productos p 
                    LEFT JOIN categorias c ON p.categoria_id = c.id 
                    WHERE p.id = ? AND p.activo = 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            throw new Exception("Error al obtener producto: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener productos destacados
     */
    public function getFeaturedProducts($limit = 8) {
        try {
            $sql = "SELECT p.*, c.nombre as categoria_nombre 
                    FROM productos p 
                    LEFT JOIN categorias c ON p.categoria_id = c.id 
                    WHERE p.activo = 1 AND p.destacado = 1 
                    ORDER BY p.fecha_creacion DESC 
                    LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception("Error al obtener productos destacados: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener categorías activas
     */
    public function getCategories() {
        try {
            $sql = "SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception("Error al obtener categorías: " . $e->getMessage());
        }
    }
    
    /**
     * Verificar stock de producto
     */
    public function checkStock($productId, $quantity = 1) {
        try {
            $sql = "SELECT stock FROM productos WHERE id = ? AND activo = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if (!$product) {
                return ['available' => false, 'message' => 'Producto no encontrado'];
            }
            
            if ($product['stock'] >= $quantity) {
                return ['available' => true, 'stock' => $product['stock']];
            } else {
                return ['available' => false, 'message' => 'Stock insuficiente', 'stock' => $product['stock']];
            }
        } catch (Exception $e) {
            throw new Exception("Error al verificar stock: " . $e->getMessage());
        }
    }
    
    /**
     * Reducir stock de producto
     */
    public function reduceStock($productId, $quantity) {
        try {
            $sql = "UPDATE productos SET stock = stock - ? WHERE id = ? AND stock >= ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$quantity, $productId, $quantity]);
            
            if ($stmt->rowCount() === 0) {
                return false; // No se pudo reducir el stock
            }
            
            return true;
        } catch (Exception $e) {
            throw new Exception("Error al reducir stock: " . $e->getMessage());
        }
    }
    
    /**
     * Buscar productos similares por categoría
     */
    public function getSimilarProducts($productId, $categoryId, $limit = 4) {
        try {
            $sql = "SELECT p.*, c.nombre as categoria_nombre 
                    FROM productos p 
                    LEFT JOIN categorias c ON p.categoria_id = c.id 
                    WHERE p.categoria_id = ? AND p.id != ? AND p.activo = 1 
                    ORDER BY RAND() 
                    LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$categoryId, $productId, $limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new Exception("Error al obtener productos similares: " . $e->getMessage());
        }
    }
}

// API endpoints para AJAX
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $productManager = new ProductManager();
    header('Content-Type: application/json');
    
    try {
        switch ($_GET['action']) {
            case 'get_products':
                $filters = [
                    'search' => $_GET['search'] ?? '',
                    'categoria_id' => $_GET['categoria_id'] ?? '',
                    'precio_min' => $_GET['precio_min'] ?? '',
                    'precio_max' => $_GET['precio_max'] ?? '',
                    'en_stock' => $_GET['en_stock'] ?? false,
                    'limit' => $_GET['limit'] ?? null,
                    'offset' => $_GET['offset'] ?? null,
                    'order_by' => $_GET['order_by'] ?? 'nombre',
                    'order_dir' => $_GET['order_dir'] ?? 'ASC'
                ];
                
                $products = $productManager->getProducts($filters);
                echo json_encode(['success' => true, 'products' => $products]);
                break;
                
            case 'get_product':
                $id = $_GET['id'] ?? 0;
                $product = $productManager->getProductById($id);
                
                if ($product) {
                    echo json_encode(['success' => true, 'product' => $product]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
                }
                break;
                
            case 'get_featured':
                $limit = $_GET['limit'] ?? 8;
                $products = $productManager->getFeaturedProducts($limit);
                echo json_encode(['success' => true, 'products' => $products]);
                break;
                
            case 'get_categories':
                $categories = $productManager->getCategories();
                echo json_encode(['success' => true, 'categories' => $categories]);
                break;
                
            case 'check_stock':
                $productId = $_GET['product_id'] ?? 0;
                $quantity = $_GET['quantity'] ?? 1;
                $stockCheck = $productManager->checkStock($productId, $quantity);
                echo json_encode(['success' => true, 'stock_check' => $stockCheck]);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Para incluir en otras páginas
if (!isset($_GET['action'])) {
    $productManager = new ProductManager();
}
?>