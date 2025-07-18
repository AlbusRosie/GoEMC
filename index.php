<?php
// Start output buffering to prevent headers already sent error
ob_start();

session_start();
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'controllers/Router.php';

// Đảm bảo biến $conn có sẵn globally
global $conn;

// Kiểm tra và xử lý lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
if ($currentRoute['is_api']) {
    $router->dispatch();
} elseif ($currentRoute['is_admin']) {
    $router->dispatch();
} else {
    // Sử dụng template system
    $content = '';
    ob_start();
    $router->dispatch();
    $content = ob_get_clean();
    
    // Include template với content
    include 'templates/main.php';
}

// Flush output buffer
ob_end_flush();
?> 