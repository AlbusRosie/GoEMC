<?php
// Dữ liệu đã được chuẩn bị từ OrderController
// $order và $orderDetails đã có sẵn
?>

<style>
.order-detail-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 30%, #fafafa 70%, #f8f9fa 100%);
    padding: 40px 0;
    position: relative;
    overflow: hidden;
    font-family: 'Playfair Display', serif;
    min-height: 100vh;
}

.order-detail-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="0.3" fill="%23000" opacity="0.02"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    pointer-events: none;
}

.order-container {
    position: relative;
    z-index: 2;
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.08);
    border: 1px solid #f0f0f0;
    overflow: hidden;
    max-width: 1000px;
    margin: 0 auto;
}

.order-header {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    color: #1a1a1a;
    padding: 3rem 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
    border-bottom: 1px solid #e0e0e0;
}

.order-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="0.3" fill="%23000" opacity="0.03"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
    pointer-events: none;
}

.order-header::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(0,0,0,0.02) 0%, transparent 70%);
    pointer-events: none;
}

.order-title {
    font-family: 'Playfair Display', serif;
    font-size: 3rem;
    font-weight: 300;
    margin-bottom: 1rem;
    line-height: 1.1;
    letter-spacing: -1px;
    position: relative;
    z-index: 2;
    color: #1a1a1a;
}

.order-subtitle {
    font-size: 1.1rem;
    color: #666;
    font-weight: 300;
    letter-spacing: 0.5px;
    position: relative;
    z-index: 2;
}

.order-badge {
    display: inline-block;
    background: #ff6b35;
    color: white;
    padding: 0.8rem 1.5rem;
    border-radius: 25px;
    font-size: 0.7rem;
    font-weight: 600;
    margin-bottom: 2rem;
    text-transform: uppercase;
    letter-spacing: 2px;
    position: relative;
    z-index: 2;
    box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
}

.order-info {
    padding: 3rem 2rem;
    border-bottom: 1px solid #e9ecef;
}

