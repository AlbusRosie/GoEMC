<?php
// Xử lý bộ lọc
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : '';
$sale_filter = isset($_GET['sale']) ? (int)$_GET['sale'] : '';
$featured_filter = isset($_GET['featured']) ? (int)$_GET['featured'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$price_min = isset($_GET['price_min']) ? (float)$_GET['price_min'] : '';
$price_max = isset($_GET['price_max']) ? (float)$_GET['price_max'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Xây dựng query
$where_conditions = ["p.status = 'active'"];
$params = [];

if ($category_filter) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_filter;
}

if ($sale_filter) {
    $where_conditions[] = "p.sale IS NOT NULL AND p.sale > 0";
}

if ($featured_filter) {
    $where_conditions[] = "p.featured = 1";
}

if ($search) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($price_min) {
    $where_conditions[] = "p.price >= ?";
    $params[] = $price_min;
}

if ($price_max) {
    $where_conditions[] = "p.price <= ?";
    $params[] = $price_max;
}

$where_clause = implode(" AND ", $where_conditions);

// Sắp xếp
$order_clause = match($sort) {
    'price_low' => 'ORDER BY p.price ASC',
    'price_high' => 'ORDER BY p.price DESC',
    'name' => 'ORDER BY p.name ASC',
    'sale' => 'ORDER BY p.sale DESC',
    default => 'ORDER BY p.created_at DESC'
};

// Phân trang
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Đếm tổng số sản phẩm
$count_sql = "SELECT COUNT(*) FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              WHERE $where_clause";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_products = $stmt->fetchColumn();
$total_pages = ceil($total_products / $limit);

// Lấy sản phẩm
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE $where_clause 
        $order_clause 
        LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Lấy danh mục cho filter
$stmt = $pdo->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();

// Lấy ảnh sản phẩm
function getProductImages($pdo, $product_id) {
    $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ? AND image_type = 'main' ORDER BY sort_order LIMIT 1");
    $stmt->execute([$product_id]);
    $result = $stmt->fetch();
    return $result ? $result['image_path'] : 'assets/images/product-default.jpg';
}

// Format giá
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . '₫';
}

// Tính phần trăm giảm giá
function getDiscountPercent($original_price, $current_price) {
    if ($original_price > $current_price) {
        return round((($original_price - $current_price) / $original_price) * 100);
    }
    return 0;
}
?>

<style>
/* Hero Section */
.products-hero {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 50%, #ffffff 100%);
    padding: 10px 0;
    position: relative;
    overflow: hidden;
    font-family: 'Playfair Display', serif;
}

.products-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="0.3" fill="%23000" opacity="0.01"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    pointer-events: none;
}

.products-hero-content {
    position: relative;
    z-index: 2;
    padding: 4rem 0;
}

.products-badge {
    display: inline-block;
    background: #000000;
    color: white;
    padding: 0.6rem 1.2rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
    margin-bottom: 2rem;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    border-left: 3px solid #ff6b35;
}

.products-title {
    font-family: 'Playfair Display', serif;
    font-size: 3.5rem;
    font-weight: 300;
    color: #000000;
    margin-bottom: 1rem;
    line-height: 1.1;
    letter-spacing: -1px;
}

.products-subtitle {
    font-family: 'Playfair Display', serif;
    font-size: 1.3rem;
    font-weight: 400;
    color: #333333;
    margin-bottom: 1rem;
    letter-spacing: 0.5px;
}

.products-description {
    font-size: 1rem;
    color: #666666;
    margin-bottom: 2rem;
    font-weight: 300;
    line-height: 1.6;
    letter-spacing: 0.3px;
}

.products-features {
    margin-top: 2.5rem;
}

.products-feature-item {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    font-size: 1rem;
    color: #000000;
    font-weight: 400;
    letter-spacing: 0.3px;
}

.products-feature-item i {
    font-size: 1.1rem;
    margin-right: 0.8rem;
    color: #ff6b35;
}

.products-feature-item span {
    letter-spacing: 0.2px;
}

.products-image-container {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.products-image {
    width: 100%;
    height: 500px;
    object-fit: cover;
}

/* Products Section */
.products-section {
    background: #ffffff;
    padding: 80px 0;
    font-family: 'Playfair Display', serif;
}

.products-container {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.05);
    border: 1px solid #e5e5e5;
    overflow: hidden;
    margin-top: -30px;
    position: relative;
    z-index: 10;
}

