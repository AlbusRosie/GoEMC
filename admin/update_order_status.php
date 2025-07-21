<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền']);
    exit;
}

if (!isset($_POST['order_id']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Thiếu dữ liệu']);
    exit;
}

$orderId = intval($_POST['order_id']);
$status = $_POST['status'];

// Kiểm tra trạng thái hợp lệ
$validStatuses = ['pending', 'confirmed', 'preparing', 'shipping', 'delivered', 'cancelled', 'completed'];
if (!in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Trạng thái không hợp lệ']);
    exit;
}

// Gọi Controller
require_once __DIR__ . '/../controllers/ManagementCashController.php';
$p = new GQLcash();
$updated = $p->updateStatus($orderId, $status);

if ($updated) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể cập nhật trạng thái đơn hàng']);
}
