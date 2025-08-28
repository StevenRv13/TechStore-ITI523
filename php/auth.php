<?php
session_start();
require_once '../config/database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    public function register($nombre, $apellido, $email, $password, $telefono = '', $direccion = '') {
        try {
            // Validar email único
            $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'El email ya está registrado'];
            }
            
            // Validar contraseña
            if (strlen($password) < 6) {
                return ['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres'];
            }
            
            // Hash de la contraseña
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insertar usuario
            $stmt = $this->db->prepare(
                "INSERT INTO usuarios (nombre, apellido, email, password, telefono, direccion) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            
            $result = $stmt->execute([$nombre, $apellido, $email, $hashedPassword, $telefono, $direccion]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Usuario registrado correctamente'];
            } else {
                return ['success' => false, 'message' => 'Error al registrar usuario'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error del sistema: ' . $e->getMessage()];
        }
    }
    
    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = 1");
            $stmt->execute([$email]);
            
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Crear sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['is_logged_in'] = true;
                
                // Actualizar último acceso
                $updateStmt = $this->db->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
                $updateStmt->execute([$user['id']]);
                
                return ['success' => true, 'message' => 'Login exitoso', 'user' => $user];
            } else {
                return ['success' => false, 'message' => 'Email o contraseña incorrectos'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error del sistema: ' . $e->getMessage()];
        }
    }
    
    public function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Sesión cerrada correctamente'];
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true;
    }
    
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }
    
    public static function getCurrentUser() {
        if (self::isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email']
            ];
        }
        return null;
    }
}

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();
    $response = ['success' => false, 'message' => 'Acción no válida'];
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'register':
                $response = $auth->register(
                    $_POST['nombre'] ?? '',
                    $_POST['apellido'] ?? '',
                    $_POST['email'] ?? '',
                    $_POST['password'] ?? '',
                    $_POST['telefono'] ?? '',
                    $_POST['direccion'] ?? ''
                );
                break;
                
            case 'login':
                $response = $auth->login(
                    $_POST['email'] ?? '',
                    $_POST['password'] ?? ''
                );
                break;
                
            case 'logout':
                $response = $auth->logout();
                break;
        }
    }
    
    // Retornar JSON para AJAX
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Redirección normal - RUTAS FINALES CORREGIDAS
    if ($response['success']) {
        if ($_POST['action'] === 'login') {
            header('Location: ../index.php');
        } elseif ($_POST['action'] === 'register') {
            header('Location: ../pages/auth/login.php?registered=1');
        } elseif ($_POST['action'] === 'logout') {
            header('Location: ../index.php');
        }
        exit;
    }
}
?>