/* Filter Section */
.filter-section {
    padding: 3rem;
    background: #fafafa;
    border-bottom: 1px solid #e5e5e5;
}

.filter-title {
    font-size: 2rem;
    font-weight: 400;
    color: #000000;
    margin-bottom: 0.8rem;
    letter-spacing: -0.5px;
}

.filter-subtitle {
    font-size: 1rem;
    color: #666666;
    margin-bottom: 2rem;
    font-weight: 300;
    line-height: 1.5;
}

.filter-form label {
    font-weight: 500;
    color: #000000;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.filter-form .form-control {
    border-radius: 8px;
    border: 1px solid #d1d1d1;
    font-size: 0.9rem;
    padding: 0.8rem 1rem;
    margin-bottom: 1.2rem;
    transition: all 0.3s ease;
    background: #ffffff;
    font-family: inherit;
}

.filter-form .form-control:focus {
    border-color: #ff6b35;
    box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
    outline: none;
}

.filter-form .form-select {
    border-radius: 8px;
    border: 1px solid #d1d1d1;
    font-size: 0.9rem;
    padding: 0.8rem 1rem;
    margin-bottom: 1.2rem;
    transition: all 0.3s ease;
    background: #ffffff;
    font-family: inherit;
}

.filter-form .form-select:focus {
    border-color: #ff6b35;
    box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
    outline: none;
}

.filter-form .form-check-input {
    width: 1rem;
    height: 1rem;
    margin-right: 0.6rem;
    border: 1px solid #d1d1d1;
    border-radius: 3px;
}

.filter-form .form-check-input:checked {
    background-color: #ff6b35;
    border-color: #ff6b35;
}

.filter-form .form-check-label {
    font-size: 0.9rem;
    color: #000000;
    font-weight: 400;
}

.filter-form .btn-primary {
    background: #000000;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 500;
    padding: 0.8rem 2rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
    font-family: inherit;
    width: 100%;
}

.filter-form .btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: #ff6b35;
    transition: left 0.3s ease;
}

.filter-form .btn-primary:hover::before {
    left: 0;
}

.filter-form .btn-primary span {
    position: relative;
    z-index: 2;
}

.filter-form .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.filter-form .btn-outline-secondary {
    border-radius: 8px;
    border: 1px solid #d1d1d1;
    font-size: 0.9rem;
    font-weight: 500;
    padding: 0.8rem 1.5rem;
    transition: all 0.3s ease;
    background: #ffffff;
    color: #666666;
    font-family: inherit;
    width: 100%;
    margin-top: 0.8rem;
}

.filter-form .btn-outline-secondary:hover {
    border-color: #ff6b35;
    color: #ff6b35;
    background: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(255, 107, 53, 0.1);
}

/* Products Grid Section */
.products-grid-section {
    padding: 3rem;
    background: #ffffff;
    color: #000000;
    position: relative;
}

.products-grid-content {
    position: relative;
    z-index: 2;
}

.products-grid-title {
    font-size: 2rem;
    font-weight: 400;
    margin-bottom: 0.8rem;
    letter-spacing: -0.5px;
    color: #000000;
}

.products-grid-subtitle {
    font-size: 1rem;
    color: #666666;
    margin-bottom: 2rem;
    font-weight: 300;
    line-height: 1.5;
}

.products-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 12px;
    border: 1px solid #e5e5e5;
}

.products-count {
    font-size: 1rem;
    color: #666666;
    font-weight: 400;
}

.view-buttons {
    display: flex;
    gap: 0.8rem;
}

.view-btn {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: #ffffff;
    border: 1px solid #d1d1d1;
    color: #666666;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    transition: all 0.3s ease;
    text-decoration: none;
}

.view-btn:hover,
.view-btn.active {
    background: #ff6b35;
    border-color: #ff6b35;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(255, 107, 53, 0.2);
}

/* Product Cards */
.product-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e5e5e5;
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
    position: relative;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border-color: #ff6b35;
}

