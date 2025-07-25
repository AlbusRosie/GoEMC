<?php

// Hiển thị thông báo cho guest users
if (isset($isGuest) && $isGuest) {
    ?>
    <div class="guest-notice" style="background: linear-gradient(135deg, #ff6b35, #ff8c42); color: white; padding: 1rem; text-align: center; margin-bottom: 1rem;">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Khách hàng: </strong> Vui lòng nhập thông tin chính xác khi đặt đơn để chúng tôi có thể hỗ trợ bạn nhanh nhất.
    </div>
    <?php
}

// Kiểm tra nếu không có sản phẩm nào trong giỏ hàng
if (empty($cartItems)) {
    ?>
    <style>
    .empty-cart-container {
        min-height: 80vh;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .empty-cart-card {
        background: white;
        border-radius: 24px;
        padding: 4rem 3rem;
        text-align: center;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        max-width: 500px;
        width: 100%;
        position: relative;
        overflow: hidden;
    }

    .empty-cart-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #ff6b35, #ff8c42);
    }

    .empty-cart-icon {
        width: 120px;
        height: 120px;
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
        position: relative;
    }

    .empty-cart-icon::after {
        content: '';
        position: absolute;
        inset: -2px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ff6b35, #ff8c42);
        opacity: 0.1;
        z-index: -1;
    }

    .empty-cart-icon i {
        font-size: 3rem;
        color: #64748b;
    }

    .empty-cart-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1rem;
        letter-spacing: -0.025em;
    }

    .empty-cart-subtitle {
        font-size: 1.125rem;
        color: #64748b;
        margin-bottom: 3rem;
        line-height: 1.6;
    }

    .empty-cart-btn {
    background: linear-gradient(135deg, #ff6b35, #ff8c42);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 1rem 2rem;
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    box-shadow: 0 10px 25px -5px rgba(255, 107, 53, 0.3);
}

.empty-cart-btn:hover {
    box-shadow: 0 20px 40px -10px rgba(255, 107, 53, 0.4);
    color: white;
    text-decoration: none;
}

    @media (max-width: 640px) {
        .empty-cart-card {
            padding: 3rem 2rem;
        }
        
        .empty-cart-title {
            font-size: 2rem;
        }
        
        .empty-cart-icon {
            width: 100px;
            height: 100px;
        }
        
        .empty-cart-icon i {
            font-size: 2.5rem;
        }
    }
    </style>

    <div class="empty-cart-container">
        <div class="empty-cart-card">
            <div class="empty-cart-icon">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <h1 class="empty-cart-title">Giỏ hàng trống</h1>
            <p class="empty-cart-subtitle">Bạn chưa có sản phẩm nào trong giỏ hàng. Hãy khám phá bộ sưu tập nội thất gỗ cao cấp của chúng tôi!</p>
            <a href="index.php?page=home" class="empty-cart-btn">
                <i class="fas fa-arrow-right"></i>
                Tiếp tục mua sắm
            </a>
        </div>
    </div>
    <?php
    return;
}
?>

<style>
/* Modern Cart Design */
.cart-page {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    min-height: 100vh;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

.cart-header {
    background: white;
    padding: 2rem 0;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 2rem;
}

.cart-header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}

.cart-title-section h1 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    letter-spacing: -0.025em;
}

