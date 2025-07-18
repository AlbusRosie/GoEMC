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

// Lấy sản phẩm bán chạy (nhiều hơn trang home)
$bestSellers = $productModel->getBestSellers(16);

// Lấy sản phẩm gợi ý (nhiều hơn trang home)
$suggestedProducts = $productModel->getSuggestedProducts(16);

// Lấy sản phẩm hot deal (nhiều hơn trang home)
$hotDeals = $productModel->getHotDeals(16);

// Lấy sản phẩm mới (nhiều hơn trang home)
$newProducts = $productModel->getNewProducts(16);

// Lấy sản phẩm theo danh mục
$categories = $categoryModel->getAll();
$productsByCategory = [];
foreach ($categories as $category) {
    $productsByCategory[$category['id']] = $productModel->getByCategory($category['id'], 8);
}

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

// Lấy gallery cho sản phẩm theo danh mục
foreach ($productsByCategory as $categoryId => &$products) {
    foreach ($products as &$product) {
        $product['gallery'] = $productModel->getProductImages($product['id'], 'main');
    }
    unset($product);
}
unset($products);
?>

<style>
/* Hero Section - Giống home */
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

/* Section Styles - Giống home */
.section-title {
    font-family: 'Playfair Display', serif;
    font-size: 3rem;
    font-weight: 300;
    color: #1a1a1a;
    margin-bottom: 1.5rem;
    letter-spacing: -1px;
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

/* Product Card - Giống home */
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

.hot-deal-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: #e74c3c;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0;
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.new-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: #27ae60;
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

/* Category Section - Giống home */
.category-section {
    background: white;
    padding: 80px 0;
}

.category-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.category-card {
    background: #f8f9fa;
    border-radius: 0;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
    box-shadow: 0 5px 25px rgba(0,0,0,0.08);
}

.category-card:hover {
    background: white;
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    transform: translateY(-3px);
}

.category-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 1rem;
}

.category-product-count {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 1.5rem;
}