.product-image-container {
    position: relative;
    overflow: hidden;
    height: 240px;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image {
    transform: scale(1.03);
}

.product-badge {
    position: absolute;
    top: 0.8rem;
    left: 0.8rem;
    background: #000000;
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 600;
    z-index: 2;
}

.discount-badge {
    position: absolute;
    top: 0.8rem;
    right: 0.8rem;
    background: #ff6b35;
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    z-index: 2;
}

.featured-badge {
    position: absolute;
    top: 2.5rem;
    right: 0.8rem;
    background: #ffc107;
    color: #000000;
    padding: 0.4rem 0.8rem;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 600;
    z-index: 2;
}

.product-content {
    padding: 1.5rem;
}

.product-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #000000;
    margin-bottom: 0.8rem;
    line-height: 1.3;
    font-family: 'Playfair Display', serif;
}

.product-description {
    font-size: 0.85rem;
    color: #666666;
    margin-bottom: 1rem;
    line-height: 1.5;
}

.product-details {
    margin-bottom: 1rem;
}

.product-detail-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
    font-size: 0.8rem;
    color: #666666;
}

.product-detail-item i {
    width: 16px;
    margin-right: 0.6rem;
    color: #ff6b35;
}

.product-price-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.product-price {
    display: flex;
    flex-direction: column;
}

.current-price {
    font-size: 1.2rem;
    font-weight: 700;
    color: #ff6b35;
    margin-bottom: 0.2rem;
}

.original-price {
    font-size: 0.9rem;
    color: #999999;
    text-decoration: line-through;
}

.product-action {
    display: flex;
    gap: 0.8rem;
}

