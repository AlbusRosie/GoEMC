<style>
.checkout-section {
    background: #f8f9fa;
    min-height: 100vh;
}

.checkout-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
}

.checkout-header {
    background: #f8f9fa;
    padding: 20px 30px;
    border-bottom: 1px solid #e9ecef;
}

.checkout-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #222;
    margin: 0;
}

.form-section {
    padding: 30px;
}

.section-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #222;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e9ecef;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.form-control {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 12px 15px;
    font-size: 0.95rem;
    transition: border-color 0.2s ease;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

.form-select {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 12px 15px;
    font-size: 0.95rem;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px 12px;
}

.payment-methods {
    display: flex;
    gap: 15px;
    margin-top: 10px;
}

.payment-method {
    flex: 1;
    border: 2px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.payment-method:hover {
    border-color: #007bff;
}

.payment-method.selected {
    border-color: #007bff;
    background-color: #f8f9ff;
}

.payment-method input[type="radio"] {
    display: none;
}

.payment-method i {
    font-size: 1.5rem;
    margin-bottom: 8px;
    display: block;
}

.coupon-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.coupon-input-group {
    display: flex;
    gap: 10px;
}

.coupon-input {
    flex: 1;
}

.coupon-btn {
    padding: 12px 20px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s ease;
}

.coupon-btn:hover {
    background: #218838;
}

.coupon-btn:disabled {
    background: #6c757d;
    cursor: not-allowed;
}

.order-summary {
    background: #f8f9fa;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.summary-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #222;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e9ecef;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    font-size: 0.95rem;
}

.summary-item.total {
    font-size: 1.2rem;
    font-weight: 700;
    color: #e74c3c;
    border-top: 1px solid #e9ecef;
    padding-top: 15px;
    margin-top: 15px;
}

.place-order-btn {
    width: 100%;
    padding: 15px;
    background: #e74c3c;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s ease;
}

.place-order-btn:hover {
    background: #c0392b;
}

.place-order-btn:disabled {
    background: #6c757d;
    cursor: not-allowed;
}

.cart-items {
    margin-bottom: 20px;
}

.cart-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #e9ecef;
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.cart-item-info {
    flex: 1;
}

.cart-item-name {
    font-weight: 600;
    color: #222;
    margin-bottom: 5px;
}

.cart-item-options {
    font-size: 0.85rem;
    color: #666;
    margin-bottom: 5px;
}

.cart-item-price {
    font-weight: 600;
    color: #e74c3c;
}

@media (max-width: 768px) {
    .checkout-header {
        padding: 15px 20px;
    }
    
    .form-section {
        padding: 20px;
    }
    
    .payment-methods {
        flex-direction: column;
    }
    
    .coupon-input-group {
        flex-direction: column;
    }
    
    .order-summary {
        margin-top: 20px;
        padding: 20px;
    }
}
</style>

<section class="checkout-section py-5">
    <div class="container">
        <div class="row">
            <!-- Checkout Form -->
            <div class="col-lg-8 mb-4">
                <div class="checkout-container">
                    <div class="checkout-header">
                        <h2 class="checkout-title">
                            <i class="fas fa-credit-card me-2"></i>
                            Thông tin đặt hàng
                        </h2>
                    </div>
                    
                    <form id="checkout-form" class="form-section">
                        <!-- Thông tin khách hàng -->
                        <div class="section-title">
                            <i class="fas fa-user me-2"></i>
                            Thông tin khách hàng
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Họ và tên *</label>
                                    <input type="text" class="form-control" name="guest_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Số điện thoại *</label>
                                    <input type="tel" class="form-control" name="guest_phone" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="guest_email" required>
                        </div>
                        
                        <!-- Địa chỉ giao hàng -->
                        <div class="section-title">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Địa chỉ giao hàng
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Địa chỉ chi tiết *</label>
                            <textarea class="form-control" name="delivery_address" rows="3" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Tỉnh/Thành phố</label>
                                    <select class="form-select" name="delivery_city" id="delivery_city">
                                        <option value="">Chọn tỉnh/thành phố</option>
                                        <option value="TP.HCM">TP.HCM</option>
                                        <option value="Hà Nội">Hà Nội</option>
                                        <option value="Đà Nẵng">Đà Nẵng</option>
                                        <option value="Cần Thơ">Cần Thơ</option>
                                        <option value="Khác">Khác</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Quận/Huyện</label>
                                    <input type="text" class="form-control" name="delivery_district">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Phường/Xã</label>
                                    <input type="text" class="form-control" name="delivery_ward">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Ghi chú giao hàng</label>
                            <textarea class="form-control" name="delivery_notes" rows="2"></textarea>
                        </div>
                        
                        <!-- Phương thức thanh toán -->
                        <div class="section-title">
                            <i class="fas fa-credit-card me-2"></i>
                            Phương thức thanh toán
                        </div>
                        
                        <div class="payment-methods">
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="cash" checked>
                                <i class="fas fa-money-bill-wave text-success"></i>
                                <div>Tiền mặt</div>
                            </label>
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="bank_transfer">
                                <i class="fas fa-university text-primary"></i>
                                <div>Chuyển khoản</div>
                            </label>
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="momo">
                                <i class="fas fa-mobile-alt text-danger"></i>
                                <div>MoMo</div>
                            </label>
                        </div>
                        
                        <!-- Mã giảm giá -->
                        <div class="coupon-section">
                            <div class="section-title" style="margin-bottom: 15px;">
                                <i class="fas fa-tag me-2"></i>
                                Mã giảm giá
                            </div>
                            <div class="coupon-input-group">
                                <input type="text" class="form-control coupon-input" id="coupon_code" placeholder="Nhập mã giảm giá">
                                <button type="button" class="coupon-btn" onclick="applyCoupon()">
                                    Áp dụng
                                </button>
                            </div>
                            <div id="coupon-message" class="mt-2"></div>
                        </div>
                        
                        <!-- Ghi chú -->
                        <div class="form-group">
                            <label class="form-label">Ghi chú đơn hàng</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="order-summary">
                    <h3 class="summary-title">Đơn hàng của bạn</h3>
                    
                    <!-- Cart Items -->
                    <div class="cart-items">
                        <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item">
                            <img src="<?php echo $item['image_'] ?: 'assets/uploads/product-default.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                 class="cart-item-image">
                            <div class="cart-item-info">
                                <div class="cart-item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <?php if (!empty($item['selected_options_array'])): ?>
                                <div class="cart-item-options">
                                    <?php foreach ($item['selected_options_array'] as $optionName => $optionValue): ?>
                                    <div><?php echo htmlspecialchars($optionName); ?>: <?php echo htmlspecialchars($optionValue); ?></div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                <div class="cart-item-price">
                                    <?php echo number_format($item['current_price']); ?>₫ x <?php echo $item['quantity']; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Summary -->
                    <div class="summary-item">
                        <span>Tạm tính:</span>
                        <span><?php echo number_format($subtotal); ?>₫</span>
                    </div>
                    
                    <div class="summary-item">
                        <span>Phí vận chuyển:</span>
                        <span id="shipping-fee">Đang tính...</span>
                    </div>
                    
                    <div class="summary-item" id="discount-row" style="display: none;">
                        <span>Giảm giá:</span>
                        <span id="discount-amount">0₫</span>
                    </div>
                    
                    <div class="summary-item total">
                        <span>Tổng cộng:</span>
                        <span id="total-amount"><?php echo number_format($subtotal); ?>₫</span>
                    </div>
                    
                    <button class="place-order-btn" onclick="placeOrder()">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Đặt hàng ngay
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
let appliedCoupon = null;
let discountAmount = 0;

// Áp dụng mã giảm giá
function applyCoupon() {
    const couponCode = document.getElementById('coupon_code').value.trim();
    const messageDiv = document.getElementById('coupon-message');
    const applyBtn = document.querySelector('.coupon-btn');
    
    if (!couponCode) {
        showCouponMessage('Vui lòng nhập mã giảm giá', 'error');
        return;
    }
    
    // Disable button
    applyBtn.disabled = true;
    applyBtn.textContent = 'Đang xử lý...';
    
    fetch('index.php?page=api/order/apply-coupon', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            coupon_code: couponCode,
            subtotal: <?php echo $subtotal; ?>
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            appliedCoupon = data.coupon;
            discountAmount = data.discount;
            showCouponMessage(`Áp dụng thành công! Giảm ${number_format(discountAmount)}₫`, 'success');
            updateTotal();
        } else {
            showCouponMessage(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showCouponMessage('Có lỗi xảy ra', 'error');
    })
    .finally(() => {
        applyBtn.disabled = false;
        applyBtn.textContent = 'Áp dụng';
    });
}

// Hiển thị thông báo mã giảm giá
function showCouponMessage(message, type) {
    const messageDiv = document.getElementById('coupon-message');
    messageDiv.textContent = message;
    messageDiv.className = `mt-2 ${type === 'success' ? 'text-success' : 'text-danger'}`;
}

// Cập nhật tổng tiền
function updateTotal() {
    const subtotal = <?php echo $subtotal; ?>;
    const shippingFee = parseFloat(document.getElementById('shipping-fee').textContent.replace(/[^\d]/g, '')) || 0;
    const total = subtotal + shippingFee - discountAmount;
    
    // Hiển thị giảm giá
    if (discountAmount > 0) {
        document.getElementById('discount-row').style.display = 'flex';
        document.getElementById('discount-amount').textContent = `-${number_format(discountAmount)}₫`;
    } else {
        document.getElementById('discount-row').style.display = 'none';
    }
    
    document.getElementById('total-amount').textContent = number_format(total) + '₫';
}

// Đặt hàng
function placeOrder() {
    const form = document.getElementById('checkout-form');
    const formData = new FormData(form);
    
    // Validate required fields
    const requiredFields = ['guest_name', 'guest_email', 'guest_phone', 'delivery_address'];
    for (let field of requiredFields) {
        if (!formData.get(field)) {
            alert('Vui lòng điền đầy đủ thông tin bắt buộc');
            return;
        }
    }
    
    // Prepare order data
    const orderData = {
        guest_name: formData.get('guest_name'),
        guest_email: formData.get('guest_email'),
        guest_phone: formData.get('guest_phone'),
        delivery_address: formData.get('delivery_address'),
        delivery_city: formData.get('delivery_city'),
        delivery_district: formData.get('delivery_district'),
        delivery_ward: formData.get('delivery_ward'),
        delivery_notes: formData.get('delivery_notes'),
        payment_method: formData.get('payment_method'),
        notes: formData.get('notes'),
        coupon_code: appliedCoupon ? appliedCoupon.code : null
    };
    
    // Disable button
    const orderBtn = document.querySelector('.place-order-btn');
    orderBtn.disabled = true;
    orderBtn.textContent = 'Đang xử lý...';
    
    fetch('index.php?page=api/order/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Đặt hàng thành công! Mã đơn hàng: ' + data.order_id);
            window.location.href = `index.php?page=order&id=${data.order_id}`;
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi đặt hàng');
    })
    .finally(() => {
        orderBtn.disabled = false;
        orderBtn.innerHTML = '<i class="fas fa-shopping-cart me-2"></i>Đặt hàng ngay';
    });
}