.category-products {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.category-product {
    background: white;
    border-radius: 0;
    padding: 1rem;
    text-align: center;
    transition: all 0.3s ease;
    border: 1px solid #f0f0f0;
}

.category-product:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.category-product img {
    width: 100%;
    height: 80px;
    object-fit: cover;
    border-radius: 0;
    margin-bottom: 0.5rem;
}

.category-product-name {
    font-size: 0.8rem;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 0.3rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.category-product-price {
    font-size: 0.9rem;
    color: #ff6b35;
    font-weight: 600;
}

.category-product-more {
    background: white;
    border-radius: 0;
    padding: 1rem;
    text-align: center;
    transition: all 0.3s ease;
    border: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 80px;
}

.category-product-more:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    background: #f8f9fa;
}

.more-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.more-icon {
    font-size: 1.5rem;
    color: #ccc;
    font-weight: 300;
    line-height: 1;
}

.more-text {
    font-size: 0.7rem;
    color: #999;
    font-weight: 400;
    text-align: center;
    line-height: 1.2;
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
    
    .category-grid {
        grid-template-columns: 1fr;
    }
    
    .category-products {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<!-- Hero Section - Giống home -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <?php if($featuredProduct): ?>
                        <div class="hero-badge">Sản phẩm nổi bật</div>
                        <h1 class="hero-title">Crafted To IMPRESS</h1>
                        <h2 class="hero-subtitle"><?php echo htmlspecialchars($featuredProduct['name']); ?></h2>
                        <div class="hero-price"><?php echo number_format($productModel->getCurrentPrice($featuredProduct)); ?>₫</div>
                        <div class="hero-original-price"><?php echo number_format($productModel->getOriginalPrice($featuredProduct)); ?>₫</div>
                        <div class="hero-expiry"><?php echo $productModel->getDiscountPercent($featuredProduct); ?>% OFF – Valid this month</div>
                        <a href="index.php?page=product&id=<?php echo $featuredProduct['id']; ?>" class="btn btn-primary btn-lg">
                            Mua ngay
                        </a>
                    <?php else: ?>
                        <div class="hero-badge">EMCwood Store</div>
                        <h1 class="hero-title">Crafted To IMPRESS</h1>
                        <h2 class="hero-subtitle">Khám phá bộ sưu tập gỗ</h2>
                        <div class="hero-price">Chất lượng cao</div>
                        <div class="hero-original-price">Giá tốt nhất</div>
                        <div class="hero-expiry">Nhiều sản phẩm đa dạng</div>
                        <a href="index.php?page=products" class="btn btn-primary btn-lg">
                            Khám phá ngay
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
                        <img src="assets/uploads/product_1752552835_des_0.jpg" alt="EMCwood Store" class="hero-image">
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Hot Deals Section -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-md-6">
                <h3 class="section-title">Hot Deals</h3>
            </div>
            <div class="col-md-6 text-end">
                <a href="index.php?page=products&sale=1" class="view-more-link">Xem thêm</a>
            </div>
        </div>
        
        <div class="row">
            <?php foreach($hotDeals as $product): ?>
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
                            <div class="hot-deal-badge">Hot Deal</div>
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

<!-- New Products Section -->
<section class="py-5" style="background: #f8f9fa;">
    <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-md-6">
                <h3 class="section-title">Sản phẩm mới</h3>
            </div>
            <div class="col-md-6 text-end">
                <a href="index.php?page=products&new=1" class="view-more-link">Xem thêm</a>
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
                            <div class="new-badge">Mới</div>
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
<section class="py-5">
    <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-md-6">
                <h3 class="section-title">Bán chạy nhất</h3>
            </div>
            <div class="col-md-6 text-end">
                <a href="index.php?page=products&bestseller=1" class="view-more-link">Xem thêm</a>
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

<!-- Suggested Products Section -->
<section class="py-5" style="background: #f8f9fa;">
    <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-md-6">
                <h3 class="section-title">Gợi ý cho bạn</h3>
            </div>
            <div class="col-md-6 text-end">
                <a href="index.php?page=products&suggested=1" class="view-more-link">Xem thêm</a>
            </div>
        </div>
        
        <div class="row">
            <?php foreach($suggestedProducts as $product): ?>
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

<!-- Products by Category Section -->
<section class="category-section">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h3 class="section-title">Sản phẩm theo danh mục</h3>
            <p class="section-subtitle">Khám phá sản phẩm theo từng danh mục cụ thể</p>
        </div>
        
        <div class="category-grid">
            <?php foreach ($categories as $category): ?>
            <div class="category-card">
                <h3 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h3>
                <p class="category-product-count"><?php echo count($productsByCategory[$category['id']] ?? []); ?> sản phẩm</p>
                
                <div class="category-products">
                    <?php 
                    $categoryProducts = $productsByCategory[$category['id']] ?? [];
                    if (!empty($categoryProducts)): 
                        $product = $categoryProducts[0];
                    ?>
                    <div class="category-product">
                        <a href="index.php?page=product&id=<?php echo $product['id']; ?>" class="product-link">
                            <?php if (!empty($product['gallery'])): ?>
                                <img src="<?php echo $product['gallery'][0]['image_path'] ?? 'assets/images/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <img src="assets/images/placeholder.jpg" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php endif; ?>
                            <div class="category-product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="category-product-price"><?php echo number_format($product['price'] - $product['sale']); ?>₫</div>
                        </a>
                    </div>
                    <div class="category-product-more">
                        <div class="more-placeholder">
                            <span class="more-icon">+</span>
                            <span class="more-text"><?php echo count($categoryProducts) - 1; ?> sản phẩm khác</span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <a href="index.php?page=products&category=<?php echo $category['id']; ?>" class="view-more-link mt-3">
                    Xem tất cả sản phẩm <?php echo htmlspecialchars($category['name']); ?>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section> 