<?php
// Đảm bảo các constants được định nghĩa
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'EMCwood');
}
if (!defined('META_DESCRIPTION')) {
    define('META_DESCRIPTION', 'Chuyên thanh lý gỗ chất lượng cao, giá tốt nhất thị trường');
}
if (!defined('META_KEYWORDS')) {
    define('META_KEYWORDS', 'thanh lý gỗ, gỗ tự nhiên, gỗ công nghiệp, nội thất gỗ');
}
?>
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
                
                <!-- Cart -->
                <div class="col-lg-3 col-md-3">
                    <div class="user-actions d-flex justify-content-end align-items-center gap-3">
                        <a href="index.php?page=cart" class="btn btn-link text-dark p-0 text-decoration-none position-relative">
                            <i class="fas fa-shopping-cart fs-5"></i>
                            <span class="cart-count position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                <?php
                                try {
                                    global $conn;
                                    if ($conn) {
                                        require_once __DIR__ . '/../models/Cart.php';
                                        $cart = new Cart($conn);
                                        if (isset($_SESSION['user_id'])) {
                                            echo $cart->getCartCount($_SESSION['user_id']);
                                        } else {
                                            echo $cart->getCartCount(null, session_id());
                                        }
                                    } else {
                                        echo '0';
                                    }
                                } catch (Exception $e) {
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
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            Khuyến mãi <i class="fas fa-chevron-down ms-1"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?page=products&sale=1">Sản phẩm giảm giá</a></li>
                            <li><a class="dropdown-item" href="index.php?page=products&featured=1">Sản phẩm nổi bật</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=contact">Liên hệ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=store">Cửa hàng</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=about">Về EMCWood</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

