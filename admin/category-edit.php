<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../models/Category.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$categoryModel = new Category($pdo);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $status = $_POST['status'];
    
    // Validation
    if (empty($name)) {
        header('Location: categories.php?error=empty_name');
        exit;
    }
    
    // Kiểm tra tên danh mục đã tồn tại (trừ danh mục hiện tại)
    if ($categoryModel->nameExists($name, $id)) {
        header('Location: categories.php?error=name_exists');
        exit;
    }
    
    // Cập nhật danh mục
    $data = [
        'name' => $name,
        'status' => $status
    ];
    
    if ($categoryModel->update($id, $data)) {
        header('Location: categories.php?success=1');
    } else {
        header('Location: categories.php?error=update_failed');
    }
    exit;
}

// Nếu không phải POST, chuyển về trang danh mục
header('Location: categories.php');
exit;
?> 