<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/ProductOption.php';

// Khởi tạo kết nối database
$db = new Database();
$conn = $db->getConnection();

// Khởi tạo model
$productModel = new Product($conn);
$categoryModel = new Category($conn);
$productOptionModel = new ProductOption($conn);

// Lấy ID sản phẩm từ URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: index.php');
    exit;
}

// Lấy thông tin sản phẩm
$product = $productModel->getById($product_id);

if (!$product) {
    header('Location: index.php');
    exit;
}

// Lấy gallery ảnh sản phẩm
$product['gallery'] = $productModel->getProductImages($product_id, 'main');
$product['description_gallery'] = $productModel->getProductImages($product_id, 'description');

// Lấy chi tiết sản phẩm từ detail_products
$productDetails = $productModel->getDetails($product_id);

// Lấy product options
$productOptions = $productOptionModel->getByProductId($product_id);

// Lấy sản phẩm liên quan (loại trừ sản phẩm hiện tại)
$relatedProducts = $productModel->getByCategory($product['category_id'], 4, $product_id);

// Lấy thông tin bổ sung cho sản phẩm liên quan
foreach ($relatedProducts as &$relatedProduct) {
    // Lấy gallery ảnh
    $relatedProduct['gallery'] = $productModel->getProductImages($relatedProduct['id'], 'main');
    
    // Lấy rating và review count từ database (nếu có)
    $relatedProduct['rating'] = $relatedProduct['rating'] ?? null;
    $relatedProduct['review_count'] = $relatedProduct['review_count'] ?? 0;
    $relatedProduct['sold_count'] = $relatedProduct['sold_count'] ?? 0;
}
unset($relatedProduct);
?>

<style>
/* Option Title Styles */
.option-title {
    font-size: 0.9rem;
    color: #333;
    font-weight: 500;
    margin-bottom: 0.3rem;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    line-height: 1.2;
}

/* Option Values Container */
.option-values {
    display: flex;
    flex-wrap: wrap;
    gap: 3px;
    margin-bottom: 0.4rem;
}

/* Option Value Button Styles */
.option-value-btn {
    display: inline-block;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #fff;
    color: #333;
    font-size: 0.85rem;
    font-weight: 500;
    min-width: 70px;
    text-align: center;
    position: relative;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    line-height: 1.1;
}

.option-value-btn:hover {
    background-color: #f8f9fa !important;
    border-color: #333 !important;
    color: #333 !important;
}

.option-value-btn.selected {
    background-color: #000 !important;
    border-color: #000 !important;
    color: #fff !important;
}

.option-value-btn.selected .badge {
    background-color: #fff !important;
    color: #000 !important;
}

/* Color Option Styles */
.color-option {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-block;
    margin-right: 3px;
    border: 1px solid #ddd;
    position: relative;
}

.color-option:hover {
    transform: scale(1.1);
    border-color: #333;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.color-option.selected {
    border: 2px solid #333;
    box-shadow: 0 0 0 2px rgba(0,0,0,0.1);
}

/* Product Detail Styles */
.product-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: #222;
    line-height: 1.2;
    margin-bottom: 0.4rem;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

/* Product Options Styles */
.product-options-section {
}

.option-group {
    border-bottom: 1px solid #eee;
    padding-bottom: 0.3rem;
    margin-bottom: 0.3rem;
}

.option-group:last-child {
    border-bottom: none;
    padding-bottom: 0;
    margin-bottom: 0;
}

/* Section dividers */
.product-info-section {
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 0.4rem;
    margin-bottom: 0.4rem;
}

.product-info-section:last-child {
    border-bottom: none;
    padding-bottom: 0;
    margin-bottom: 0;
}

/* Product Details Styles */
.product-details {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.detail-item {
    margin-bottom: 0.3rem;
}

.detail-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.1rem;
    letter-spacing: 0.01em;
    line-height: 1.1;
}

.detail-value {
    font-size: 0.75rem;
    font-weight: 400;
    color: #555;
    line-height: 1.2;
    letter-spacing: 0.01em;
}

.option-values {
    margin-top: 0.5rem;
}

.form-check {
    margin-bottom: 0.5rem;
}

.form-check-label {
    cursor: pointer;
    padding-left: 0.5rem;
}

.form-check-input:checked + .form-check-label {
    font-weight: 600;
    color: #007bff;
}

