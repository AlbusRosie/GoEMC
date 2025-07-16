<?php
// Include helpers
require_once __DIR__ . '/helpers.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Thanh lý gỗ chất lượng cao</title>
    <meta name="description" content="<?php echo META_DESCRIPTION; ?>">
    <meta name="keywords" content="<?php echo META_KEYWORDS; ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
    <!-- Header -->
    <header class="header-main">
        <!-- Main Header -->
        <div class="main-header py-3">
            <div class="container">
                <div class="row align-items-center">
                    <!-- Logo -->
                    <div class="col-lg-3 col-md-4">
                        <a class="navbar-brand" href="index.php">
                            <span class="brand-text">EMCwood.</span>
                            <span class="brand-dot"></span>
                        </a>
                    </div>
                    
                    <!-- Search Bar -->
                    <div class="col-lg-6 col-md-5">
                        <div class="search-container">
                            <form class="search-form" action="index.php" method="GET">
                                <input type="hidden" name="page" value="products">
                                <div class="input-group">
                                    <input type="text" class="form-control search-input" 
                                           name="search" placeholder="Tìm kiếm sản phẩm..." 
                                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                    <button class="btn search-btn" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- User Actions - Redesigned -->
                    <div class="col-lg-3 col-md-3">
                        <div class="user-actions d-flex justify-content-end align-items-center gap-3">
                            <!-- 3D House Button -->
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-cube me-1"></i>3D HOUSE
                            </a>
                            
                            <!-- User Account -->
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <div class="dropdown">
                                    <button class="btn btn-link text-dark p-0" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-user-circle fs-5"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="index.php?page=profile"><i class="fas fa-user me-2"></i>Hồ sơ</a></li>
                                        <li><a class="dropdown-item" href="index.php?page=orders"><i class="fas fa-shopping-bag me-2"></i>Đơn hàng</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <a href="index.php?page=login" class="btn btn-link text-dark p-0">
                                    <i class="fas fa-user-circle fs-5"></i>
                                </a>
                            <?php endif; ?>
                            
                            <!-- Cart -->
                            <a href="index.php?page=cart" class="btn btn-link text-dark p-0 position-relative">
                                <i class="fas fa-shopping-cart fs-5"></i>
                                <span class="cart-count position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                    <?php
                                    // Hiển thị số lượng sản phẩm trong giỏ hàng
                                    global $conn;
                                    if ($conn) {
                                        if (isset($_SESSION['user_id'])) {
                                            require_once __DIR__ . '/../models/Cart.php';
                                            $cart = new Cart($conn);
                                            echo $cart->getCartCount($_SESSION['user_id']);
                                        } else {
                                            require_once __DIR__ . '/../models/Cart.php';
                                            $cart = new Cart($conn);
                                            echo $cart->getCartCount(null, session_id());
                                        }
                                    } else {
                                        echo '0';
                                    }
                                    ?>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-top">
            <div class="container">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Sản phẩm <i class="fas fa-chevron-down ms-1"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="index.php?page=products&category=1">Gỗ tự nhiên</a></li>
                                <li><a class="dropdown-item" href="index.php?page=products&category=2">Gỗ công nghiệp</a></li>
                                <li><a class="dropdown-item" href="index.php?page=products&category=3">Nội thất gỗ</a></li>
                                <li><a class="dropdown-item" href="index.php?page=products&category=4">Ván gỗ</a></li>
                                <li><a class="dropdown-item" href="index.php?page=products&category=5">Gỗ tấm</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="index.php?page=products&sale=1">Sản phẩm giảm giá</a></li>
                                <li><a class="dropdown-item" href="index.php?page=products&featured=1">Sản phẩm nổi bật</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=contact">Liên hệ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=about">Về EMCWood</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=stores">Cửa hàng</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content --> 