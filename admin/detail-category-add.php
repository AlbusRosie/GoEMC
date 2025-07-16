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
    $category_id = (int)$_POST['category_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    // Validation
    if (empty($name)) {
        header('Location: detail-categories.php?error=empty_name');
        exit;
    }
    
    if (!$category_id) {
        header('Location: detail-categories.php?error=invalid_category');
        exit;
    }
    
    // Thêm detail category
    $data = [
        'name' => $name,
        'description' => $description
    ];
    
    if ($categoryModel->addDetail($category_id, $data)) {
        header('Location: detail-categories.php?success=1');
    } else {
        header('Location: detail-categories.php?error=add_failed');
    }
    exit;
}

// Nếu không phải POST, chuyển về trang detail categories
header('Location: detail-categories.php');
exit;
?> 