// Tính phí vận chuyển khi thay đổi thành phố
document.getElementById('delivery_city').addEventListener('change', function() {
    const city = this.value;
    const subtotal = <?php echo $subtotal; ?>;
    
    fetch('index.php?page=api/order/calculate-shipping', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            subtotal: subtotal,
            delivery_city: city
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const shippingFee = data.shipping_fee;
            document.getElementById('shipping-fee').textContent = shippingFee > 0 ? 
                number_format(shippingFee) + '₫' : 'Miễn phí';
            updateTotal();
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});

// Payment method selection
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.payment-method').forEach(method => {
            method.classList.remove('selected');
        });
        this.closest('.payment-method').classList.add('selected');
    });
});

// Initialize payment method selection
document.querySelector('input[name="payment_method"]:checked').closest('.payment-method').classList.add('selected');

// Calculate shipping on page load
document.addEventListener('DOMContentLoaded', function() {
    const subtotal = <?php echo $subtotal; ?>;
    
    fetch('index.php?page=api/order/calculate-shipping', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            subtotal: subtotal
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const shippingFee = data.shipping_fee;
            document.getElementById('shipping-fee').textContent = shippingFee > 0 ? 
                number_format(shippingFee) + '₫' : 'Miễn phí';
            updateTotal();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('shipping-fee').textContent = '50,000₫';
    });
});

// Hàm format số
function number_format(number) {
    return new Intl.NumberFormat('vi-VN').format(number);
}
</script> 