.order-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.order-detail {
    text-align: center;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.order-detail:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.order-detail h6 {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
}

.order-detail p {
    color: #1a1a1a;
    font-size: 1.1rem;
    font-weight: 400;
    margin: 0;
    letter-spacing: 0.3px;
}

.customer-info {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 2rem;
    border: 1px solid #e0e0e0;
}

.customer-info h5 {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 400;
    color: #1a1a1a;
    margin-bottom: 1.5rem;
    letter-spacing: 0.5px;
}

.customer-info p {
    color: #666;
    font-size: 1rem;
    font-weight: 300;
    letter-spacing: 0.3px;
    margin-bottom: 0.5rem;
}

.customer-info strong {
    color: #1a1a1a;
    font-weight: 500;
}

.order-items {
    padding: 3rem 2rem;
}

.order-items h5 {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 400;
    color: #1a1a1a;
    margin-bottom: 2rem;
    letter-spacing: 0.5px;
}

.order-item {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.5rem;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 1rem;
    background: white;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.order-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.order-item-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}

.order-item-info {
    flex: 1;
}

.order-item-name {
    font-weight: 500;
    color: #1a1a1a;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
    letter-spacing: 0.3px;
}

.order-item-options {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 0.5rem;
    font-weight: 300;
    letter-spacing: 0.3px;
}

.order-item-price {
    font-weight: 600;
    color: #ff6b35;
    font-size: 1.1rem;
}

.order-item-quantity {
    color: #666;
    font-size: 0.9rem;
    font-weight: 300;
    letter-spacing: 0.3px;
}

.order-summary {
    padding: 3rem 2rem;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}

.order-summary h5 {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 400;
    color: #1a1a1a;
    margin-bottom: 2rem;
    letter-spacing: 0.5px;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
    padding: 0.8rem 0;
    border-bottom: 1px solid #e0e0e0;
    font-weight: 300;
    letter-spacing: 0.3px;
}

.summary-item.total {
    border-top: 2px solid #e0e0e0;
    padding-top: 1rem;
    margin-top: 1rem;
    font-weight: 600;
    font-size: 1.3rem;
    color: #ff6b35;
    border-bottom: none;
}

.status-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.status-badge.pending {
    background: #fff3cd;
    color: #856404;
}

.status-badge.confirmed {
    background: #d1ecf1;
    color: #0c5460;
}

.status-badge.preparing {
    background: #fff3cd;
    color: #856404;
}

.status-badge.shipping {
    background: #cce5ff;
    color: #004085;
}

.status-badge.delivered {
    background: #d4edda;
    color: #155724;
}

.status-badge.cancelled {
    background: #f8d7da;
    color: #721c24;
}

.status-badge.completed {
    background: #d4edda;
    color: #155724;
}

.status-badge.paid {
    background: #d4edda;
    color: #155724;
}

.status-badge.failed {
    background: #f8d7da;
    color: #721c24;
}

.action-buttons {
    padding: 3rem 2rem;
    display: flex;
    gap: 1rem;
    justify-content: center;
    background: white;
}

.btn {
    padding: 1rem 2rem;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    letter-spacing: 0.3px;
}

.btn-primary {
    background: #ff6b35;
    color: white;
}

.btn-primary:hover {
    background: #e55a2b;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 107, 53, 0.3);
}

.btn-outline {
    background: transparent;
    color: #1a1a1a;
    border: 2px solid #1a1a1a;
}

.btn-outline:hover {
    background: #1a1a1a;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(26, 26, 26, 0.3);
}

@media (max-width: 768px) {
    .order-container {
        margin: 0 1rem;
    }
    
    .order-header {
        padding: 2rem 1rem;
    }
    
    .order-title {
        font-size: 2rem;
    }
    
    .order-details {
        grid-template-columns: 1fr;
    }
    
    .order-item {
        flex-direction: column;
        text-align: center;
    }
    
    .action-buttons {
        flex-direction: column;
        padding: 2rem 1rem;
    }
    
    .order-info,
    .order-items,
    .order-summary {
        padding: 2rem 1rem;
    }
}
</style>

<div class="order-detail-section">
    <div class="order-container">
        <!-- Header -->
        <div class="order-header">
            <div class="order-badge">Đơn hàng của bạn</div>
            <h1 class="order-title">
                Đơn hàng #<?php echo htmlspecialchars($order['id']); ?>
            </h1>
            <p class="order-subtitle">Chi tiết đơn hàng và thông tin giao hàng</p>
        </div>
        
        <!-- Thông tin đơn hàng -->
        <div class="order-info">
            <div class="order-details">
                <div class="order-detail">
                    <h6>Trạng thái đơn hàng</h6>
                    <p>
                        <span class="status-badge <?php echo $order['status']; ?>">
                            <?php 
                            $statusLabels = [
                                'confirmed' => 'Đã đặt đơn',
                                'preparing' => 'Đang chuẩn bị',
                                'shipping' => 'Đang vận chuyển',
                                'delivered' => 'Đang giao',
                                'cancelled' => 'Đã hủy',
                                'completed' => 'Hoàn thành'
                            ];
                            echo $statusLabels[$order['status']] ?? ucfirst($order['status']);
                            ?>
                        </span>
                    </p>
                </div>
                <div class="order-detail">
                    <h6>Trạng thái thanh toán</h6>
                    <p>
                        <span class="status-badge <?php echo $order['payment_status']; ?>">
                            <?php echo ucfirst($order['payment_status']); ?>
                        </span>
                    </p>
                </div>
                <div class="order-detail">
                    <h6>Phương thức thanh toán</h6>
                    <p><?php echo ucfirst($order['payment_method'] ?? 'Chưa chọn'); ?></p>
                </div>
                <div class="order-detail">
                    <h6>Ngày đặt</h6>
                    <p><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                </div>
            </div>
            
            <!-- Thông tin khách hàng -->
            <div class="customer-info">
                <h5><i class="fas fa-user me-2"></i>Thông tin khách hàng</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Họ và tên:</strong> <?php echo htmlspecialchars($order['guest_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['guest_email']); ?></p>
                        <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['guest_phone']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Địa chỉ giao hàng:</strong></p>
                        <p><?php echo htmlspecialchars($order['delivery_address']); ?></p>
                        <?php if ($order['delivery_city']): ?>
                        <p><?php echo htmlspecialchars($order['delivery_city']); ?></p>
                        <?php endif; ?>
                        <?php if ($order['delivery_district']): ?>
                        <p><?php echo htmlspecialchars($order['delivery_district']); ?></p>
                        <?php endif; ?>
                        <?php if ($order['delivery_ward']): ?>
                        <p><?php echo htmlspecialchars($order['delivery_ward']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Danh sách sản phẩm -->
        <div class="order-items">
            <h5><i class="fas fa-box me-2"></i>Sản phẩm đã đặt</h5>
            <?php foreach ($orderDetails as $item): ?>
            <div class="order-item">
                <img src="<?php echo htmlspecialchars($item['product_image'] ?? 'assets/uploads/product-default.jpg'); ?>" 
                     alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                     class="order-item-image">
                <div class="order-item-info">
                    <div class="order-item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                    <?php if (!empty($item['selected_options_array'])): ?>
                    <div class="order-item-options">
                        <?php foreach ($item['selected_options_array'] as $optionName => $optionValue): ?>
                        <div><?php echo htmlspecialchars($optionName); ?>: <?php echo htmlspecialchars($optionValue); ?></div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <div class="order-item-price"><?php echo number_format($item['price']); ?>₫</div>
                </div>
                <div class="order-item-quantity">
                    Số lượng: <?php echo $item['quantity']; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Tổng kết -->
        <div class="order-summary">
            <h5><i class="fas fa-calculator me-2"></i>Tổng kết đơn hàng</h5>
            <div class="summary-item">
                <span>Tạm tính:</span>
                <span><?php echo number_format($order['subtotal']); ?>₫</span>
            </div>
            <div class="summary-item">
                <span>Phí vận chuyển:</span>
                <span><?php echo number_format($order['shipping_fee']); ?>₫</span>
            </div>
            <?php if ($order['discount_amount'] > 0): ?>
            <div class="summary-item">
                <span>Giảm giá:</span>
                <span>-<?php echo number_format($order['discount_amount']); ?>₫</span>
            </div>
            <?php endif; ?>
            <div class="summary-item total">
                <span>Tổng cộng:</span>
                <span><?php echo number_format($order['total']); ?>₫</span>
            </div>
        </div>
        
        <!-- Action buttons -->
        <div class="action-buttons">
            <?php if ($order['payment_status'] === 'pending' && $order['payment_method'] !== 'cash'): ?>
            <a href="index.php?page=payment-info&id=<?php echo $order['id']; ?>" class="btn btn-primary">
                <i class="fas fa-credit-card"></i>
                Thanh toán ngay
            </a>
            <?php endif; ?>
            <a href="index.php?page=home" class="btn btn-outline">
                <i class="fas fa-home"></i>
                Về trang chủ
            </a>
        </div>
    </div>
</div> 