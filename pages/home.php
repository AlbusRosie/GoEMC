<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

// Khởi tạo kết nối database
$db = new Database();
$conn = $db->getConnection();

// Khởi tạo model
$productModel = new Product($conn);
$categoryModel = new Category($conn);

// Lấy sản phẩm nổi bật cho hero section
$featuredProduct = $productModel->getFeaturedProduct();

// Lấy sản phẩm bán chạy
$bestSellers = $productModel->getBestSellers(8);

// Lấy sản phẩm gợi ý
$suggestedProducts = $productModel->getSuggestedProducts(8);

// Lấy sản phẩm hot deal
$hotDeals = $productModel->getHotDeals(8);

// Lấy sản phẩm mới
$newProducts = $productModel->getNewProducts(4);

// Lấy gallery ảnh cho từng sản phẩm
if ($featuredProduct) {
    $featuredProduct['gallery'] = $productModel->getProductImages($featuredProduct['id'], 'main');
}

foreach ($newProducts as &$product) {
    $product['gallery'] = $productModel->getProductImages($product['id'], 'main');
}
unset($product);

foreach ($bestSellers as &$product) {
    $product['gallery'] = $productModel->getProductImages($product['id'], 'main');
}
unset($product);

foreach ($suggestedProducts as &$product) {
    $product['gallery'] = $productModel->getProductImages($product['id'], 'main');
}
unset($product);

foreach ($hotDeals as &$product) {
    $product['gallery'] = $productModel->getProductImages($product['id'], 'main');
}
unset($product);
?>

<style>
/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 30%, #fafafa 70%, #f8f9fa 100%);
    padding: 10px 0;
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="0.3" fill="%23000" opacity="0.02"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    pointer-events: none;
}

.hero-content {
    position: relative;
    z-index: 2;
}

.hero-badge {
    display: inline-block;
    background: #1a1a1a;
    color: white;
    padding: 0.8rem 1.5rem;
    border-radius: 2px;
    font-size: 0.7rem;
    font-weight: 600;
    margin-bottom: 2.5rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    border-left: 3px solid #ff6b35;
}

.hero-title {
    font-family: 'Playfair Display', serif;
    font-size: 4.5rem;
    font-weight: 300;
    color: #1a1a1a;
    margin-bottom: 1rem;
    line-height: 1.1;
    letter-spacing: -2px;
}

.hero-subtitle {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 400;
    color: #666;
    margin-bottom: 2.5rem;
    letter-spacing: 1px;
}

.hero-price {
    font-size: 3.5rem;
    font-weight: 300;
    color: #1a1a1a;
    margin-bottom: 1rem;
    letter-spacing: -1px;
}

.hero-original-price {
    font-size: 1.4rem;
    color: #999;
    text-decoration: line-through;
    margin-bottom: 1rem;
    font-weight: 300;
}