.current-price {
    font-size: 1.2rem;
    font-weight: 700;
    color: #e74c3c;
    line-height: 1.2;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.original-price {
    font-size: 0.9rem;
    color: #999;
    text-decoration: line-through;
    font-weight: 400;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.discount-badge {
    display: inline-block;
    background: #e74c3c;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.9rem;
    font-weight: 600;
}

/* Product Images Layout - MOHO Style */
.product-images-container {
    position: relative;
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Sticky Product Images */
.product-images-sticky {
    position: sticky;
    top: 20px;
    z-index: 100;
}

.product-images-sticky.sticky-end {
    position: relative;
    top: auto;
}

.thumbnail-column {
    width: 120px;
    flex-shrink: 0;
}

.thumbnail-scroll-container {
    height: 700px;
    max-height: 700px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #ddd transparent;
}

@media (max-width: 768px) {
    .thumbnail-scroll-container {
        height: 350px;
        max-height: 350px;
    }
}

@media (max-width: 576px) {
    .thumbnail-scroll-container {
        height: 300px;
        max-height: 300px;
    }
}

.thumbnail-scroll-container::-webkit-scrollbar {
    width: 4px;
}

.thumbnail-scroll-container::-webkit-scrollbar-track {
    background: transparent;
}

.thumbnail-scroll-container::-webkit-scrollbar-thumb {
    background: #ddd;
    border-radius: 2px;
}

.thumbnail-scroll-container::-webkit-scrollbar-thumb:hover {
    background: #bbb;
}

.thumbnail-item {
    width: 100%;
}

.thumbnail-image {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid #ddd;
    cursor: pointer;
    transition: all 0.3s ease;
    display: block;
}

.thumbnail-image:hover {
    border-color:rgb(19, 19, 19);
    transform: scale(1.05);
}

.thumbnail-image.active {
    border: 3px solidrgb(19, 19, 19);
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.2);
}

.main-image-container {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.main-product-image {
    width: 130%;
    height: 700px;
    object-fit: cover;
    border-radius: 12px;
}

.product-nav-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255, 255, 255, 0.9);
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 10;
}

.product-nav-arrow:hover {
    background: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.product-nav-arrow.left {
    left: 10px;
}

.product-nav-arrow.right {
    right: 10px;
}

.floating-wishlist-btn {
    position: absolute;
    bottom: 20px;
    left: 20px;
    width: 50px;
    height: 50px;
    background: white;
    color: #e74c3c;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
    z-index: 10;
    border: 2px solid #e74c3c;
}

.floating-wishlist-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(0,0,0,0.2);
    background: #e74c3c;
    color: white;
}

.floating-wishlist {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 60px;
    height: 60px;
    background: #e74c3c;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
    transition: all 0.3s ease;
    z-index: 1000;
}

.floating-wishlist:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(231, 76, 60, 0.6);
}

