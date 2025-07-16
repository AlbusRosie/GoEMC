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

// Khởi tạo Router
$router = new Router();

// Lấy thông tin route hiện tại
$currentRoute = $router->getCurrentRoute();

// Kiểm tra xem có phải admin route không
if ($currentRoute['is_admin']) {
    // Admin routes - không include header/footer
    $router->dispatch();
} else {
    // Frontend routes - include header/footer
    // Xử lý logout trước khi include header
    $page = $_GET['page'] ?? 'home';

    if ($page === 'logout') {
        require_once 'controllers/UserController.php';
        $controller = new UserController($conn);
        $controller->logout();
        exit;
    }

    // Sau đó mới include header
    include 'includes/header.php';
    $router->dispatch();
    include 'includes/footer.php';
}
?> 