<?php
session_start();
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'controllers/Router.php';

// Đảm bảo biến $conn có sẵn globally
global $conn;

// Kiểm tra và xử lý lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Xử lý các action POST trước khi routing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'login' || $action === 'register') {
        require_once 'controllers/UserController.php';
        $controller = new UserController($conn);
        
        if ($action === 'login') {
            $controller->login();
            exit;
        } elseif ($action === 'register') {
            $controller->register();
            exit;
        }
    }
}

// Nếu là POST và Content-Type là application/json thì parse body vào $_POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $_POST = json_decode($raw, true) ?? [];
}

// Khởi tạo Router
$router = new Router();

// Lấy thông tin route hiện tại
$currentRoute = $router->getCurrentRoute();

// Nếu là API thì không include header/footer
if (strpos($currentRoute['page'], 'api/') === 0) {
    $router->dispatch();
} elseif ($currentRoute['is_admin']) {
    $router->dispatch();
} else {
    include 'includes/header.php';
    $router->dispatch();
    include 'includes/footer.php';
}
?> 