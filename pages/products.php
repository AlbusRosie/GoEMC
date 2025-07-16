<?php
// Xử lý bộ lọc
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$price_min = isset($_GET['price_min']) ? $_GET['price_min'] : '';
$price_max = isset($_GET['price_max']) ? $_GET['price_max'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Xây dựng query
$where_conditions = ["p.status = 1"];
$params = [];

if ($category_filter) {
    $where_conditions[] = "c.slug = ?";
    $params[] = $category_filter;
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
    default => 'ORDER BY p.created_at DESC'
};

// Phân trang
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$limit = ITEMS_PER_PAGE;
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
$stmt = $pdo->prepare("SELECT * FROM categories WHERE status = 1 ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar Filter -->
        <div class="col-lg-3">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Bộ lọc</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <input type="hidden" name="page" value="products">
                        
                        <!-- Danh mục -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Danh mục</label>
                            <select name="category" class="form-select">
                                <option value="">Tất cả danh mục</option>
                                <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['slug']; ?>" 
                                        <?php echo $category_filter == $cat['slug'] ? 'selected' : ''; ?>>
                                    <?php echo $cat['name']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Khoảng giá -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Khoảng giá</label>
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
                        <div class="mb-3">
                            <label class="form-label fw-bold">Sắp xếp</label>
                            <select name="sort" class="form-select">
                                <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                                <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Giá tăng dần</option>
                                <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Giá giảm dần</option>
                                <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Tên A-Z</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Lọc
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="col-lg-9">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold">Sản Phẩm</h2>
                    <p class="text-muted mb-0">Tìm thấy <?php echo $total_products; ?> sản phẩm</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary" id="grid-view">
                        <i class="fas fa-th"></i>
                    </button>
                    <button class="btn btn-outline-secondary" id="list-view">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
            
            <!-- Products -->
            <div class="row" id="products-container">
                <?php if(empty($products)): ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Không tìm thấy sản phẩm</h4>
                    <p class="text-muted">Hãy thử thay đổi bộ lọc để tìm sản phẩm khác</p>
                </div>
                <?php else: ?>
                    <?php foreach($products as $product): ?>
                    <div class="col-md-6 col-lg-4 mb-4 product-item">
                        <div class="card h-100 shadow-sm">
                            <div class="position-relative">
                                <img src="<?php echo explode(',', $product['images'])[0] ?: 'assets/images/product-default.jpg'; ?>" 
                                     class="card-img-top" alt="<?php echo $product['name']; ?>">
                                <?php if($product['original_price'] > $product['price']): ?>
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                    -<?php echo round((($product['original_price'] - $product['price']) / $product['original_price']) * 100); ?>%
                                </span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <span class="badge bg-secondary"><?php echo $product['category_name']; ?></span>
                                </div>
                                <h6 class="card-title"><?php echo $product['name']; ?></h6>
                                <p class="card-text text-muted small"><?php echo substr($product['description'], 0, 100); ?>...</p>
                                
                                <?php if($product['dimensions']): ?>
                                <p class="small text-muted mb-2">
                                    <i class="fas fa-ruler-combined me-1"></i><?php echo $product['dimensions']; ?>
                                </p>
                                <?php endif; ?>
                                
                                <?php if($product['material']): ?>
                                <p class="small text-muted mb-2">
                                    <i class="fas fa-tree me-1"></i><?php echo $product['material']; ?>
                                </p>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold text-primary"><?php echo formatPrice($product['price']); ?></span>
                                        <?php if($product['original_price'] > $product['price']): ?>
                                        <small class="text-muted text-decoration-line-through ms-2">
                                            <?php echo formatPrice($product['original_price']); ?>
                                        </small>
                                        <?php endif; ?>
                                    </div>
                                    <a href="index.php?page=product&id=<?php echo $product['id']; ?>" 
                                       class="btn btn-sm btn-primary">Chi tiết</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <nav aria-label="Product pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=products&p=<?php echo $page-1; ?>&category=<?php echo $category_filter; ?>&price_min=<?php echo $price_min; ?>&price_max=<?php echo $price_max; ?>&sort=<?php echo $sort; ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=products&p=<?php echo $i; ?>&category=<?php echo $category_filter; ?>&price_min=<?php echo $price_min; ?>&price_max=<?php echo $price_max; ?>&sort=<?php echo $sort; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=products&p=<?php echo $page+1; ?>&category=<?php echo $category_filter; ?>&price_min=<?php echo $price_min; ?>&price_max=<?php echo $price_max; ?>&sort=<?php echo $sort; ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div> 