.cart-count {
    background: linear-gradient(135deg, #ff6b35, #ff8c42);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
    margin-left: 1rem;
}

.cart-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.continue-shopping-btn {
    background: transparent;
    border: 2px solid #e2e8f0;
    color: #64748b;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.continue-shopping-btn:hover {
    border-color: #ff6b35;
    color: #ff6b35;
    text-decoration: none;
}

/* Cart Container */
.cart-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.cart-grid {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 2rem;
    align-items: start;
}

/* Cart Items Section */
.cart-items-section {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.cart-items-header {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.cart-items-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.cart-items-title i {
    color: #ff6b35;
}

.cart-item {
    padding: 2rem;
    border-bottom: 1px solid #e2e8f0;
    position: relative;
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item:hover {
    background: #f8fafc;
}

/* Cart Item Grid - Cố định layout */
.cart-item-grid {
    display: grid;
    grid-template-columns: 100px 1fr 150px 120px 50px;
    gap: 1.5rem;
    align-items: center;
    width: 100%;
}

/* Product Image - Cố định kích thước */
.product-image-container {
    position: relative;
    width: 100px;
    height: 100px;
    border-radius: 12px;
    overflow: hidden;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-image-placeholder {
    color: #94a3b8;
    font-size: 2rem;
}

/* Product Info - Cố định chiều rộng */
.product-info {
    min-width: 0;
    max-width: 300px;
    flex-shrink: 0;
}

.product-info h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 0.5rem 0;
    line-height: 1.4;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Selected Options - Cố định kích thước */
.selected-options {
    max-width: 200px;
    width: 100%;
    min-width: 0;
    word-break: break-word;
}

.options-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.option-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    background: white;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.option-item:hover {
    border-color: #ff6b35;
    box-shadow: 0 2px 8px rgba(255, 107, 53, 0.1);
}

.option-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #64748b;
    min-width: 80px;
    flex-shrink: 0;
}

.option-value {
    font-size: 0.875rem;
    font-weight: 500;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.color-swatch {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid #e2e8f0;
    display: inline-block;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    position: relative;
}

.color-swatch-white {
    border: 2px solid #d1d5db;
    background: linear-gradient(45deg, #f3f4f6 25%, transparent 25%), 
                linear-gradient(-45deg, #f3f4f6 25%, transparent 25%), 
                linear-gradient(45deg, transparent 75%, #f3f4f6 75%), 
                linear-gradient(-45deg, transparent 75%, #f3f4f6 75%);
    background-size: 4px 4px;
    background-position: 0 0, 0 2px, 2px -2px, -2px 0px;
}

/* Legacy option-tag for backward compatibility */
.product-options {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.option-tag {
    background: #f1f5f9;
    color: #475569;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    border: 1px solid #e2e8f0;
}

/* Quantity Controls - Cố định hoàn toàn */
.quantity-controls {
    display: grid;
    grid-template-columns: 40px 70px 40px;
    align-items: center;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    width: 150px;
    min-width: 150px;
    max-width: 150px;
    height: 44px;
    flex-shrink: 0;
}

.quantity-controls:hover {
    border-color: #ff6b35;
    box-shadow: 0 2px 8px rgba(255, 107, 53, 0.1);
}

.quantity-btn {
    background: white;
    border: none;
    padding: 0;
    color: #64748b;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 44px;
    font-size: 1rem;
    font-weight: 600;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

.quantity-btn:hover {
    background: #ff6b35;
    color: #fff;
    box-shadow: 0 2px 8px rgba(255, 107, 53, 0.3);
    border: none;
}

.quantity-btn:active {
    background: #e55a2b;
    color: #fff;
}

.quantity-btn:first-child:hover {
    background: #ff6b35;
    color: #fff;
}

.quantity-btn:last-child:hover {
    background: #ff8c42;
    color: #fff;
}

.quantity-btn:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.2);
}

.quantity-input {
    border: none;
    background: transparent;
    text-align: center;
    width: 70px;
    height: 44px;
    font-weight: 600;
    color: #1e293b;
    font-size: 1rem;
    -webkit-appearance: none;
    -moz-appearance: textfield;
    margin: 0;
    padding: 0;
    font-family: inherit;
    box-sizing: border-box;
    grid-column: 2;
}

.quantity-input::-webkit-outer-spin-button,
.quantity-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.quantity-input:focus {
    outline: none;
    background: rgba(255, 107, 53, 0.05);
}

.quantity-input:hover {
    background: rgba(255, 107, 53, 0.02);
}

/* Price Section - Cố định kích thước */
.price-section {
    text-align: right;
    width: 120px;
    min-width: 120px;
    max-width: 120px;
    flex-shrink: 0;
}

.price-original {
    text-decoration: line-through;
    color: #94a3b8;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
    will-change: auto;
}

.price-current {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1e293b;
    will-change: auto;
}

.price-sale {
    color: #dc2626;
    font-weight: 700;
}

/* Remove Button - Cố định kích thước */
.remove-btn {
    background: transparent;
    border: none;
    color: #ef4444;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    flex-shrink: 0;
}

.remove-btn:hover {
    background: #fef2f2;
    color: #dc2626;
}

/* Cart Summary */
.cart-summary {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 2rem;
}

.cart-summary-header {
    padding: 1.5rem 2rem;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.cart-summary-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.cart-summary-content {
    padding: 2rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    font-size: 1rem;
}

.summary-row.total {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    border-top: 2px solid #e2e8f0;
    padding-top: 1rem;
    margin-top: 1rem;
}

.checkout-btn {
    width: 100%;
    background: linear-gradient(135deg, #ff6b35, #ff8c42);
    color: white;
    border: none;
    border-radius: 12px;
    padding: 1rem 2rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
}

.checkout-btn:hover {
    box-shadow: 0 10px 25px -5px rgba(255, 107, 53, 0.3);
}

.checkout-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Sale Badge */
.sale-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 10;
}

/* Cart items */
.cart-item:hover {
    background: #f8fafc;
}

/* Quantity button */
.quantity-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Quantity input focus */
.quantity-input:focus {
    outline: none;
    background: rgba(255, 107, 53, 0.05);
    box-shadow: 0 0 0 2px rgba(255, 107, 53, 0.2);
}

/* Responsive Design */
@media (max-width: 1024px) {
    .cart-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .cart-summary {
        position: static;
    }
}

@media (max-width: 768px) {
    .cart-header-content {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .cart-title-section h1 {
        font-size: 2rem;
    }
    
    .cart-item-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .product-image-container {
        width: 80px;
        height: 80px;
        margin: 0 auto;
    }
    
    .product-info {
        text-align: center;
    }
    
    .selected-options {
        margin-top: 0.75rem;
        padding: 0.75rem;
    }
    
    .option-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
        text-align: left;
    }
    
    .option-label {
        min-width: auto;
        font-size: 0.8rem;
    }
    
    .option-value {
        font-size: 0.8rem;
    }
    
    .quantity-controls {
        justify-content: center;
        max-width: 200px;
        margin: 0 auto;
    }
    
    .quantity-btn {
        min-width: 36px;
        padding: 0.6rem;
    }
    
    .quantity-input {
        width: 50px;
        padding: 0.5rem;
    }
    
    .price-section {
        text-align: center;
    }
    
    .remove-btn {
        justify-content: center;
        width: 100%;
        padding: 0.75rem;
        background: #fef2f2;
        border-radius: 8px;
    }
    
    .cart-items-header,
    .cart-summary-header {
        padding: 1rem 1.5rem;
    }
    
    .cart-item,
    .cart-summary-content {
        padding: 1.5rem;
    }
}

@media (max-width: 480px) {
    .cart-container {
        padding: 0 0.5rem;
    }
    
    .cart-title-section h1 {
        font-size: 1.75rem;
    }
    
    .cart-count {
        margin-left: 0.5rem;
        padding: 0.25rem 0.75rem;
    }
    
    .cart-items-header,
    .cart-summary-header {
        padding: 1rem;
    }
    
    .cart-item,
    .cart-summary-content {
        padding: 1rem;
    }
    
    .product-image-container {
        width: 60px;
        height: 60px;
    }
    
    .product-info h3 {
        font-size: 1rem;
    }
    
    .selected-options {
        margin-top: 0.5rem;
        padding: 0.5rem;
    }
    
    .options-title {
        font-size: 0.75rem;
        margin-bottom: 0.5rem;
    }
    
    .option-item {
        padding: 0.4rem;
        gap: 0.2rem;
    }
    
    .option-label {
        font-size: 0.7rem;
    }
    
    .option-value {
        font-size: 0.7rem;
    }
    
    .color-swatch {
        width: 16px;
        height: 16px;
    }
    
    .option-tag {
        font-size: 0.75rem;
        padding: 0.2rem 0.5rem;
    }
}
</style>

<div class="cart-page">
    <!-- Cart Header -->
    <div class="cart-header">
        <div class="cart-container">
            <div class="cart-header-content">
                <div class="cart-title-section">
                    <h1>Giỏ hàng <span class="cart-count"><?php echo $cartCount; ?> sản phẩm</span></h1>
                </div>
                <div class="cart-actions">
                    <a href="index.php?page=home" class="continue-shopping-btn">
                        <i class="fas fa-arrow-left"></i>
                        Tiếp tục mua sắm
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Content -->
    <div class="cart-container">
        <div class="cart-grid">
            <!-- Cart Items -->
            <div class="cart-items-section">
                <div class="cart-items-header">
                    <h2 class="cart-items-title">
                        <i class="fas fa-shopping-cart"></i>
                        Sản phẩm trong giỏ hàng
                    </h2>
                </div>
                
                <?php foreach ($cartItems as $item): ?>
                <?php 
                $price = floatval($item['product_price']);
                $sale = floatval($item['product_sale']);
                $currentPrice = $sale > 0 ? $price - $sale : $price;
                $originalPrice = isset($item['original_price']) ? floatval($item['original_price']) : $price;
                ?>
                <div class="cart-item" data-cart-id="<?php echo $item['id']; ?>" 
                     data-price-original="<?php echo $originalPrice; ?>" 
                     data-price-current="<?php echo $currentPrice; ?>">
                    <?php if (isset($item['sale_amount']) && $item['sale_amount'] > 0): ?>
                    <div class="sale-badge">
                        -<?php echo round(($item['sale_amount'] / $item['original_price']) * 100); ?>%
                    </div>
                    <?php endif; ?>
                    
                    <div class="cart-item-grid">
                        <!-- Product Image -->
                        <div class="product-image-container">
                            <?php 
                            if (!empty($item['product_image'])) {
                                $img = $item['product_image'];
                            } elseif (!empty($item['product_main_images'])) {
                                $mainImages = json_decode($item['product_main_images'], true);
                                $img = $mainImages[0] ?? 'assets/uploads/product-default.jpg';
                            } else {
                                $img = 'assets/uploads/product-default.jpg';
                            }
                            ?>
                            <?php if (!empty($img)): ?>
                                <img src="<?php echo $img; ?>" 
                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                     class="product-image">
                            <?php else: ?>
                                <div class="product-image-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Product Info -->
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                            
                            <!-- Selected Options Display -->
                            <?php if (!empty($item['selected_options_array'])): ?>
                            <div class="selected-options">
                                <div class="options-list">
                                    <?php foreach ($item['selected_options_array'] as $optionName => $optionValue): ?>
                                    <div class="option-item">
                                        <span class="option-label"><?php echo htmlspecialchars($optionName); ?>:</span>
                                        <span class="option-value">
                                            <?php if (strtolower($optionName) === 'màu sắc' || strtolower($optionName) === 'color' || strtolower($optionName) === 'màu'): ?>
                                                <?php 
                                                // Xử lý màu sắc
                                                $colorValue = htmlspecialchars($optionValue);
                                                $colorClass = '';
                                                
                                                // Map tên màu tiếng Việt sang mã màu
                                                $colorMap = [
                                                    'đỏ' => '#dc2626',
                                                    'xanh dương' => '#2563eb',
                                                    'xanh lá' => '#16a34a',
                                                    'vàng' => '#ca8a04',
                                                    'cam' => '#ea580c',
                                                    'tím' => '#9333ea',
                                                    'hồng' => '#ec4899',
                                                    'nâu' => '#92400e',
                                                    'đen' => '#000000',
                                                    'trắng' => '#ffffff',
                                                    'xám' => '#6b7280',
                                                    'red' => '#dc2626',
                                                    'blue' => '#2563eb',
                                                    'green' => '#16a34a',
                                                    'yellow' => '#ca8a04',
                                                    'orange' => '#ea580c',
                                                    'purple' => '#9333ea',
                                                    'pink' => '#ec4899',
                                                    'brown' => '#92400e',
                                                    'black' => '#000000',
                                                    'white' => '#ffffff',
                                                    'gray' => '#6b7280'
                                                ];
                                                
                                                $backgroundColor = $colorMap[strtolower($colorValue)] ?? $colorValue;
                                                if ($backgroundColor === '#ffffff') {
                                                    $colorClass = 'color-swatch-white';
                                                }
                                                ?>
                                                <span class="color-swatch <?php echo $colorClass; ?>" style="background-color: <?php echo $backgroundColor; ?>"></span>
                                                <?php echo $colorValue; ?>
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($optionValue); ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Quantity Controls -->
                        <div class="quantity-controls">
                            <button type="button" class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, -1)" title="Giảm số lượng">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" 
                                   min="1" max="99" onchange="updateQuantity(<?php echo $item['id']; ?>, this.value, true)" title="Số lượng">
                            <button type="button" class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, 1)" title="Tăng số lượng">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        
                        <!-- Price -->
                        <div class="price-section">
                            <?php 
                            $totalPrice = $currentPrice * $item['quantity'];
                            $totalOriginalPrice = $originalPrice * $item['quantity'];
                            ?>
                            <!-- Luôn hiển thị giá gốc -->
                            <div class="price-original" style="text-decoration: line-through; color: #94a3b8; font-size: 0.875rem; margin-bottom: 0.25rem;">
                                <?php echo number_format($totalOriginalPrice); ?>₫
                            </div>
                            <!-- Hiển thị giá hiện tại -->
                            <?php if ($sale > 0): ?>
                                <div class="price-current price-sale">
                                    <?php echo number_format($totalPrice); ?>₫
                                </div>
                            <?php else: ?>
                                <div class="price-current">
                                    <?php echo number_format($totalPrice); ?>₫
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Remove Button -->
                        <button class="remove-btn" onclick="removeFromCart(<?php echo $item['id']; ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Cart Summary -->
            <div class="cart-summary">
                <div class="cart-summary-header">
                    <h3 class="cart-summary-title">Tổng đơn hàng</h3>
                </div>
                <div class="cart-summary-content">
                    <div class="summary-row">
                        <span>Tạm tính:</span>
                        <span><?php echo number_format($cartTotal); ?>₫</span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Phí vận chuyển:</span>
                        <span id="shipping-fee">Miễn phí</span>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Tổng cộng:</span>
                        <span id="total-amount"><?php echo number_format($cartTotal); ?>₫</span>
                    </div>
                    
                    <button class="checkout-btn" onclick="proceedToCheckout()">
                        <i class="fas fa-credit-card"></i>
                        Tiến hành thanh toán
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Cập nhật số lượng sản phẩm
function updateQuantity(cartId, change, isDirectInput = false) {
    let quantity;
    let oldQuantity;
    
    const input = document.querySelector(`[data-cart-id="${cartId}"] .quantity-input`);
    const cartItem = document.querySelector(`[data-cart-id="${cartId}"]`);
    
    if (isDirectInput) {
        quantity = parseInt(change);
        oldQuantity = parseInt(input.value);
    } else {
        oldQuantity = parseInt(input.value);
        quantity = oldQuantity + parseInt(change);
    }
    
    if (quantity < 1) quantity = 1;
    if (quantity > 99) quantity = 99;
    
    // Nếu số lượng không thay đổi, không làm gì
    if (quantity === oldQuantity) return;
    
    // Cập nhật input ngay lập tức để tránh giật
    input.value = quantity;
    
    
    
    // Gửi request cập nhật
    fetch('index.php?page=api/cart/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            order_detail_id: cartId,
            quantity: quantity
        })
    })
            .then(response => {
            return response.json();
        })
            .then(data => {
        if (data.success) {
            // Cập nhật giá tiền của item này ngay lập tức
            updateItemPrice(cartId, quantity);
            
            // Cập nhật tổng tiền từ response
            if (data.cart_total !== undefined) {
                updateCartTotal(data.cart_total);
            }
            
            // Cập nhật cart count nếu có
            if (data.cart_count !== undefined) {
                const cartCountElement = document.querySelector('.cart-count');
                if (cartCountElement) {
                    cartCountElement.textContent = data.cart_count + ' sản phẩm';
                }
            }
        } else {
            // Khôi phục giá trị cũ nếu có lỗi
            input.value = oldQuantity;
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        // Khôi phục giá trị cũ nếu có lỗi
        input.value = oldQuantity;
        alert('Có lỗi xảy ra khi cập nhật số lượng');
    })
    .finally(() => {
        // Hoàn thành
    });
}

// Cập nhật giá tiền của item
function updateItemPrice(cartId, quantity) {
    const cartItem = document.querySelector(`[data-cart-id="${cartId}"]`);
    const priceSection = cartItem.querySelector('.price-section');
    
    // Lấy giá đơn vị từ data attributes hoặc tính toán
    const priceOriginal = parseFloat(cartItem.dataset.priceOriginal || 0);
    const priceCurrent = parseFloat(cartItem.dataset.priceCurrent || 0);
    
    if (priceOriginal > 0 && priceCurrent > 0) {
        const totalOriginal = priceOriginal * quantity;
        const totalCurrent = priceCurrent * quantity;
        
        // Cập nhật giá gốc ngay lập tức
        const originalElement = priceSection.querySelector('.price-original');
        if (originalElement) {
            originalElement.textContent = number_format(totalOriginal) + '₫';
        }
        
        // Cập nhật giá hiện tại ngay lập tức
        const currentElement = priceSection.querySelector('.price-current');
        if (currentElement) {
            currentElement.textContent = number_format(totalCurrent) + '₫';
        }
    }
}

// Cập nhật tổng tiền
function updateCartTotal(newTotal) {
    const totalElement = document.getElementById('total-amount');
    if (totalElement) {
        totalElement.textContent = number_format(newTotal) + '₫';
    }
}

// Xóa sản phẩm khỏi giỏ hàng
function removeFromCart(cartId) {
    if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
        return;
    }
    
    const cartItem = document.querySelector(`[data-cart-id="${cartId}"]`);
    
    fetch('index.php?page=api/cart/remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            order_detail_id: cartId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Xóa item ngay lập tức
            cartItem.remove();
            
            // Cập nhật cart count trong header
            const cartCountElement = document.querySelector('.cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = data.cart_count + ' sản phẩm';
            }
            
            // Reload trang nếu giỏ hàng trống
            if (data.cart_count == 0) {
                location.reload();
            } else {
                // Cập nhật tổng tiền từ response
                if (data.cart_total !== undefined) {
                    updateCartTotal(data.cart_total);
                }
            }
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        alert('Có lỗi xảy ra khi xóa sản phẩm');
    });
}

// Tiến hành thanh toán
function proceedToCheckout() {
    window.location.href = 'index.php?page=checkout';
}

// Tính phí vận chuyển khi trang load
document.addEventListener('DOMContentLoaded', function() {
    const subtotal = <?php echo $cartTotal; ?>;
    const shippingFeeElement = document.getElementById('shipping-fee');
    const totalAmountElement = document.getElementById('total-amount');
    
    // Kiểm tra element tồn tại trước khi sử dụng
    if (!shippingFeeElement || !totalAmountElement) {
        return;
    }
    
    // Tạm thời bỏ qua API call để tránh lỗi
    shippingFeeElement.textContent = 'Miễn phí';
    totalAmountElement.textContent = number_format(subtotal) + '₫';
});

// Hàm format số
function number_format(number) {
    return new Intl.NumberFormat('vi-VN').format(number);
}
</script> 