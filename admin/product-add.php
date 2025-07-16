<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../models/Product.php';
require_once '../models/Category.php';
require_once '../models/ProductOption.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$productModel = new Product($pdo);
$categoryModel = new Category($pdo);
$productOptionModel = new ProductOption($pdo);

$error_message = '';
$success_message = '';

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Debug: Log POST data
    error_log("POST data received: " . json_encode($_POST));
    
    $data = [
        'category_id' => $_POST['category_id'],
        'name' => trim($_POST['name']),
        'price' => (float)$_POST['price'],
        'stock' => (int)$_POST['stock'],
        'description' => trim($_POST['description']),
        'size' => trim($_POST['size']),
        'colors' => isset($_POST['colors']) ? $_POST['colors'] : [],
        'color' => isset($_POST['colors']) ? implode(', ', $_POST['colors']) : '', // For backward compatibility
        'sale' => !empty($_POST['sale']) ? (float)$_POST['sale'] : null,
        'is_available' => isset($_POST['is_available']) ? 1 : 0,
        'status' => $_POST['status']
    ];
    
    // Xử lý upload nhiều hình ảnh chính
    $main_images_array = [];
    if (isset($_FILES['main_images']) && is_array($_FILES['main_images']['name'])) {
        $upload_dir = '../assets/uploads/';
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        foreach ($_FILES['main_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['main_images']['error'][$key] == 0) {
                $file_extension = strtolower(pathinfo($_FILES['main_images']['name'][$key], PATHINFO_EXTENSION));
                
                if (in_array($file_extension, $allowed_extensions)) {
                    $filename = 'product_' . time() . '_main_' . $key . '.' . $file_extension;
                    $filepath = $upload_dir . $filename;
                    
                    if (move_uploaded_file($tmp_name, $filepath)) {
                        $main_images_array[] = 'assets/uploads/' . $filename;
                    }
                }
            }
        }
    }
    
    // Xử lý upload nhiều hình ảnh mô tả
    $description_images_array = [];
    if (isset($_FILES['description_images']) && is_array($_FILES['description_images']['name'])) {
        $upload_dir = '../assets/uploads/';
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        foreach ($_FILES['description_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['description_images']['error'][$key] == 0) {
                $file_extension = strtolower(pathinfo($_FILES['description_images']['name'][$key], PATHINFO_EXTENSION));
                
                if (in_array($file_extension, $allowed_extensions)) {
                    $filename = 'product_' . time() . '_des_' . $key . '.' . $file_extension;
                    $filepath = $upload_dir . $filename;
                    
                    if (move_uploaded_file($tmp_name, $filepath)) {
                        $description_images_array[] = 'assets/uploads/' . $filename;
                    }
                }
            }
        }
    }
    
    // Thêm mảng hình ảnh vào data
    $data['main_images_array'] = $main_images_array;
    $data['description_images_array'] = $description_images_array;
    
    // Lưu JSON cho backward compatibility
    $data['main_images'] = !empty($main_images_array) ? json_encode($main_images_array) : null;
    $data['description_images'] = !empty($description_images_array) ? json_encode($description_images_array) : null;
    
    // Xử lý detail products
    $detail_products = [];
    if (isset($_POST['detail_names']) && is_array($_POST['detail_names'])) {
        foreach ($_POST['detail_names'] as $key => $name) {
            if (!empty(trim($name))) {
                $detail_products[] = [
                    'name' => trim($name),
                    'description' => trim($_POST['detail_descriptions'][$key] ?? '')
                ];
            }
        }
    }
    
    // Debug: Log detail_products
    error_log("Detail products to add: " . json_encode($detail_products));
    
    // Validation
    if (empty($data['name'])) {
        $error_message = 'Tên sản phẩm không được để trống.';
    } elseif ($data['price'] <= 0) {
        $error_message = 'Giá sản phẩm phải lớn hơn 0.';
    } elseif ($data['stock'] < 0) {
        $error_message = 'Số lượng tồn kho không được âm.';
    } else {
        // Thêm sản phẩm
        $product_id = $productModel->create($data);
        if ($product_id && $product_id > 0) {
            // Debug: Log product_id
            error_log("Product created successfully with ID: " . $product_id);
            
            // Thêm detail products
            if (!empty($detail_products)) {
                foreach ($detail_products as $detail) {
                    try {
                        $productModel->addDetail($product_id, $detail);
                        error_log("Detail added successfully for product ID: " . $product_id);
                    } catch (Exception $e) {
                        error_log("Error adding detail for product ID " . $product_id . ": " . $e->getMessage());
                        throw $e;
                    }
                }
            }
            
            // Xử lý product options
            $options_data = [];
            if (isset($_POST['option_names']) && is_array($_POST['option_names'])) {
                foreach ($_POST['option_names'] as $key => $name) {
                    if (!empty(trim($name))) {
                        $option_data = [
                            'name' => trim($name),
                            'values' => []
                        ];
                        if (isset($_POST['option_values'][$key]) && is_array($_POST['option_values'][$key])) {
                            foreach ($_POST['option_values'][$key] as $value_key => $value) {
                                if (!empty(trim($value))) {
                                    $option_data['values'][] = [
                                        'value' => trim($value),
                                        'stock_quantity' => (int)($_POST['option_value_stocks'][$key][$value_key] ?? 0)
                                    ];
                                }
                            }
                        }
                        $options_data[] = $option_data;
                    }
                }
            }
            // Lưu product options
            if (!empty($options_data)) {
                try {
                    $result = $productOptionModel->saveProductOptions($product_id, $options_data);
                    
                    if ($result) {
                        error_log("Product options saved successfully for product ID: $product_id");
                    } else {
                        error_log("Failed to save product options for product ID: $product_id");
                    }
                } catch (Exception $e) {
                    // Log lỗi nhưng không dừng quá trình thêm sản phẩm
                    error_log("Lỗi khi lưu product options: " . $e->getMessage());
                    error_log("Stack trace: " . $e->getTraceAsString());
                }
            } else {
                error_log("No product options data to save");
            }
            
            $success_message = 'Thêm sản phẩm thành công!';
            // Reset form
            $data = [];
        } else {
            $error_message = 'Có lỗi xảy ra khi thêm sản phẩm.';
        }
    }
}