.product-card {
    border: 1px solid #eee;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    background: white;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.product-image {
    position: relative;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image img {
    transform: scale(1.05);
}

.product-info {
    padding: 15px;
}

.product-name {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
}

.product-price {
    margin-bottom: 5px;
}

.current-price {
    font-weight: 700;
    color: #e74c3c;
}

.original-price {
    color: #999;
    text-decoration: line-through;
    margin-left: 8px;
    font-size: 0.9rem;
}

        .product-sales {
            font-size: 0.8rem;
            color: #666;
        }
        
        /* Responsive images in product description */
        .description-text img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 10px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .description-text table {
            max-width: 100%;
            overflow-x: auto;
            display: block;
        }
        
        .description-text table img {
            max-width: 100%;
            height: auto;
        }
        
        /* Responsive content */
        .description-text {
            overflow-wrap: break-word;
            word-wrap: break-word;
        }
        
        .description-text * {
            max-width: 100%;
        }
        
        /* Responsive Design for Product Images */
        @media (max-width: 768px) {
            .product-images-container {
                flex-direction: column;
                padding: 15px;
            }
            
            .thumbnail-column {
                width: 100%;
                margin-bottom: 15px;
            }
            
            .main-product-image {
                width: 100%;
            }
            
            .thumbnail-scroll-container {
                max-height: 120px;
                overflow-x: auto;
                overflow-y: hidden;
                display: flex;
                gap: 10px;
            }
            
            .thumbnail-item {
                width: auto;
                flex-shrink: 0;
            }
            
            .thumbnail-image {
                width: 80px;
                height: 80px;
            }
            
            .main-product-image {
                height: 350px;
            }
            
            .floating-wishlist-btn {
                bottom: 15px;
                left: 15px;
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
        }
        
        @media (max-width: 576px) {
            .product-images-container {
                padding: 10px;
            }
            
            .thumbnail-image {
                width: 70px;
                height: 70px;
            }
            
            .main-product-image {
                height: 300px;
            }
            
            .floating-wishlist-btn {
                bottom: 10px;
                left: 10px;
                width: 35px;
                height: 35px;
                font-size: 14px;
            }
        }

.quantity-action {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
}
.quantity-label {
    font-weight: 600;
    margin-right: 8px;
    font-size: 0.9rem;
}
.quantity-group {
    display: flex;
    align-items: center;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
}
.quantity-btn {
    width: 38px;
    height: 38px;
    border: none;
    background: #f5f5f5;
    color: #222;
    font-size: 1.3rem;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.2s;
    outline: none;
    display: flex;
    align-items: center;
    justify-content: center;
}
.quantity-btn:hover {
    background: #ececec;
}
.quantity-input {
    width: 48px;
    height: 38px;
    border: none;
    text-align: center;
    font-size: 1.1rem;
    font-weight: 600;
    outline: none;
    background: #fff;
}

.product-action-row {
    display: flex;
    gap: 8px;
    margin-bottom: 8px;
}
.btn-add-cart, .btn-buy-now {
    flex: 1 1 0;
    border-radius: 6px;
    font-size: 1.1rem;
    font-weight: 700;
    padding: 14px 0;
    text-transform: uppercase;
    border: none;
    transition: background 0.2s, color 0.2s;
    letter-spacing: 0.5px;
}
.btn-add-cart {
    background: #222;
    color: #fff;
}
.btn-add-cart:hover {
    background: #444;
}
.btn-buy-now {
    background: #ff6d2d;
    color: #fff;
}
.btn-buy-now:hover {
    background: #ff8c4a;
}
@media (max-width: 768px) {
    .product-action-row {
        flex-direction: column;
        gap: 12px;
    }
    .btn-add-cart, .btn-buy-now {
        width: 100%;
        padding: 16px 0;
        font-size: 1.2rem;
    }
}

/* Related Products Section */
.related-products-section {
    background: #fff;
}

.section-title {
    font-size: 1.4rem;
    color: #222;
    margin-bottom: 8px;
    font-weight: 700;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.title-underline {
    width: 40px;
    height: 2px;
    background: #222;
    margin: 8px auto 0;
}

.related-product-card {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
    height: 100%;
    border: 1px solid #f0f0f0;
}

.related-product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.product-image-container {
    position: relative;
    overflow: hidden;
}

.related-product-image {
    width: 100%;
    height: 220px;
    object-fit: cover;
    transition: transform 0.2s ease;
}

.related-product-card:hover .related-product-image {
    transform: scale(1.02);
}

.related-discount-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    background: #e74c3c;
    color: white;
    padding: 3px 6px;
    border-radius: 3px;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 10;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.related-product-info {
    padding: 12px;
}

.related-product-name {
    font-size: 0.9rem;
    font-weight: 600;
    color: #222;
    margin-bottom: 6px;
    line-height: 1.3;
    height: 2.4em;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.related-product-price {
    margin-bottom: 6px;
}

.related-product-price .current-price {
    font-size: 1rem;
    font-weight: 700;
    color: #e74c3c;
    margin-bottom: 2px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.related-product-price .original-price {
    font-size: 0.85rem;
    color: #999;
    text-decoration: line-through;
    font-weight: 400;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.related-product-rating {
    display: flex;
    align-items: center;
    margin-bottom: 6px;
}

.stars {
    display: flex;
    margin-right: 4px;
}

.stars i {
    color: #ffa500;
    font-size: 0.75rem;
    margin-right: 1px;
}

.rating-count {
    font-size: 0.75rem;
    color: #666;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.related-color-options {
    display: flex;
    gap: 3px;
    margin-bottom: 6px;
}

.related-color-option {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    cursor: pointer;
    transition: transform 0.2s ease;
    border: 1px solid #ddd;
}

.related-color-option:hover {
    transform: scale(1.1);
}

.related-product-sales {
    font-size: 0.75rem;
    color: #666;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.option-value-btn:hover {
    background-color: #f8f9fa !important;
    border-color: #333 !important;
    color: #333 !important;
}

.option-value-btn.selected {
    background-color: #000 !important;
    border-color: #000 !important;
    color: #fff !important;
}

.option-value-btn.selected .badge {
    background-color: #fff !important;
    color: #000 !important;
}

/* Disabled option styles */
.option-value-btn input[disabled] {
    cursor: not-allowed;
}

.option-value-btn:has(input[disabled]) {
    opacity: 0.5;
    cursor: not-allowed;
    background-color: #f5f5f5 !important;
    border-color: #ddd !important;
    color: #999 !important;
}

.option-value-btn:has(input[disabled]):hover {
    background-color: #f5f5f5 !important;
    border-color: #ddd !important;
    color: #999 !important;
}

/* Fallback for browsers that don't support :has() */
.option-value-btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background-color: #f5f5f5 !important;
    border-color: #ddd !important;
    color: #999 !important;
}

.option-value-btn.disabled:hover {
    background-color: #f5f5f5 !important;
    border-color: #ddd !important;
    color: #999 !important;
}

/* Responsive Design for Options */
@media (max-width: 768px) {
    .option-values {
        gap: 4px;
    }
    
    .option-value-btn {
        padding: 8px 12px;
        font-size: 0.85rem;
        min-width: 70px;
    }
    
    .option-title {
        font-size: 0.9rem;
        margin-bottom: 0.3rem;
    }
    
    .option-group {
        padding-bottom: 0.4rem;
        margin-bottom: 0.4rem;
    }
    
    .product-info-section {
        padding-bottom: 0.6rem;
        margin-bottom: 0.6rem;
    }
    
    .product-title {
        font-size: 1.5rem;
    }
    
    .current-price {
        font-size: 1.4rem;
    }
    
    .quantity-label {
        font-size: 1rem;
    }
}

@media (max-width: 576px) {
    .option-value-btn {
        padding: 6px 10px;
        font-size: 0.8rem;
        min-width: 60px;
    }
    
    .color-option {
        width: 18px;
        height: 18px;
        margin-right: 3px;
    }
    
    .option-title {
        font-size: 0.85rem;
        margin-bottom: 0.25rem;
    }
    
    .option-group {
        padding-bottom: 0.3rem;
        margin-bottom: 0.3rem;
    }
    
    .product-info-section {
        padding-bottom: 0.5rem;
        margin-bottom: 0.5rem;
    }
    
    .product-title {
        font-size: 1.3rem;
    }
    
    .current-price {
        font-size: 1.2rem;
    }
    
    .quantity-label {
        font-size: 0.9rem;
    }
}
</style>

<!-- Breadcrumbs -->
<section class="breadcrumb-section py-3 bg-light">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="index.php" class="text-muted text-decoration-none">Trang chủ</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="index.php?page=products" class="text-muted text-decoration-none">Tất cả sản phẩm MOHO</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo htmlspecialchars($product['name']); ?>
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Product Detail Section -->
<section class="product-detail-section py-5">
    <div class="container">
        <div class="row">
            <!-- Product Images Column -->
            <div class="col-lg-7 mb-4">
                <div class="product-images-container product-images-sticky d-flex">
                    <!-- Thumbnail Images Column (Left) -->
                    <div class="thumbnail-column me-3">
                        <div class="thumbnail-scroll-container">
                            <?php 
                            $allImages = array_merge($product['gallery'], $product['description_gallery']);
                            $maxThumbnails = 8;
                            $thumbnailCount = 0;
                            
                            // Hiển thị ảnh từ gallery
                            foreach ($allImages as $index => $image) {
                                if ($thumbnailCount >= $maxThumbnails) break;
                                $isActive = ($index === 0) ? 'active' : '';
                                ?>
                                <div class="thumbnail-item mb-2">
                                    <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                         alt="Thumbnail <?php echo $index + 1; ?>" 
                                         class="thumbnail-image <?php echo $isActive; ?>" 
                                         onclick="setMainImage(this, '<?php echo htmlspecialchars($image['image_path']); ?>')">
                                </div>
                                <?php
                                $thumbnailCount++;
                            }
                            
                            // Nếu không đủ ảnh, thêm ảnh mặc định
                            while ($thumbnailCount < $maxThumbnails) {
                                $isActive = ($thumbnailCount === 0 && empty($allImages)) ? 'active' : '';
                                ?>
                                <div class="thumbnail-item mb-2">
                                    <img src="assets/uploads/product-default.jpg" 
                                         alt="Thumbnail <?php echo $thumbnailCount + 1; ?>" 
                                         class="thumbnail-image <?php echo $isActive; ?>" 
                                         onclick="setMainImage(this, 'assets/uploads/product-default.jpg')">
                                </div>
                                <?php
                                $thumbnailCount++;
                            }
                            ?>
                        </div>
                    </div>
                    
                    <!-- Main Image Container (Right) -->
                    <div class="main-image-container flex-grow-1 position-relative">
                        <?php if (!empty($product['gallery'])): ?>
                            <img src="<?php echo htmlspecialchars($product['gallery'][0]['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="main-product-image img-fluid" id="mainImage">
                        <?php else: ?>
                            <img src="<?php echo $product['image_'] ?: 'assets/uploads/product-default.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="main-product-image img-fluid" id="mainImage">
                        <?php endif; ?>
                        
                        <!-- Navigation Arrows -->
                        <div class="product-nav-arrow left" onclick="changeImage('prev')">
                            <i class="fas fa-chevron-left"></i>
                        </div>
                        <div class="product-nav-arrow right" onclick="changeImage('next')">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </div>
                    
                    <!-- Floating Wishlist Button -->
                    <div class="floating-wishlist-btn">
                        <i class="fas fa-heart"></i>
                    </div>
                </div>
            </div>
            
            <!-- Product Info Column -->
            <div class="col-lg-5">
                <div class="product-info-container">
                    <?php
                    $currentPrice = $productModel->getCurrentPrice($product);
                    $originalPrice = $productModel->getOriginalPrice($product);
                    $discountPercent = $productModel->getDiscountPercent($product);
                    ?>
                    <!-- Tên sản phẩm -->
                    <div class="product-info-section">
                        <h1 class="product-title mb-2" style="font-size:1.5rem; font-weight:700; color:#222; line-height:1.2;">
                            <?php echo htmlspecialchars($product['name']); ?>
                        </h1>
                        
                        <!-- Đánh giá, đã bán, chia sẻ -->
                        <div class="d-flex align-items-center mb-2">
                            <div class="me-2">
                                <span class="text-warning" style="font-size:0.9rem;">
                                    <?php for($i=0;$i<5;$i++): ?><i class="fas fa-star"></i><?php endfor; ?>
                                </span>
                                <span class="text-dark fw-bold ms-1">(109)</span>
                            </div>
                            <div class="text-muted ms-3 small">Đã bán: <?php echo $product['sold_count'] ?? 0; ?></div>
                            <div class="ms-auto small">
                                <span class="text-muted">Chia sẻ:</span>
                                <a href="#" class="text-primary ms-1"><i class="fab fa-facebook"></i></a>
                            </div>
                        </div>
                        <!-- SKU nhỏ, xám -->
                        <div class="mb-2">
                            <span class="text-muted small">SKU: <?php echo $product['id']; ?></span>
                        </div>
                        <!-- Giá -->
                        <div class="d-flex align-items-end mb-3">
                            <div class="me-3">
                                <span class="current-price" style="font-size:1.5rem; color:#e74c3c; font-weight:700;">
                                    <?php echo number_format($currentPrice); ?>₫
                                </span>
                                <?php if($discountPercent > 0): ?>
                                    <span class="original-price ms-2" style="font-size:0.9rem; color:#999; text-decoration:line-through;">
                                        <?php echo number_format($originalPrice); ?>₫
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if($discountPercent > 0): ?>
                                <span class="badge bg-danger ms-2" style="font-size:0.8rem;">-<?php echo $discountPercent; ?>%</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Color Selection -->
                    <?php 
                    $productColors = [];
                    if (!empty($product['color'])) {
                        if (strpos($product['color'], '[') === 0) {
                            $productColors = json_decode($product['color'], true) ?: [];
                        } else {
                            $productColors = [$product['color']];
                        }
                    }
                    if (!empty($productColors)): 
                    ?>
                    <div class="product-info-section">
                        <div class="option-group mb-3">
                            <div class="option-title mb-2" style="font-size: 0.9rem; color: #222; font-weight: 600;">
                                <?php echo htmlspecialchars($productColors[0]); ?>
                            </div>
                            <div class="option-values">
                                <?php 
                                $colorMap = [
                                    'Nâu tự nhiên' => '#8B4513',
                                    'Nâu đậm' => '#654321',
                                    'Nâu sáng' => '#D2691E',
                                    'Nâu' => '#8B4513',
                                    'Đen' => '#000000',
                                    'Trắng' => '#FFFFFF',
                                    'Xám' => '#808080',
                                    'Kem' => '#F5F5DC',
                                    'Vàng' => '#FFD700',
                                    'Đỏ' => '#FF0000',
                                    'Hồng' => '#FFC0CB',
                                    'Xanh lá' => '#228B22',
                                    'Xanh dương' => '#0000FF',
                                    'Tím' => '#800080',
                                    'Cam' => '#FFA500',
                                    'Khác' => 'linear-gradient(45deg, #ff0000, #00ff00, #0000ff)'
                                ];
                                foreach ($productColors as $index => $colorName): 
                                    $hexColor = $colorMap[$colorName] ?? '#808080';
                                    $isActive = ($index === 0) ? 'selected' : '';
                                    $borderStyle = ($colorName === 'Trắng' || $colorName === 'Kem') ? 'border: 1px solid #ddd;' : '';
                                ?>
                                <div class="color-option <?php echo $isActive; ?>" 
                                     style="
                                        width: 24px;
                                        height: 24px;
                                        border-radius: 50%;
                                        background-color: <?php echo $hexColor; ?>;
                                        <?php echo $borderStyle; ?>
                                        cursor: pointer;
                                        transition: all 0.3s ease;
                                        display: inline-block;
                                        margin-right: 8px;
                                        border: 1px solid #ddd;
                                     " 
                                     data-color="<?php echo htmlspecialchars($colorName); ?>"
                                     onclick="selectColor(this, '<?php echo htmlspecialchars($colorName); ?>')">
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <!-- Product Options -->
                    <?php if (!empty($productOptions)): ?>
                    <div class="product-info-section">
                        <div class="product-options-section mb-2">
                            <?php foreach ($productOptions as $option): ?>
                            <div class="option-group mb-2">
                                                            <div class="option-title mb-2" style="font-size: 0.9rem; color: #222; font-weight: 600;">
                                <?php echo htmlspecialchars($option['name']); ?>
                                <?php if ($option['is_required']): ?>
                                    <span class="text-danger">*</span>
                                <?php endif; ?>
                            </div>
                                <div class="option-values">
                                    <?php foreach ($option['values'] as $value): ?>
                                    <label class="option-value-btn me-2 mb-2 <?php echo ($value['stock_quantity'] <= 0) ? 'disabled' : ''; ?>" style="
                                        display: inline-block;
                                        padding: 8px 12px;
                                        border: 1px solid #ddd;
                                        border-radius: 0;
                                        cursor: pointer;
                                        transition: all 0.3s ease;
                                        background: #fff;
                                        color: #333;
                                        font-size: 0.85rem;
                                        font-weight: 500;
                                        min-width: 80px;
                                        text-align: center;
                                        position: relative;
                                    " onclick="<?php echo ($value['stock_quantity'] <= 0) ? '' : "selectOption(this, '" . $option['id'] . "', '" . htmlspecialchars($value['value']) . "', " . $value['id'] . ")"; ?>">
                                        <input type="radio" name="option_<?php echo $option['id']; ?>" 
                                               value="<?php echo htmlspecialchars($value['value']); ?>" 
                                               class="d-none"
                                               data-stock="<?php echo $value['stock_quantity']; ?>"
                                               data-value-id="<?php echo $value['id']; ?>"
                                               <?php echo ($value['stock_quantity'] <= 0) ? 'disabled' : ''; ?>
                                               <?php echo ($option['is_required']) ? 'required' : ''; ?>>
                                        <?php echo htmlspecialchars($value['value']); ?>
                                        <?php if ($value['stock_quantity'] <= 0): ?>
                                            <span class="badge bg-secondary ms-1">Hết hàng</span>
                                        <?php endif; ?>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <!-- Thông tin chi tiết -->
                    <?php if (!empty($productDetails)): ?>
                    <div class="product-info-section">
                        <div class="product-details mt-2">
                            <?php foreach($productDetails as $detail): ?>
                            <div class="detail-item mb-1">
                                <div class="detail-label fw-bold mb-1" style="font-size: 0.85rem;"><?php echo htmlspecialchars($detail['name']); ?>:</div>
                                <div class="detail-value" style="font-size: 0.8rem;"><?php echo htmlspecialchars($detail['description']); ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Quantity Selection -->
                    <div class="product-info-section">
                        <div class="quantity-action">
                            <span class="quantity-label" style="font-size: 0.85rem;">Số lượng:</span>
                            <div class="quantity-group">
                                <button type="button" class="quantity-btn" onclick="changeQuantity(-1)">-</button>
                                <input type="text" id="quantity" class="quantity-input" value="1" min="1" readonly />
                                <button type="button" class="quantity-btn" onclick="changeQuantity(1)">+</button>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="product-action-row">
                            <!-- Nút thêm vào giỏ hàng -->
                            <button class="btn-add-cart" onclick="addToCart()">THÊM VÀO GIỎ</button>
                            <button class="btn-buy-now" onclick="buyNow()">MUA NGAY</button>
                        </div>
                    </div>
                    
                    <!-- Service Info -->
                    <div class="product-info-section">
                        <div class="service-info">
                            <div class="service-item d-flex align-items-center mb-1">
                                <i class="fas fa-check-circle text-success me-2" style="font-size: 0.8rem;"></i>
                                <span class="small" style="font-size: 0.75rem;">Miễn phí giao hàng & lắp đặt tại tất cả các quận huyện thuộc TP.HCM, Hà Nội, Khu đô thị Ecopark, Biên Hòa và một số quận thuộc Bình Dương (*)</span>
                            </div>
                            <div class="service-item d-flex align-items-center mb-1">
                                <i class="fas fa-check-circle text-success me-2" style="font-size: 0.8rem;"></i>
                                <span class="small" style="font-size: 0.75rem;">Miễn phí 1 đổi 1 - Bảo hành 2 năm - Bảo trì trọn đời (**)</span>
                            </div>
                            <div class="service-notes">
                                <small class="text-muted" style="font-size: 0.7rem;">(*) Không áp dụng cho danh mục Đồ Trang Trí</small><br>
                                <small class="text-muted" style="font-size: 0.7rem;">(**) Không áp dụng cho các sản phẩm Clearance. Chỉ bảo hành 01 năm cho khung ghế, mâm và cân đối với Ghế Văn Phòng</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Product Tabs Section -->
<section class="product-tabs-section py-5 bg-light">
    <div class="container">
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs" id="productTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab">
                    Mô tả
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">
                    Đánh giá
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="policy-tab" data-bs-toggle="tab" data-bs-target="#policy" type="button" role="tab">
                    Chính sách
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="care-tab" data-bs-toggle="tab" data-bs-target="#care" type="button" role="tab">
                    Bảo quản
                </button>
            </li>
        </ul>
        
        <!-- Tab Content -->
        <div class="tab-content" id="productTabsContent">
            <!-- Description Tab -->
            <div class="tab-pane fade show active" id="description" role="tabpanel">
                <div class="p-4">
                    <h4 class="mb-3">Mô tả sản phẩm</h4>
                    <div class="product-description">
                        <?php if($product['image_des']): ?>
                            <div class="description-image mb-4">
                                <img src="<?php echo $product['image_des']; ?>" alt="Mô tả sản phẩm" class="img-fluid">
                            </div>
                        <?php endif; ?>
                        
                        <div class="description-text">
                            <?php echo $product['description'] ?? 'Mô tả chi tiết sản phẩm sẽ được cập nhật sớm.'; ?>
                        </div>
                        
                        <?php if($productDetails): ?>
                        <div class="technical-details mt-4">
                            <h5 class="mb-3">Thông số kỹ thuật</h5>
                            <?php foreach($productDetails as $detail): ?>
                            <div class="detail-row mb-2">
                                <strong><?php echo htmlspecialchars($detail['name']); ?>:</strong>
                                <span><?php echo htmlspecialchars($detail['description']); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Reviews Tab -->
            <div class="tab-pane fade" id="reviews" role="tabpanel">
                <div class="p-4">
                    <h4 class="mb-3">Đánh giá sản phẩm</h4>
                    <div class="reviews-container">
                        <div class="text-center py-5">
                            <i class="fas fa-star text-muted" style="font-size: 48px;"></i>
                            <p class="text-muted mt-3">Chưa có đánh giá nào cho sản phẩm này</p>
                            <button class="btn btn-primary">Viết đánh giá đầu tiên</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Policy Tab -->
            <div class="tab-pane fade" id="policy" role="tabpanel">
                <div class="p-4">
                    <h4 class="mb-3">Chính sách</h4>
                    <div class="policy-content">
                        <h5>Chính sách bảo hành</h5>
                        <ul>
                            <li>Bảo hành 2 năm cho tất cả sản phẩm</li>
                            <li>Bảo trì trọn đời</li>
                            <li>Miễn phí 1 đổi 1 trong 30 ngày</li>
                        </ul>
                        
                        <h5>Chính sách vận chuyển</h5>
                        <ul>
                            <li>Miễn phí giao hàng & lắp đặt tại TP.HCM, Hà Nội</li>
                            <li>Giao hàng toàn quốc</li>
                            <li>Thời gian giao hàng: 3-7 ngày</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Care Tab -->
            <div class="tab-pane fade" id="care" role="tabpanel">
                <div class="p-4">
                    <h4 class="mb-3">Hướng dẫn bảo quản</h4>
                    <div class="care-content">
                        <h5>Bảo quản sản phẩm gỗ</h5>
                        <ul>
                            <li>Tránh ánh nắng trực tiếp</li>
                            <li>Giữ độ ẩm phù hợp (40-60%)</li>
                            <li>Lau chùi bằng khăn mềm</li>
                            <li>Không sử dụng hóa chất mạnh</li>
                        </ul>
                        
                        <h5>Bảo quản vải bọc</h5>
                        <ul>
                            <li>Hút bụi thường xuyên</li>
                            <li>Giặt khô khi cần thiết</li>
                            <li>Tránh nước và chất lỏng</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Products Section -->
<section class="related-products-section py-5">
    <div class="container">
        <div class="section-header text-center mb-4">
            <h3 class="section-title">SẢN PHẨM LIÊN QUAN</h3>
            <div class="title-underline"></div>
        </div>
        
        <div class="row">
            <?php foreach($relatedProducts as $relatedProduct): ?>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="related-product-card">
                    <div class="product-image-container position-relative">
                        <?php if (!empty($relatedProduct['gallery'])): ?>
                            <img src="<?php echo htmlspecialchars($relatedProduct['gallery'][0]['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($relatedProduct['name']); ?>" 
                                 class="related-product-image">
                        <?php else: ?>
                            <img src="<?php echo $relatedProduct['image_'] ?: 'assets/uploads/product-default.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($relatedProduct['name']); ?>" 
                                 class="related-product-image">
                        <?php endif; ?>
                        
                        <!-- Discount Badge -->
                        <?php 
                        $discountPercent = $productModel->getDiscountPercent($relatedProduct);
                        if($discountPercent > 0): 
                        ?>
                        <div class="related-discount-badge">-<?php echo $discountPercent; ?>%</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="related-product-info">
                        <h5 class="related-product-name"><?php echo htmlspecialchars($relatedProduct['name']); ?></h5>
                        
                        <div class="related-product-price">
                            <div class="current-price"><?php echo number_format($productModel->getCurrentPrice($relatedProduct)); ?>₫</div>
                            <?php if($productModel->getDiscountPercent($relatedProduct) > 0): ?>
                            <div class="original-price"><?php echo number_format($productModel->getOriginalPrice($relatedProduct)); ?>₫</div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Star Rating - Chỉ hiển thị nếu có rating thật -->
                        <?php if (!empty($relatedProduct['rating']) || !empty($relatedProduct['review_count'])): ?>
                        <div class="related-product-rating">
                            <div class="stars">
                                <?php 
                                $rating = $relatedProduct['rating'] ?? 5;
                                $fullStars = floor($rating);
                                $hasHalfStar = ($rating - $fullStars) >= 0.5;
                                
                                for ($i = 1; $i <= 5; $i++): 
                                    if ($i <= $fullStars): ?>
                                        <i class="fas fa-star"></i>
                                    <?php elseif ($i == $fullStars + 1 && $hasHalfStar): ?>
                                        <i class="fas fa-star-half-alt"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif;
                                endfor; ?>
                            </div>
                            <span class="rating-count">(<?php echo $relatedProduct['review_count'] ?? 0; ?>)</span>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Color Options - Chỉ hiển thị nếu có màu thật -->
                        <?php 
                        $relatedColors = [];
                        if (!empty($relatedProduct['color'])) {
                            if (strpos($relatedProduct['color'], '[') === 0) {
                                $relatedColors = json_decode($relatedProduct['color'], true) ?: [];
                            } else {
                                $relatedColors = [$relatedProduct['color']];
                            }
                        }
                        
                        if (!empty($relatedColors)): 
                        ?>
                        <div class="related-color-options">
                            <?php 
                            $colorMap = [
                                'Nâu tự nhiên' => '#8B4513',
                                'Nâu đậm' => '#654321',
                                'Nâu sáng' => '#D2691E',
                                'Nâu' => '#8B4513',
                                'Đen' => '#000000',
                                'Trắng' => '#FFFFFF',
                                'Xám' => '#808080',
                                'Kem' => '#F5F5DC',
                                'Vàng' => '#FFD700',
                                'Đỏ' => '#FF0000',
                                'Hồng' => '#FFC0CB',
                                'Xanh lá' => '#228B22',
                                'Xanh dương' => '#0000FF',
                                'Tím' => '#800080',
                                'Cam' => '#FFA500',
                                'Khác' => 'linear-gradient(45deg, #ff0000, #00ff00, #0000ff)'
                            ];
                            
                            foreach (array_slice($relatedColors, 0, 3) as $colorName): 
                                $hexColor = $colorMap[$colorName] ?? '#808080';
                                $borderStyle = ($colorName === 'Trắng' || $colorName === 'Kem') ? 'border: 1px solid #ddd;' : '';
                            ?>
                            <div class="related-color-option" 
                                 style="background-color: <?php echo $hexColor; ?>; <?php echo $borderStyle; ?>">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="related-product-sales">Đã bán <?php echo $relatedProduct['sold_count'] ?? 0; ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div id="cart-toast" style="
    display:none;
    position: fixed;
    bottom: 32px;
    right: 32px;
    background: #222;
    color: #fff;
    padding: 16px 28px;
    border-radius: 8px;
    font-size: 1rem;
    z-index: 9999;
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    transition: opacity 0.3s;
"></div>

<script>
// Đảm bảo các hàm ở phạm vi global

// Product Image Gallery
let currentImageIndex = 0;
const images = [
    <?php 
    $allImages = array_merge($product['gallery'], $product['description_gallery']);
    if (!empty($allImages)) {
        foreach ($allImages as $index => $image) {
            echo "'" . htmlspecialchars($image['image_path']) . "'";
            if ($index < count($allImages) - 1) echo ',';
        }
    } else {
        echo "'" . ($product['image_'] ?: 'assets/uploads/product-default.jpg') . "'";
    }
    ?>
];

function setMainImage(thumbnail, imageSrc) {
    document.getElementById('mainImage').src = imageSrc;
    // Update active thumbnail
    document.querySelectorAll('.thumbnail-image').forEach(img => img.classList.remove('active'));
    thumbnail.classList.add('active');
    // Update current index
    currentImageIndex = images.indexOf(imageSrc);
}

function changeImage(direction) {
    if (direction === 'next') {
        currentImageIndex = (currentImageIndex + 1) % images.length;
    } else {
        currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
    }
    const newImageSrc = images[currentImageIndex];
    document.getElementById('mainImage').src = newImageSrc;
    // Update active thumbnail
    document.querySelectorAll('.thumbnail-image').forEach((img, index) => {
        if (index === currentImageIndex) {
            img.classList.add('active');
        } else {
            img.classList.remove('active');
        }
    });
}

function changeQuantity(delta) {
    const quantityInput = document.getElementById('quantity');
    let currentQuantity = parseInt(quantityInput.value);
    currentQuantity = Math.max(1, currentQuantity + delta);
    quantityInput.value = currentQuantity;
}

function selectColor(element, colorName) {
    document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('selected'));
    element.classList.add('selected');
    const optionGroup = element.closest('.option-group');
    if (optionGroup) {
        const colorTitle = optionGroup.querySelector('.option-title');
        if (colorTitle) {
            colorTitle.textContent = colorName;
        }
    }
}

const productOptions = <?php echo json_encode($productOptions); ?>;
const basePrice = <?php echo $product['price']; ?>;
const salePrice = <?php echo $product['sale'] ?? 0; ?>;

function selectOption(element, optionId, optionValue, valueId) {
    const radioInput = element.querySelector('input[type="radio"]');
    if (radioInput.disabled) {
        return;
    }
    const optionGroup = element.closest('.option-group');
    optionGroup.querySelectorAll('.option-value-btn').forEach(btn => {
        btn.classList.remove('selected');
    });
    element.classList.add('selected');
    radioInput.checked = true;
    updatePrice();
}

function updatePrice() {
    let totalAdjustment = 0;
    productOptions.forEach(option => {
        const selectedValue = document.querySelector(`input[name="option_${option.id}"]:checked`);
        if (selectedValue) {
            const valueId = parseInt(selectedValue.getAttribute('data-value-id'));
            const value = option.values.find(v => v.id === valueId);
            if (value) {
                totalAdjustment += parseFloat(value.price_adjustment || 0);
            }
        }
    });
    const finalBasePrice = basePrice + totalAdjustment;
    const finalPrice = salePrice > 0 ? finalBasePrice - salePrice : finalBasePrice;
    const priceElement = document.querySelector('.current-price');
    if (priceElement) {
        priceElement.textContent = finalPrice.toLocaleString('vi-VN') + '₫';
    }
    if (salePrice > 0) {
        const originalPriceElement = document.querySelector('.original-price');
        if (originalPriceElement) {
            originalPriceElement.textContent = finalBasePrice.toLocaleString('vi-VN') + '₫';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    updatePrice();
    handleStickyImages();
});

function addToCart() {
    console.log('addToCart called!');
    const quantity = document.getElementById('quantity').value;
    const productId = <?php echo $product_id; ?>;
    // Lấy màu sắc
    let selectedColor = null;
    const colorEl = document.querySelector('.color-option.selected');
    if (colorEl) {
        selectedColor = colorEl.getAttribute('data-color');
    }
    // Lấy các option khác
    const selectedOptions = {};
    if (selectedColor) {
        selectedOptions['Màu sắc'] = selectedColor;
    }
    if (Array.isArray(productOptions)) {
        productOptions.forEach(option => {
            const selectedValue = document.querySelector(`input[name="option_${option.id}"]:checked`);
            if (selectedValue) {
                selectedOptions[option.name] = selectedValue.value;
            }
        });
    }
    // Validate
    if (!selectedColor && document.querySelector('.color-option')) {
        console.log('Chưa chọn màu sắc!');
        return;
    }
    // Validate required options
    let missingOptions = [];
    if (Array.isArray(productOptions)) {
        productOptions.forEach(option => {
            if (option.is_required) {
                const selectedValue = document.querySelector(`input[name="option_${option.id}"]:checked`);
                if (!selectedValue) missingOptions.push(option.name);
            }
        });
    }
    if (missingOptions.length > 0) {
        console.log('Thiếu option bắt buộc:', missingOptions);
        return;
    }
    // Gửi request
    console.log('Gửi request addToCart:', {productId, quantity, selectedOptions});
    fetch('index.php?page=api/cart/add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            product_id: productId,
            quantity: parseInt(quantity),
            selected_options: selectedOptions
        })
    })
    .then(response => response.text())
    .then(text => {
      console.log('Raw response:', text);
      return JSON.parse(text);
    })
    .then(data => {
        console.log('Kết quả addToCart:', data);
        if (data.success) {
            document.querySelectorAll('.cart-count').forEach(function(el) {
                el.textContent = data.cart_count;
                console.log('Cập nhật cart-count:', el, el.textContent);
            });
            if (typeof updateCartCount === 'function') updateCartCount();
        } else {
            console.log('Lỗi khi thêm vào giỏ hàng:', data.message);
        }
    })
    .catch(error => {
        console.log('Lỗi fetch addToCart:', error);
    });
}

function buyNow() {
    const quantity = document.getElementById('quantity').value;
    const requiredOptions = document.querySelectorAll('input[required]');
    let isValid = true;
    let missingOptions = [];
    requiredOptions.forEach(input => {
        if (!input.checked) {
            const optionName = input.closest('.option-group').querySelector('.option-title').textContent.trim();
            const cleanOptionName = optionName.replace(/\s*\*$/, '');
            missingOptions.push(cleanOptionName);
            isValid = false;
        }
    });
    if (!isValid) {
        // alert('Vui lòng chọn: ' + missingOptions.join(', '));
        return;
    }
    const selectedOptions = {};
    productOptions.forEach(option => {
        const selectedValue = document.querySelector(`input[name="option_${option.id}"]:checked`);
        if (selectedValue) {
            selectedOptions[option.name] = {
                value: selectedValue.value,
                valueId: selectedValue.getAttribute('data-value-id')
            };
        }
    });
    // alert('Chuyển đến trang thanh toán với ' + quantity + ' sản phẩm!');
}

function handleStickyImages() {
    const stickyContainer = document.querySelector('.product-images-sticky');
    const productInfoContainer = document.querySelector('.product-info-container');
    if (!stickyContainer || !productInfoContainer) return;
    const stickyRect = stickyContainer.getBoundingClientRect();
    const productInfoRect = productInfoContainer.getBoundingClientRect();
    const windowHeight = window.innerHeight;
    const productInfoEnd = productInfoRect.bottom;
    const stickyEnd = stickyRect.bottom;
    if (productInfoEnd < 0) {
        stickyContainer.classList.add('sticky-end');
    } else {
        stickyContainer.classList.remove('sticky-end');
    }
}
window.addEventListener('scroll', handleStickyImages);
window.addEventListener('resize', handleStickyImages);

function showCartToast(message) {
    var toast = document.getElementById('cart-toast');
    if (!toast) return;
    toast.textContent = message;
    toast.style.display = 'block';
    toast.style.opacity = 1;
    setTimeout(function() {
        toast.style.opacity = 0;
        setTimeout(function() {
            toast.style.display = 'none';
        }, 400);
    }, 2000);
}

// Thêm hàm updateCartCount nếu chưa có
if (typeof updateCartCount !== 'function') {
    function updateCartCount() {
        fetch('index.php?page=api/cart/get')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.cart-count').forEach(function(el) {
                        el.textContent = data.cart_count;
                    });
                }
            });
    }
}
</script> 