.btn-detail {
    background: #000000;
    border: none;
    border-radius: 8px;
    color: white;
    padding: 0.6rem 1.2rem;
    font-size: 0.8rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.btn-detail:hover {
    background: #ff6b35;
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(255, 107, 53, 0.2);
    color: white;
    text-decoration: none;
}

/* Pagination */
.pagination-section {
    padding: 2rem 3rem;
    background: #f8f9fa;
    border-top: 1px solid #e5e5e5;
}

.pagination {
    justify-content: center;
    gap: 0.4rem;
}

.page-link {
    border-radius: 8px;
    border: 1px solid #d1d1d1;
    background: #ffffff;
    color: #666666;
    padding: 0.6rem 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.page-link:hover {
    background: #ff6b35;
    border-color: #ff6b35;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(255, 107, 53, 0.2);
}

.page-item.active .page-link {
    background: #ff6b35;
    border-color: #ff6b35;
    color: white;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #666666;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1.5rem;
    color: #cccccc;
}

.empty-state h4 {
    font-size: 1.3rem;
    margin-bottom: 0.8rem;
    color: #000000;
}

.empty-state p {
    font-size: 1rem;
    color: #666666;
}

/* List View */
.list-view .product-item {
    flex: 0 0 100%;
    max-width: 100%;
}

.list-view .product-card {
    flex-direction: row;
    align-items: stretch;
}

.list-view .product-image-container {
    width: 250px;
    height: auto;
    flex-shrink: 0;
}

.list-view .product-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.list-view .product-price-section {
    margin-bottom: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
    .products-title {
        font-size: 2.5rem;
    }
    
    .products-subtitle {
        font-size: 1.1rem;
    }
    
    .products-image {
        height: 350px;
    }
    
    .products-hero {
        padding: 0;
    }
    
    .products-description {
        padding-left: 12px;
        padding-right: 12px;
        text-align: left;
    }
    
    .filter-section,
    .products-grid-section,
    .pagination-section {
        padding: 1.5rem 1rem;
    }
    
    .products-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .list-view .product-card {
        flex-direction: column;
    }
    
    .list-view .product-image-container {
        width: 100%;
        height: 240px;
    }
    
    .filter-form .btn-primary {
        display: block;
        margin-left: auto;
        margin-right: auto;
        width: 100%;
    }
}
</style>

<!-- Hero Section -->
<section class="products-hero">
    <div class="container">
        <div class="products-hero-content">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="products-content">
                        <div class="products-badge">Khám phá ngay</div>
                        <h1 class="products-title">Bộ sưu tập gỗ cao cấp</h1>
                        <h2 class="products-subtitle">EMCwood</h2>
                        <p class="products-description">
                            Khám phá bộ sưu tập nội thất gỗ cao cấp với thiết kế hiện đại, 
                            chất lượng đẳng cấp quốc tế. Từ gỗ tự nhiên đến gỗ công nghiệp, 
                            chúng tôi mang đến những sản phẩm hoàn hảo cho không gian sống của bạn.
                        </p>
                        <div class="products-features">
                            <div class="products-feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Chất lượng cao cấp</span>
                            </div>
                            <div class="products-feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Thiết kế hiện đại</span>
                            </div>
                            <div class="products-feature-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Bảo hành chính hãng</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="products-image-container">
                        <img src="assets/uploads/product_1752552835_des_0.jpg" alt="EMCwood - Sản phẩm" class="products-image">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Products Section -->
<section class="products-section">
    <div class="container">
        <div class="products-container">
            <div class="row g-0">
                <!-- Filter Section -->
                <div class="col-lg-3">
                    <div class="filter-section">
                        <h2 class="filter-title">Bộ lọc</h2>
                        <p class="filter-subtitle">Tìm kiếm sản phẩm phù hợp với nhu cầu của bạn</p>
                        
                        <form method="GET" action="" class="filter-form">
                            <input type="hidden" name="page" value="products">
                            
                            <!-- Tìm kiếm -->
                            <div class="mb-3">
                                <label for="search">Tìm kiếm</label>
                                <input type="text" id="search" name="search" class="form-control" 
                                       placeholder="Tên sản phẩm..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        
                        <!-- Danh mục -->
                        <div class="mb-3">
                                <label for="category">Danh mục</label>
                                <select id="category" name="category" class="form-select">
                                <option value="">Tất cả danh mục</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" 
                                            <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                            
                            <!-- Khuyến mãi -->
                            <div class="mb-3">
                                <label>Khuyến mãi</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="sale" value="1" 
                                           id="saleCheck" <?php echo $sale_filter ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="saleCheck">
                                        Sản phẩm giảm giá
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="featured" value="1" 
                                           id="featuredCheck" <?php echo $featured_filter ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="featuredCheck">
                                        Sản phẩm nổi bật
                                    </label>
                                </div>
                            </div>
                        
                        <!-- Khoảng giá -->
                        <div class="mb-3">
                                <label>Khoảng giá</label>
                            <div class="row">
                                <div class="col-6">
                                    <input type="number" name="price_min" class="form-control" 
                                           placeholder="Từ" value="<?php echo $price_min; ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="price_max" class="form-control" 
                                           placeholder="Đến" value="<?php echo $price_max; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sắp xếp -->
                            <div class="mb-4">
                                <label for="sort">Sắp xếp</label>
                                <select id="sort" name="sort" class="form-select">
                                <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                                <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Giá tăng dần</option>
                                <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Giá giảm dần</option>
                                <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Tên A-Z</option>
                                    <option value="sale" <?php echo $sort == 'sale' ? 'selected' : ''; ?>>Giảm giá nhiều nhất</option>
                            </select>
                        </div>
                        
                            <button type="submit" class="btn btn-primary">
                                <span><i class="fas fa-search me-2"></i>Lọc sản phẩm</span>
                        </button>
                            
                            <?php if ($category_filter || $sale_filter || $featured_filter || $search || $price_min || $price_max): ?>
                            <a href="index.php?page=products" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Xóa bộ lọc
                            </a>
                            <?php endif; ?>
                    </form>
            </div>
        </div>
        
                <!-- Products Grid Section -->
        <div class="col-lg-9">
                    <div class="products-grid-section">
                        <div class="products-grid-content">
                            <h2 class="products-grid-title">Sản Phẩm</h2>
                            <p class="products-grid-subtitle">Khám phá bộ sưu tập nội thất gỗ cao cấp của chúng tôi</p>
                            
            <!-- Header -->
                            <div class="products-header">
                <div>
                                    <h3 class="mb-0">Tất cả sản phẩm</h3>
                                    <p class="products-count mb-0">Tìm thấy <?php echo $total_products; ?> sản phẩm</p>
                </div>
                                <div class="view-buttons">
                                    <button class="view-btn" id="grid-view" title="Xem dạng lưới">
                        <i class="fas fa-th"></i>
                    </button>
                                    <button class="view-btn" id="list-view" title="Xem dạng danh sách">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
            
                            <!-- Products Grid -->
            <div class="row" id="products-container">
                <?php if(empty($products)): ?>
                                <div class="col-12">
                                    <div class="empty-state">
                                        <i class="fas fa-search"></i>
                                        <h4>Không tìm thấy sản phẩm</h4>
                                        <p>Hãy thử thay đổi bộ lọc để tìm sản phẩm khác</p>
                                    </div>
                </div>
                <?php else: ?>
                    <?php foreach($products as $product): ?>
                    <div class="col-md-6 col-lg-4 mb-4 product-item">
                                        <div class="product-card">
                                            <div class="product-image-container">
                                                <img src="<?php echo getProductImages($pdo, $product['id']); ?>" 
                                                     class="product-image" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                
                                                <div class="product-badge"><?php echo htmlspecialchars($product['category_name']); ?></div>
                                                
                                                <?php 
                                                $discount_percent = getDiscountPercent($product['original_price'], $product['price']);
                                                if($discount_percent > 0): 
                                                ?>
                                                <div class="discount-badge">-<?php echo $discount_percent; ?>%</div>
                                                <?php endif; ?>
                                                
                                                <?php if($product['featured']): ?>
                                                <div class="featured-badge">
                                                    <i class="fas fa-star me-1"></i>Nổi bật
                                                </div>
                                <?php endif; ?>
                            </div>
                                            
                                            <div class="product-content">
                                                <div>
                                                    <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                                    <p class="product-description">
                                                        <?php echo htmlspecialchars(substr(strip_tags($product['description']), 0, 100)); ?>...
                                                    </p>
                                                    
                                                    <div class="product-details">
                                                        <?php if($product['size']): ?>
                                                        <div class="product-detail-item">
                                                            <i class="fas fa-ruler-combined"></i>
                                                            <span><?php echo htmlspecialchars($product['size']); ?></span>
                                                        </div>
                                <?php endif; ?>
                                
                                                        <?php if($product['color']): ?>
                                                        <div class="product-detail-item">
                                                            <i class="fas fa-palette"></i>
                                                            <span><?php echo htmlspecialchars($product['color']); ?></span>
                                                        </div>
                                <?php endif; ?>
                                                    </div>
                                                </div>
                                                
                                                <div class="product-price-section">
                                                    <div class="product-price">
                                                        <span class="current-price"><?php echo formatPrice($product['price']); ?></span>
                                        <?php if($product['original_price'] > $product['price']): ?>
                                                        <span class="original-price"><?php echo formatPrice($product['original_price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                                    <div class="product-action">
                                    <a href="index.php?page=product&id=<?php echo $product['id']; ?>" 
                                                           class="btn-detail">
                                                            <i class="fas fa-eye"></i>
                                                            Chi tiết
                                                        </a>
                                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
                            <div class="pagination-section">
                                <nav aria-label="Product pagination">
                                    <ul class="pagination">
                    <?php if($page > 1): ?>
                    <li class="page-item">
                                            <a class="page-link" href="?page=products&p=<?php echo $page-1; ?>&category=<?php echo $category_filter; ?>&sale=<?php echo $sale_filter; ?>&featured=<?php echo $featured_filter; ?>&search=<?php echo urlencode($search); ?>&price_min=<?php echo $price_min; ?>&price_max=<?php echo $price_max; ?>&sort=<?php echo $sort; ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=products&p=<?php echo $i; ?>&category=<?php echo $category_filter; ?>&sale=<?php echo $sale_filter; ?>&featured=<?php echo $featured_filter; ?>&search=<?php echo urlencode($search); ?>&price_min=<?php echo $price_min; ?>&price_max=<?php echo $price_max; ?>&sort=<?php echo $sort; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if($page < $total_pages): ?>
                    <li class="page-item">
                                            <a class="page-link" href="?page=products&p=<?php echo $page+1; ?>&category=<?php echo $category_filter; ?>&sale=<?php echo $sale_filter; ?>&featured=<?php echo $featured_filter; ?>&search=<?php echo urlencode($search); ?>&price_min=<?php echo $price_min; ?>&price_max=<?php echo $price_max; ?>&sort=<?php echo $sort; ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
                            </div>
            <?php endif; ?>
        </div>
    </div>
</div> 
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý view mode (grid/list)
    const gridView = document.getElementById('grid-view');
    const listView = document.getElementById('list-view');
    const productsContainer = document.getElementById('products-container');
    
    if (gridView && listView && productsContainer) {
        // Set default active state
        gridView.classList.add('active');
        
        gridView.addEventListener('click', function() {
            productsContainer.className = 'row';
            gridView.classList.add('active');
            listView.classList.remove('active');
        });
        
        listView.addEventListener('click', function() {
            productsContainer.className = 'row list-view';
            listView.classList.add('active');
            gridView.classList.remove('active');
        });
    }
});
</script> 