// Lấy danh sách danh mục
$categories = $categoryModel->getAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm sản phẩm - Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- CKEditor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.25rem;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 2px dashed #ddd;
        }
        
        /* Color Selection Styles */
        .color-option {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            cursor: pointer;
            border: 3px solid #ddd;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 12px;
            padding: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        
        .color-option:hover {
            transform: scale(1.15);
            border-color: #007bff;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }
        
        .color-option.selected {
            border: 4px solid #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.2);
            transform: scale(1.1);
        }
        
        .color-option.custom-color {
            background: linear-gradient(45deg, #ff0000, #00ff00, #0000ff, #ffff00, #ff00ff, #00ffff) !important;
        }
        
        .color-checkbox {
            position: absolute;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
        }
        
        .color-option:has(.color-checkbox:checked) {
            border: 4px solid #28a745;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.3);
            transform: scale(1.1);
        }
        
        .color-option:has(.color-checkbox:checked)::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-weight: bold;
            font-size: 24px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.9);
        }
        
        .color-options {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            margin-bottom: 10px;
        }
        
        .selected-colors-info {
            font-size: 14px;
            color: #666;
        }
        
        .selected-colors-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        
        .selected-color-badge {
            background-color: #007bff;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .no-colors-text {
            color: #999;
            font-style: italic;
        }
        .image-preview-item {
            position: relative;
            display: inline-block;
            margin: 5px;
        }
        .image-preview-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            border: 2px solid #ddd;
        }
        .remove-image {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .remove-image:hover {
            background: #c82333;
        }
        
        /* CKEditor responsive styles */
        .ck-editor__editable img {
            max-width: 100% !important;
            height: auto !important;
        }
        
        .ck-editor__editable table {
            max-width: 100% !important;
            overflow-x: auto !important;
        }
        
        .ck-editor__editable table img {
            max-width: 100% !important;
            height: auto !important;
        }
        
        /* Product Options Styles */
        .option-card {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            margin-bottom: 20px;
            background: #f8f9fa;
        }
        
        .option-header {
            background: #007bff;
            color: white;
            padding: 15px;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .option-body {
            padding: 20px;
        }
        
        .value-item {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
        }
        
        .option-preview {
            background: #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .option-preview h6 {
            color: #495057;
            margin-bottom: 10px;
        }
        
        .value-badge {
            background: #007bff;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin: 2px;
            display: inline-block;
        }
        
        .default-badge {
            background: #28a745;
        }
        
        .price-adjustment {
            color: #dc3545;
            font-weight: bold;
        }
        
        .stock-info {
            color: #6c757d;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">
                            <i class="fas fa-tree me-2"></i>Admin Panel
                        </h4>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="products.php">
                                <i class="fas fa-box me-2"></i>Sản phẩm
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="categories.php">
                                <i class="fas fa-tags me-2"></i>Danh mục
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="orders.php">
                                <i class="fas fa-shopping-cart me-2"></i>Đơn hàng
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users me-2"></i>Người dùng
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="contacts.php">
                                <i class="fas fa-envelope me-2"></i>Liên hệ
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog me-2"></i>Cài đặt
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link" href="../index.php">
                                <i class="fas fa-home me-2"></i>Về trang chủ
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Thêm sản phẩm mới</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="products.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                        </a>
                    </div>
                </div>

                <?php if($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- Add Product Form -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <!-- Basic Information -->
                                    <h5 class="mb-3">Thông tin cơ bản</h5>
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($data['name'] ?? ''); ?>" required>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="category_id" class="form-label">Danh mục <span class="text-danger">*</span></label>
                                                <select class="form-select" id="category_id" name="category_id" required>
                                                    <option value="">Chọn danh mục</option>
                                                    <?php foreach($categories as $category): ?>
                                                    <option value="<?php echo $category['id']; ?>" 
                                                            <?php echo ($data['category_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($category['name']); ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="stock" class="form-label">Số lượng tồn kho</label>
                                                <input type="number" class="form-control" id="stock" name="stock" 
                                                       value="<?php echo $data['stock'] ?? 0; ?>" min="0">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="price" class="form-label">Giá gốc <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="price" name="price" 
                                                           value="<?php echo $data['price'] ?? ''; ?>" min="0" step="1000" required>
                                                    <span class="input-group-text">₫</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="sale" class="form-label">Giá khuyến mãi</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" id="sale" name="sale" 
                                                           value="<?php echo $data['sale'] ?? ''; ?>" min="0" step="1000">
                                                    <span class="input-group-text">₫</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="size" class="form-label">Kích thước</label>
                                                <input type="text" class="form-control" id="size" name="size" 
                                                       value="<?php echo htmlspecialchars($data['size'] ?? ''); ?>" 
                                                       placeholder="VD: 4x8 feet">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Màu sắc (có thể chọn nhiều)</label>
                                                <div class="color-selection">
                                                    <div class="color-options d-flex flex-wrap gap-2 mb-2">
                                                        <div class="color-option" data-color="Nâu tự nhiên" data-hex="#8B4513" style="background-color: #8B4513;" title="Nâu tự nhiên">
                                                            <input type="checkbox" class="color-checkbox" name="colors[]" value="Nâu tự nhiên" <?php echo in_array('Nâu tự nhiên', $data['colors'] ?? []) ? 'checked' : ''; ?>>
                                                        </div>
                                                        <div class="color-option" data-color="Nâu đậm" data-hex="#654321" style="background-color: #654321;" title="Nâu đậm">
                                                            <input type="checkbox" class="color-checkbox" name="colors[]" value="Nâu đậm" <?php echo in_array('Nâu đậm', $data['colors'] ?? []) ? 'checked' : ''; ?>>
                                                        </div>
                                                        <div class="color-option" data-color="Nâu sáng" data-hex="#D2691E" style="background-color: #D2691E;" title="Nâu sáng">
                                                            <input type="checkbox" class="color-checkbox" name="colors[]" value="Nâu sáng" <?php echo in_array('Nâu sáng', $data['colors'] ?? []) ? 'checked' : ''; ?>>
                                                        </div>
                                                        <div class="color-option" data-color="Đen" data-hex="#000000" style="background-color: #000000;" title="Đen">
                                                            <input type="checkbox" class="color-checkbox" name="colors[]" value="Đen" <?php echo in_array('Đen', $data['colors'] ?? []) ? 'checked' : ''; ?>>
                                                        </div>
                                                        <div class="color-option" data-color="Trắng" data-hex="#FFFFFF" style="background-color: #FFFFFF; border: 1px solid #ddd;" title="Trắng">
                                                            <input type="checkbox" class="color-checkbox" name="colors[]" value="Trắng" <?php echo in_array('Trắng', $data['colors'] ?? []) ? 'checked' : ''; ?>>
                                                        </div>
                                                        <div class="color-option" data-color="Xám" data-hex="#808080" style="background-color: #808080;" title="Xám">
                                                            <input type="checkbox" class="color-checkbox" name="colors[]" value="Xám" <?php echo in_array('Xám', $data['colors'] ?? []) ? 'checked' : ''; ?>>
                                                        </div>
                                                        <div class="color-option" data-color="Kem" data-hex="#F5F5DC" style="background-color: #F5F5DC; border: 1px solid #ddd;" title="Kem">
                                                            <input type="checkbox" class="color-checkbox" name="colors[]" value="Kem" <?php echo in_array('Kem', $data['colors'] ?? []) ? 'checked' : ''; ?>>
                                                        </div>
                                                        <div class="color-option" data-color="Vàng" data-hex="#FFD700" style="background-color: #FFD700;" title="Vàng">
                                                            <input type="checkbox" class="color-checkbox" name="colors[]" value="Vàng" <?php echo in_array('Vàng', $data['colors'] ?? []) ? 'checked' : ''; ?>>
                                                        </div>
                                                        <div class="color-option" data-color="Đỏ" data-hex="#FF0000" style="background-color: #FF0000;" title="Đỏ">
                                                            <input type="checkbox" class="color-checkbox" name="colors[]" value="Đỏ" <?php echo in_array('Đỏ', $data['colors'] ?? []) ? 'checked' : ''; ?>>
                                                        </div>
                                                        <div class="color-option" data-color="Hồng" data-hex="#FFC0CB" style="background-color: #FFC0CB;" title="Hồng">
                                                            <input type="checkbox" class="color-checkbox" name="colors[]" value="Hồng" <?php echo in_array('Hồng', $data['colors'] ?? []) ? 'checked' : ''; ?>>
                                                        </div>
                                                        <div class="color-option" data-color="Xanh lá" data-hex="#228B22" style="background-color: #228B22;" title="Xanh lá">
                                                            <input type="checkbox" class="color-checkbox" name="colors[]" value="Xanh lá" <?php echo in_array('Xanh lá', $data['colors'] ?? []) ? 'checked' : ''; ?>>
                                                        </div>
                                                        <div class="color-option" data-color="Xanh dương" data-hex="#0000FF" style="background-color: #0000FF;" title="Xanh dương">
                                                            <input type="checkbox" class="color-checkbox" name="colors[]" value="Xanh dương" <?php echo in_array('Xanh dương', $data['colors'] ?? []) ? 'checked' : ''; ?>>
                                                        </div>
                                                        <div class="color-option" data-color="Tím" data-hex="#800080" style="background-color: #800080;" title="Tím">
                                                            <input type="checkbox" class="color-checkbox" name="colors[]" value="Tím" <?php echo in_array('Tím', $data['colors'] ?? []) ? 'checked' : ''; ?>>
                                                        </div>
                                                        <div class="color-option" data-color="Cam" data-hex="#FFA500" style="background-color: #FFA500;" title="Cam">
                                                            <input type="checkbox" class="color-checkbox" name="colors[]" value="Cam" <?php echo in_array('Cam', $data['colors'] ?? []) ? 'checked' : ''; ?>>
                                                        </div>
                                                        <div class="color-option custom-color" data-color="Khác" data-hex="" style="background: linear-gradient(45deg, #ff0000, #00ff00, #0000ff);" title="Khác (tùy chỉnh)">
                                                            <input type="checkbox" class="color-checkbox" name="colors[]" value="Khác" <?php echo in_array('Khác', $data['colors'] ?? []) ? 'checked' : ''; ?>>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Selected colors display -->
                                                    <div class="selected-colors-info mt-2">
                                                        <div class="selected-colors-list">
                                                            <?php 
                                                            $selectedColors = $data['colors'] ?? [];
                                                            if (!empty($selectedColors)): 
                                                                foreach($selectedColors as $color): ?>
                                                                <span class="selected-color-badge"><?php echo htmlspecialchars($color); ?></span>
                                                            <?php endforeach; endif; ?>
                                                            <span class="no-colors-text" <?php echo empty($selectedColors) ? '' : 'style="display:none;"'; ?>>Chưa chọn màu nào</span>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Custom Color Input -->
                                                    <div id="customColorInput" class="mt-2" style="display: none;">
                                                        <input type="text" class="form-control" id="custom_color" name="custom_color" 
                                                               value="<?php echo htmlspecialchars($data['custom_color'] ?? ''); ?>" 
                                                               placeholder="Nhập màu sắc tùy chỉnh...">
                                                        <small class="text-muted">Nhập màu sắc khác nếu không có trong danh sách</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Mô tả sản phẩm</label>
                                        <textarea class="form-control" id="description" name="description" rows="8" 
                                                  placeholder="Mô tả chi tiết về sản phẩm..."><?php echo htmlspecialchars($data['description'] ?? ''); ?></textarea>
                                        <small class="text-muted">Sử dụng rich text editor để định dạng văn bản và thêm hình ảnh</small>
                                    </div>

                                    <!-- Product Options Section -->
                                    <div class="mb-4">
                                        <h5 class="mb-3">
                                            <i class="fas fa-cogs me-2 text-primary"></i>Tùy chọn sản phẩm
                                        </h5>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Hướng dẫn:</strong> Thêm các tùy chọn để khách hàng có thể lựa chọn khi mua sản phẩm (VD: Kích thước, Màu sắc, Chất liệu)
                                        </div>
                                        
                                        <div id="product-options-container" class="mb-3">
                                            <!-- Options sẽ được thêm vào đây -->
                                            <div class="text-center text-muted py-4">
                                                <i class="fas fa-plus-circle fa-2x mb-2"></i>
                                                <p>Chưa có tùy chọn nào. Hãy thêm tùy chọn đầu tiên!</p>
                                            </div>
                                        </div>
                                        
                                        <button type="button" class="btn btn-primary" onclick="addProductOption()">
                                            <i class="fas fa-plus me-1"></i>Thêm tùy chọn sản phẩm
                                        </button>
                                        <small class="text-muted d-block mt-2">Thêm các tùy chọn như kích thước, màu sắc, chất liệu để khách hàng có thể lựa chọn</small>
                                    </div>

                                    <!-- Detail Products Section -->
                                    <div class="mb-3">
                                        <label class="form-label">Chi tiết sản phẩm</label>
                                        <div id="detail-products-container">
                                            <div class="detail-product-item mb-3 p-3 border rounded">
                                                <div class="row">
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="detail_names[]" 
                                                               placeholder="Tên chi tiết (VD: Kích thước, Chất liệu)" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <textarea class="form-control" name="detail_descriptions[]" rows="2" 
                                                                  placeholder="Mô tả chi tiết..."></textarea>
                                                    </div>
                                                    <div class="col-md-1">
                                                        <button type="button" class="btn btn-danger btn-sm" onclick="removeDetailProduct(this)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-success btn-sm" onclick="addDetailProduct()">
                                            <i class="fas fa-plus me-1"></i>Thêm chi tiết
                                        </button>
                                        <small class="text-muted d-block mt-1">Thêm các thông số kỹ thuật, đặc điểm của sản phẩm</small>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="status" class="form-label">Trạng thái</label>
                                                <select class="form-select" id="status" name="status">
                                                    <option value="active" <?php echo ($data['status'] ?? '') == 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                                                    <option value="inactive" <?php echo ($data['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Không hoạt động</option>
                                                    <option value="out_of_stock" <?php echo ($data['status'] ?? '') == 'out_of_stock' ? 'selected' : ''; ?>>Hết hàng</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <div class="form-check mt-4">
                                                    <input class="form-check-input" type="checkbox" id="is_available" name="is_available" 
                                                           <?php echo ($data['is_available'] ?? 1) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="is_available">
                                                        Có sẵn để bán
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <!-- Image Upload -->
                                    <h5 class="mb-3">Hình ảnh sản phẩm</h5>
                                    
                                    <div class="mb-3">
                                        <label for="main_images" class="form-label">Hình ảnh chính</label>
                                        <input type="file" class="form-control" id="main_images" name="main_images[]" 
                                               accept="image/*" multiple onchange="previewMultipleImages(this, 'main_preview_container')">
                                        <small class="text-muted">Có thể chọn nhiều hình ảnh</small>
                                        <div id="main_preview_container" class="mt-2 d-flex flex-wrap gap-2"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description_images" class="form-label">Hình ảnh mô tả</label>
                                        <input type="file" class="form-control" id="description_images" name="description_images[]" 
                                               accept="image/*" multiple onchange="previewMultipleImages(this, 'description_preview_container')">
                                        <small class="text-muted">Có thể chọn nhiều hình ảnh</small>
                                        <div id="description_preview_container" class="mt-2 d-flex flex-wrap gap-2"></div>
                                    </div>

                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Lưu ý:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>Hỗ trợ định dạng: JPG, JPEG, PNG, GIF, WEBP</li>
                                            <li>Kích thước tối đa: 5MB mỗi hình</li>
                                            <li>Có thể upload tối đa 10 hình mỗi loại</li>
                                            <li>Hình ảnh sẽ được tự động resize</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-end gap-2">
                                <a href="products.php" class="btn btn-secondary">Hủy</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Thêm sản phẩm
                                </button>
                            </div>
                            
                            <!-- Note about product options -->
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Lưu ý:</strong> Sau khi thêm sản phẩm, bạn có thể quản lý các tùy chọn sản phẩm (như kích thước, màu sắc, v.v.) trong trang chỉnh sửa sản phẩm.
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const file = input.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            } else {
                preview.classList.add('d-none');
            }
        }
        
        function previewMultipleImages(input, containerId) {
            const container = document.getElementById(containerId);
            container.innerHTML = '';
            
            if (input.files) {
                Array.from(input.files).forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'preview-image';
                        img.style.maxWidth = '100px';
                        img.style.maxHeight = '100px';
                        img.style.marginRight = '10px';
                        img.style.marginBottom = '10px';
                        img.style.borderRadius = '8px';
                        img.style.border = '2px solid #ddd';
                        container.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                });
            }
        }
        
        function updateSelectedColors() {
            const selectedColors = [];
            const checkboxes = document.querySelectorAll('.color-checkbox:checked');
            
            checkboxes.forEach(checkbox => {
                selectedColors.push(checkbox.value);
            });
            
            // Update display
            const selectedColorsList = document.querySelector('.selected-colors-list');
            const noColorsText = document.querySelector('.no-colors-text');
            
            // Clear existing badges
            const existingBadges = selectedColorsList.querySelectorAll('.selected-color-badge');
            existingBadges.forEach(badge => badge.remove());
            
            // Add new badges
            selectedColors.forEach(color => {
                const badge = document.createElement('span');
                badge.className = 'selected-color-badge';
                badge.textContent = color;
                selectedColorsList.appendChild(badge);
            });
            
            // Show/hide no colors text
            if (selectedColors.length === 0) {
                noColorsText.style.display = 'inline';
            } else {
                noColorsText.style.display = 'none';
            }
            
            // Handle custom color option
            const customColorInput = document.getElementById('customColorInput');
            if (selectedColors.includes('Khác')) {
                customColorInput.style.display = 'block';
            } else {
                customColorInput.style.display = 'none';
            }
        }
        
        // Detail Products Functions
        function addDetailProduct() {
            const container = document.getElementById('detail-products-container');
            const newItem = document.createElement('div');
            newItem.className = 'detail-product-item mb-3 p-3 border rounded';
            newItem.innerHTML = `
                <div class="row">
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="detail_names[]" 
                               placeholder="Tên chi tiết (VD: Kích thước, Chất liệu)" required>
                    </div>
                    <div class="col-md-6">
                        <textarea class="form-control" name="detail_descriptions[]" rows="2" 
                                  placeholder="Mô tả chi tiết..."></textarea>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeDetailProduct(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(newItem);
        }
        
        function removeDetailProduct(button) {
            const container = document.getElementById('detail-products-container');
            const items = container.querySelectorAll('.detail-product-item');
            
            if (items.length > 1) {
                button.closest('.detail-product-item').remove();
            } else {
                alert('Phải có ít nhất một chi tiết sản phẩm!');
            }
        }
        
        // Product Options Functions
        let optionCounter = 0;
        
        function addProductOption() {
            const container = document.getElementById('product-options-container');
            
            // Xóa thông báo "chưa có tùy chọn" nếu có
            const emptyMessage = container.querySelector('.text-center.text-muted');
            if (emptyMessage) {
                emptyMessage.remove();
            }
            
            const optionDiv = document.createElement('div');
            optionDiv.className = 'option-card';
            optionDiv.innerHTML = `
                <div class="option-header">
                    <h6 class="mb-0">
                        <i class="fas fa-cog me-2"></i>Tùy chọn #${optionCounter + 1}
                    </h6>
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeProductOption(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="option-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tên tùy chọn <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="option_names[]" 
                                       placeholder="VD: Kích thước, Màu sắc, Chất liệu" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mô tả tùy chọn</label>
                        <textarea class="form-control" name="option_descriptions[]" rows="2" 
                                  placeholder="Mô tả chi tiết về tùy chọn này..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="option_required[]" value="${optionCounter}">
                            <label class="form-check-label">
                                <i class="fas fa-exclamation-triangle text-warning me-1"></i>Bắt buộc chọn
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-list me-1"></i>Các giá trị tùy chọn
                        </label>
                        <div class="values-container">
                            <!-- Giá trị sẽ được thêm vào đây -->
                        </div>
                        <button type="button" class="btn btn-success btn-sm" onclick="addOptionValue(this)">
                            <i class="fas fa-plus me-1"></i>Thêm giá trị
                        </button>
                        <small class="text-muted d-block mt-1">Thêm các giá trị mà khách hàng có thể chọn (VD: S, M, L, XL cho kích thước)</small>
                    </div>
                    
                    <!-- Option Preview -->
                    <div class="option-preview">
                        <h6><i class="fas fa-eye me-1"></i>Xem trước tùy chọn:</h6>
                        <div class="option-preview-content">
                            <span class="text-muted">Chưa có giá trị</span>
                        </div>
                    </div>
                </div>
            `;
            
            container.appendChild(optionDiv);
            optionCounter++;
            
            // Thêm giá trị mặc định đầu tiên
            const newOptionCard = container.lastElementChild;
            const addValueButton = newOptionCard.querySelector('.btn-success');
            addOptionValue(addValueButton);
        }
        
        function removeProductOption(button) {
            const optionCard = button.closest('.option-card');
            const container = document.getElementById('product-options-container');
            
            optionCard.remove();
            
            // Kiểm tra nếu không còn tùy chọn nào, hiển thị thông báo
            const remainingOptions = container.querySelectorAll('.option-card');
            if (remainingOptions.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-plus-circle fa-2x mb-2"></i>
                        <p>Chưa có tùy chọn nào. Hãy thêm tùy chọn đầu tiên!</p>
                    </div>
                `;
            }
        }
        
        function addOptionValue(button) {
            const valuesContainer = button.previousElementSibling;
            const optionCard = button.closest('.option-card');
            const optionIndex = Array.from(optionCard.parentNode.children).indexOf(optionCard);
            
            // Đếm số value hiện tại để tạo index mới
            const currentValues = valuesContainer.querySelectorAll('.value-item');
            const valueIndex = currentValues.length;
            
            const valueDiv = document.createElement('div');
            valueDiv.className = 'value-item';
            valueDiv.innerHTML = `
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="option_values[${optionIndex}][]" placeholder="Giá trị" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control" name="option_value_stocks[${optionIndex}][]" placeholder="Tồn kho" min="0">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeOptionValue(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            
            valuesContainer.appendChild(valueDiv);
        }
        
        function removeOptionValue(button) {
            const valueItem = button.closest('.value-item');
            valueItem.remove();
        }
        
        // Update preview when inputs change
        document.addEventListener('input', function(e) {
            if (e.target.name && (e.target.name.includes('option_names') || e.target.name.includes('option_values'))) {
                updateOptionPreview(e.target);
            }
        });
        
        // Update preview when checkboxes change
        document.addEventListener('change', function(e) {
            if (e.target.name && (e.target.name.includes('option_value_defaults') || e.target.name.includes('option_required'))) {
                const optionCard = e.target.closest('.option-card');
                if (optionCard) {
                    const optionNameInput = optionCard.querySelector('input[name="option_names[]"]');
                    if (optionNameInput) {
                        updateOptionPreview(optionNameInput);
                    }
                }
            }
        });
        
        function updateOptionPreview(input) {
            const optionCard = input.closest('.option-card');
            const previewContent = optionCard.querySelector('.option-preview-content');
            const optionName = optionCard.querySelector('input[name="option_names[]"]').value;
            
            if (!optionName) {
                previewContent.innerHTML = '<span class="text-muted">Chưa có giá trị</span>';
                return;
            }
            
            const values = [];
            const valueInputs = optionCard.querySelectorAll('input[name*="option_values"]');
            
            valueInputs.forEach((valueInput, index) => {
                if (valueInput.value.trim()) {
                    const stockInput = optionCard.querySelectorAll('input[name*="option_value_stocks"]')[index];
                    
                    let badgeClass = 'value-badge';
                    
                    let badge = `<span class="${badgeClass}">${valueInput.value}`;
                    
                    if (stockInput && stockInput.value > 0) {
                        badge += ` <span class="stock-info">(Còn: ${stockInput.value})</span>`;
                    }
                    
                    badge += '</span>';
                    values.push(badge);
                }
            });
            
            if (values.length > 0) {
                previewContent.innerHTML = `<strong>${optionName}:</strong> ${values.join(' ')}`;
            } else {
                previewContent.innerHTML = `<strong>${optionName}:</strong> <span class="text-muted">Chưa có giá trị</span>`;
            }
        }
        
        // Initialize color selection on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Add change event to all color checkboxes
            document.querySelectorAll('.color-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateSelectedColors();
                });
            });
            
            // Initial update
            updateSelectedColors();
            
            // Initialize CKEditor
            ClassicEditor
                .create(document.querySelector('#description'), {
                    toolbar: {
                        items: [
                            'heading',
                            '|',
                            'bold',
                            'italic',
                            'link',
                            'bulletedList',
                            'numberedList',
                            '|',
                            'outdent',
                            'indent',
                            '|',
                            'imageUpload',
                            'blockQuote',
                            'insertTable',
                            'mediaEmbed',
                            'undo',
                            'redo'
                        ]
                    },
                    image: {
                        resizeOptions: [
                            {
                                name: 'imageOriginal',
                                value: null,
                                label: 'Original'
                            },
                            {
                                name: 'image50',
                                value: '50',
                                label: '50%'
                            },
                            {
                                name: 'image75',
                                value: '75',
                                label: '75%'
                            }
                        ],
                        resizeUnit: '%'
                    },
                    simpleUpload: {
                        uploadUrl: 'upload-image.php'
                    },
                    language: 'vi'
                })
                .then(editor => {
                    console.log('CKEditor initialized successfully');
                    
                    // Add error handling for image upload
                    editor.plugins.get('FileRepository').createUploadAdapter = function(loader) {
                        return {
                            upload: function() {
                                return loader.file.then(function(file) {
                                    return new Promise(function(resolve, reject) {
                                        const formData = new FormData();
                                        formData.append('upload', file);
                                        
                                        fetch('upload-image.php', {
                                            method: 'POST',
                                            body: formData
                                        })
                                        .then(response => response.json())
                                        .then(result => {
                                            if (result.uploaded) {
                                                resolve({
                                                    default: result.url
                                                });
                                            } else {
                                                reject(result.error || 'Upload failed');
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Upload error:', error);
                                            reject('Upload failed');
                                        });
                                    });
                                });
                            }
                        };
                    };
                })
                .catch(error => {
                    console.error('CKEditor error:', error);
                });
        });
    </script>
</body>
</html> 