.hero-expiry {
    font-size: 0.85rem;
    color: #666;
    margin-bottom: 3.5rem;
    font-weight: 400;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.hero-image-container {
    position: relative;
    border-radius: 0;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

.hero-image {
    width: 100%;
    height: 600px;
    object-fit: cover;
}

/* Section Styles */
.section-title {
    font-family: 'Playfair Display', serif;
    font-size: 3rem;
    font-weight: 300;
    color: #1a1a1a;
    margin-bottom: 1.5rem;
    letter-spacing: -1px;
}

.section-subtitle {
    font-size: 1.1rem;
    color: #666;
    margin-bottom: 5rem;
    font-weight: 300;
    line-height: 1.8;
    letter-spacing: 0.5px;
}

.view-more-link {
    color: #1a1a1a;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
    display: inline-flex;
    align-items: center;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    border-bottom: 1px solid #1a1a1a;
    padding-bottom: 2px;
}

.view-more-link:hover {
    color: #ff6b35;
    border-bottom-color: #ff6b35;
}

/* Product Card */
.product-card {
    background: white;
    border-radius: 0;
    overflow: hidden;
    box-shadow: 0 5px 25px rgba(0,0,0,0.08);
    transition: box-shadow 0.3s ease;
    height: 100%;
    border: 1px solid #f0f0f0;
}

.product-card:hover {
    box-shadow: 0 15px 40px rgba(0,0,0,0.12);
}

.product-link {
    text-decoration: none;
    color: inherit;
    display: block;
    height: 100%;
}

.product-link:hover {
    text-decoration: none;
    color: inherit;
}

.product-image {
    position: relative;
    height: 300px;
    overflow: hidden;
    background: #f8f9fa;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: opacity 0.3s ease;
}

.product-card:hover .product-image img {
    opacity: 0.9;
}

.discount-badge {
    position: absolute;
    top: 20px;
    left: 20px;
    background: #1a1a1a;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0;
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.product-info {
    padding: 2rem;
}

.product-name {
    font-size: 1.1rem;
    font-weight: 400;
    color: #1a1a1a;
    margin-bottom: 1.2rem;
    line-height: 1.5;
    letter-spacing: 0.2px;
    font-family: 'Playfair Display', serif;
}

.product-price {
    margin-bottom: 1rem;
}

.current-price {
    font-size: 1.4rem;
    font-weight: 400;
    color: #1a1a1a;
    letter-spacing: -0.5px;
}

.original-price {
    font-size: 1rem;
    color: #999;
    text-decoration: line-through;
    margin-left: 1rem;
    font-weight: 300;
}

.product-sales {
    font-size: 0.75rem;
    color: #666;
    font-weight: 300;
    letter-spacing: 1px;
    text-transform: uppercase;
}

/* Service Cards */
.service-card {
    background: white;
    padding: 3rem 2rem;
    border-radius: 0;
    text-align: center;
    box-shadow: 0 5px 25px rgba(0,0,0,0.08);
    transition: box-shadow 0.3s ease;
    height: 100%;
    border: 1px solid #f0f0f0;
    position: relative;
}

.service-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: #1a1a1a;
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.service-card:hover::before {
    transform: scaleX(1);
}

.service-card:hover {
    box-shadow: 0 15px 40px rgba(0,0,0,0.12);
}

.service-icon {
    width: 80px;
    height: 80px;
    background: #f8f9fa;
    border-radius: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 2rem;
    transition: background-color 0.3s ease;
    border: 1px solid #e0e0e0;
}

.service-card:hover .service-icon {
    background: #1a1a1a;
}

.service-icon i {
    font-size: 1.8rem;
    color: #1a1a1a;
    transition: color 0.3s ease;
}

.service-card:hover .service-icon i {
    color: white;
}

.service-title {
    font-size: 1.2rem;
    font-weight: 400;
    color: #1a1a1a;
    margin-bottom: 1rem;
    letter-spacing: 0.5px;
    font-family: 'Playfair Display', serif;
}

.service-subtitle {
    font-size: 0.9rem;
    color: #666;
    margin: 0;
    font-weight: 300;
    line-height: 1.6;
    letter-spacing: 0.3px;
}

/* Review Cards */
.review-card {
    background: white;
    padding: 3rem 2.5rem;
    border-radius: 0;
    text-align: center;
    box-shadow: 0 5px 25px rgba(0,0,0,0.08);
    transition: box-shadow 0.3s ease;
    height: 100%;
    border: 1px solid #f0f0f0;
    position: relative;
}

.review-card::before {
    content: '"';
    position: absolute;
    top: 30px;
    left: 40px;
    font-size: 5rem;
    color: #f0f0f0;
    font-family: 'Playfair Display', serif;
    font-weight: 300;
    line-height: 1;
}

.review-card:hover {
    box-shadow: 0 15px 40px rgba(0,0,0,0.12);
}

.review-image {
    width: 100px;
    height: 100px;
    border-radius: 0;
    object-fit: cover;
    margin: 0 auto 2rem;
    border: 2px solid #1a1a1a;
}

.reviewer-name {
    font-size: 1.3rem;
    font-weight: 400;
    color: #1a1a1a;
    margin-bottom: 1.5rem;
    letter-spacing: 0.5px;
    font-family: 'Playfair Display', serif;
}

.review-text {
    font-size: 1rem;
    color: #666;
    line-height: 1.8;
    font-style: italic;
    margin: 0;
    font-weight: 300;
    letter-spacing: 0.3px;
}



/* Responsive */
@media (max-width: 768px) {
    .hero-title {
        font-size: 3rem;
    }
    
    .hero-subtitle {
        font-size: 2rem;
    }
    
    .hero-price {
        font-size: 2.8rem;
    }
    
    .section-title {
        font-size: 2.5rem;
    }
    
    .hero-image {
        height: 400px;
    }
    
    .hero-section {
        padding: 6rem 0;
    }
    
    .service-card,
    .review-card {
        padding: 2.5rem 2rem;
    }
}
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <?php if($featuredProduct): ?>
                        <div class="hero-badge">Sản phẩm mới</div>
                        <h1 class="hero-title">Crafted To IMPRESS</h1>
                        <h2 class="hero-subtitle">AURORA</h2>
                        <div class="hero-price"><?php echo number_format($productModel->getCurrentPrice($featuredProduct)); ?>₫</div>
                        <div class="hero-original-price"><?php echo number_format($productModel->getOriginalPrice($featuredProduct)); ?>₫</div>
                        <div class="hero-expiry">23% OFF – Valid this month</div>
                        <a href="index.php?page=product&id=<?php echo $featuredProduct['id']; ?>" class="btn btn-primary btn-lg">
                            Mua ngay
                        </a>
                    <?php else: ?>
                        <div class="hero-badge">Sản phẩm mới</div>
                        <h1 class="hero-title">Crafted To IMPRESS</h1>
                        <h2 class="hero-subtitle">AURORA</h2>
                        <div class="hero-price">32,990,000₫</div>
                        <div class="hero-original-price">42,897,000₫</div>
                        <div class="hero-expiry">23% OFF – Valid this month</div>
                        <a href="index.php?page=products" class="btn btn-primary btn-lg">
                            Mua ngay
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image-container">
                    <?php if($featuredProduct): ?>
                        <?php if (!empty($featuredProduct['gallery'])): ?>
                            <img src="<?php echo htmlspecialchars($featuredProduct['gallery'][0]['image_path']); ?>" alt="<?php echo htmlspecialchars($featuredProduct['name']); ?>" class="hero-image">
                        <?php else: ?>
                            <img src="<?php echo $featuredProduct['image_'] ?: 'assets/uploads/hero-sofa.jpg'; ?>" alt="<?php echo htmlspecialchars($featuredProduct['name']); ?>" class="hero-image">
                        <?php endif; ?>
                    <?php else: ?>
                        <img src="assets/uploads/hero-sofa.jpg" alt="Aurora Sofa" class="hero-image">
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- New Products Section -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-md-6">
                <h3 class="section-title">Sản phẩm mới</h3>
            </div>
            <div class="col-md-6 text-end">
                <a href="index.php?page=products&new=1" class="view-more-link">Xem thêm <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
        
        <div class="row">
            <?php foreach($newProducts as $product): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="product-card" data-product-id="<?php echo $product['id']; ?>">
                    <a href="index.php?page=product&id=<?php echo $product['id']; ?>" class="product-link">
                        <div class="product-image">
                            <?php if (!empty($product['gallery'])): ?>
                                <img src="<?php echo htmlspecialchars($product['gallery'][0]['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <img src="assets/uploads/product-default.jpg" alt="No image">
                            <?php endif; ?>
                            
                            <?php 
                            $discountPercent = $productModel->getDiscountPercent($product);
                            if($discountPercent > 0): 
                            ?>
                            <div class="discount-badge">-<?php echo $discountPercent; ?>%</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-info">
                            <h5 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <div class="product-price">
                                <span class="current-price"><?php echo number_format($productModel->getCurrentPrice($product)); ?>₫</span>
                                <?php if($productModel->getDiscountPercent($product) > 0): ?>
                                <span class="original-price"><?php echo number_format($productModel->getOriginalPrice($product)); ?>₫</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-sales">Đã bán <?php echo $product['sold_count'] ?? 0; ?></div>
                        </div>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Best Sellers Section -->
<section class="py-5" style="background: #f8f9fa;">
    <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-md-6">
                <h3 class="section-title">Bán chạy nhất</h3>
            </div>
            <div class="col-md-6 text-end">
                <a href="index.php?page=products&bestseller=1" class="view-more-link">Xem thêm <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
        </div>
        
        <div class="row">
            <?php foreach($bestSellers as $product): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="product-card" data-product-id="<?php echo $product['id']; ?>">
                    <a href="index.php?page=product&id=<?php echo $product['id']; ?>" class="product-link">
                        <div class="product-image">
                            <?php if (!empty($product['gallery'])): ?>
                                <img src="<?php echo htmlspecialchars($product['gallery'][0]['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <img src="assets/uploads/product-default.jpg" alt="No image">
                            <?php endif; ?>
                            
                            <?php 
                            $discountPercent = $productModel->getDiscountPercent($product);
                            if($discountPercent > 0): 
                            ?>
                            <div class="discount-badge">-<?php echo $discountPercent; ?>%</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-info">
                            <h5 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <div class="product-price">
                                <span class="current-price"><?php echo number_format($productModel->getCurrentPrice($product)); ?>₫</span>
                                <?php if($productModel->getDiscountPercent($product) > 0): ?>
                                <span class="original-price"><?php echo number_format($productModel->getOriginalPrice($product)); ?>₫</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-sales">Đã bán <?php echo $product['sold_count'] ?? 0; ?></div>
                        </div>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h3 class="section-title">Dịch vụ của chúng tôi</h3>
            <p class="section-subtitle">Cam kết mang đến trải nghiệm mua sắm tốt nhất</p>
        </div>
        
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h5 class="service-title">Miễn phí vận chuyển</h5>
                    <p class="service-subtitle">Cho đơn hàng từ 500k</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h5 class="service-title">Bảo hành chính hãng</h5>
                    <p class="service-subtitle">12 tháng bảo hành</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-arrow-rotate-left"></i>
                    </div>
                    <h5 class="service-title">Đổi trả miễn phí</h5>
                    <p class="service-subtitle">Trong 30 ngày</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="service-card">
                    <div class="service-icon">
                        <i class="fas fa-phone-volume"></i>
                    </div>
                    <h5 class="service-title">Hỗ trợ 24/7</h5>
                    <p class="service-subtitle">Hotline: 090-123-4567</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Customer Reviews Section -->
<section class="py-5" style="background: #f8f9fa;">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h3 class="section-title">Khách hàng nói gì</h3>
            <p class="section-subtitle">Những đánh giá chân thực từ khách hàng đã sử dụng sản phẩm</p>
        </div>
        
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="review-card">
                    <img src="assets/uploads/review-1.jpg" alt="Review 1" class="review-image">
                    <h5 class="reviewer-name">Nguyễn Văn A</h5>
                    <p class="review-text">"Sản phẩm gỗ chất lượng rất tốt, giao hàng nhanh chóng và nhân viên phục vụ rất nhiệt tình. Tôi rất hài lòng!"</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="review-card">
                    <img src="assets/uploads/review-2.jpg" alt="Review 2" class="review-image">
                    <h5 class="reviewer-name">Trần Thị B</h5>
                    <p class="review-text">"Gỗ đẹp, giá cả hợp lý. Đã mua nhiều lần và luôn hài lòng với chất lượng sản phẩm."</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="review-card">
                    <img src="assets/uploads/review-3.jpg" alt="Review 3" class="review-image">
                    <h5 class="reviewer-name">Lê Văn C</h5>
                    <p class="review-text">"Chất lượng gỗ vượt trội so với giá tiền. Sẽ tiếp tục ủng hộ shop trong tương lai."</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!isset($_SESSION['user_id'])): ?>
<div class="auth-form-container my-5">
  <ul class="nav nav-tabs mb-3 justify-content-center" id="authTabHome" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="login-tab-home" data-bs-toggle="tab" data-bs-target="#login-form-home" type="button" role="tab">Đăng nhập</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="register-tab-home" data-bs-toggle="tab" data-bs-target="#register-form-home" type="button" role="tab">Đăng ký</button>
    </li>
  </ul>
  <div class="tab-content" id="authTabContentHome">
    <!-- Đăng nhập -->
    <div class="tab-pane fade show active" id="login-form-home" role="tabpanel">
      <form method="post" action="index.php?page=login" class="auth-form">
        <h3 class="auth-title">Chào mừng trở lại!</h3>
        <div class="form-group">
          <input type="text" class="form-control" name="email_or_phone" placeholder="Email hoặc Số điện thoại" required>
        </div>
        <div class="form-group">
          <input type="password" class="form-control" name="password" placeholder="Mật khẩu" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>
        <div class="switch-link">
          Chưa có tài khoản? <a href="#" onclick="switchToRegisterHome(event)">Đăng ký</a>
        </div>
      </form>
    </div>
    <!-- Đăng ký -->
    <div class="tab-pane fade" id="register-form-home" role="tabpanel">
      <form method="post" action="index.php?page=register" class="auth-form">
        <h3 class="auth-title">Tạo tài khoản mới</h3>
        <div class="form-group">
          <input type="email" class="form-control" name="email" placeholder="Email" required>
        </div>
        <div class="form-group">
          <input type="text" class="form-control" name="phone" placeholder="Số điện thoại" required>
        </div>
        <div class="form-group">
          <input type="text" class="form-control" name="name" placeholder="Họ tên" required>
        </div>
        <div class="form-group">
          <input type="text" class="form-control" name="address" placeholder="Địa chỉ">
        </div>
        <div class="form-group">
          <input type="password" class="form-control" name="password" placeholder="Mật khẩu" required>
        </div>
        <button type="submit" class="btn btn-success btn-block">Đăng ký</button>
        <div class="switch-link">
          Đã có tài khoản? <a href="#" onclick="switchToLoginHome(event)">Đăng nhập</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
function switchToRegisterHome(e) {
  e.preventDefault();
  var tab = document.querySelector('#register-tab-home');
  if(tab) tab.click();
}
function switchToLoginHome(e) {
  e.preventDefault();
  var tab = document.querySelector('#login-tab-home');
  if(tab) tab.click();
}
</script>

<!-- Bootstrap JS + Popper (nên dùng CDN) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

 