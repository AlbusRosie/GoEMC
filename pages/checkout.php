<?php
// Đảm bảo session được start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Cart.php';

// Khởi tạo kết nối database
$db = new Database();
$conn = $db->getConnection();

// Khởi tạo model
$cartModel = new Cart($conn);

// Lấy dữ liệu giỏ hàng
$userId = $_SESSION['user_id'] ?? null;
$sessionId = session_id();
$cartItems = $cartModel->getCart($userId, $sessionId);
$subtotal = $cartModel->getCartTotal($userId, $sessionId);



// Kiểm tra giỏ hàng có sản phẩm không
if (empty($cartItems)) {
    header('Location: index.php?page=cart');
    exit;
}

// Kiểm tra xem có phải guest user không
$isGuest = !isset($_SESSION['user_id']);
?>

<?php if ($isGuest): ?>
<div class="guest-notice" style="background: linear-gradient(135deg, #ff6b35, #ff8c42); color: white; padding: 1rem; text-align: center; margin-bottom: 1rem;">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Khách hàng:</strong> Vui lòng nhập thông tin cá nhân để hoàn tất đơn hàng. Bạn không cần đăng ký tài khoản.
</div>
<?php endif; ?>

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

.form-label.required::after {
    content: " *";
    color: #dc3545;
}

.form-control {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 12px 15px;
    font-size: 0.95rem;
    transition: border-color 0.2s ease;
}

.form-control:focus {
    border-color: #ff6b35;
    box-shadow: 0 0 0 0.2rem rgba(255, 107, 53, 0.25);
}

.form-control.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #dc3545;
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
    border-color: #ff6b35;
}

.payment-method.selected {
    border-color: #ff6b35;
    background-color: #fff5f2;
}

/* Payment Info Styles */
.payment-info {
    margin-top: 20px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.payment-detail {
    margin-bottom: 20px;
}

.bank-accounts {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.bank-account {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.bank-logo {
    width: 50px;
    height: 50px;
    background: #f8f9fa;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e9ecef;
}

.bank-logo i {
    font-size: 1.5rem;
}

.bank-details {
    flex: 1;
}

.bank-details h6 {
    margin: 0 0 10px 0;
    color: #222;
    font-weight: 600;
}

.bank-details p {
    margin: 5px 0;
    font-size: 0.9rem;
    color: #666;
}

.bank-actions {
    display: flex;
    gap: 10px;
}

.payment-note {
    margin-top: 15px;
}

.momo-payment {
    display: flex;
    gap: 20px;
    align-items: flex-start;
}

.momo-qr {
    flex: 0 0 200px;
}

.qr-placeholder {
    width: 200px;
    height: 200px;
    background: white;
    border: 2px dashed #e9ecef;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #666;
}

.momo-details {
    flex: 1;
}

.momo-details h6 {
    margin: 0 0 15px 0;
    color: #222;
    font-weight: 600;
}

.momo-details p {
    margin: 8px 0;
    font-size: 0.9rem;
    color: #666;
}

@media (max-width: 768px) {
    .bank-account {
        flex-direction: column;
        text-align: center;
    }
    
    .momo-payment {
        flex-direction: column;
        align-items: center;
    }
    
    .momo-qr {
        flex: none;
    }
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
    background: #ff6b35;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s ease;
}

.place-order-btn:hover {
    background: #e55a2b;
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

.progress-steps {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
    padding: 0 20px;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    position: relative;
}

.step:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 15px;
    left: 50%;
    width: 100%;
    height: 2px;
    background: #e9ecef;
    z-index: 1;
}

.step.active:not(:last-child)::after {
    background: #ff6b35;
}

.step-number {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e9ecef;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9rem;
    margin-bottom: 8px;
    position: relative;
    z-index: 2;
}

.step.active .step-number {
    background: #ff6b35;
    color: white;
}

.step.completed .step-number {
    background: #28a745;
    color: white;
}

.step-label {
    font-size: 0.8rem;
    color: #666;
    text-align: center;
    font-weight: 500;
}

.step.active .step-label {
    color: #ff6b35;
    font-weight: 600;
}

.step.completed .step-label {
    color: #28a745;
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
    
    .progress-steps {
        padding: 0 10px;
    }
    
    .step-label {
        font-size: 0.7rem;
    }
}
</style>

<section class="checkout-section py-5">
    <div class="container">
        <!-- Progress Steps -->
        <div class="progress-steps">
            <div class="step completed">
                <div class="step-number">1</div>
                <div class="step-label">Giỏ hàng</div>
            </div>
            <div class="step active">
                <div class="step-number">2</div>
                <div class="step-label">Thông tin</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-label">Thanh toán</div>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-label">Hoàn tất</div>
            </div>
        </div>

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
                                    <label class="form-label required">Họ và tên</label>
                                    <input type="text" class="form-control" name="guest_name" id="guest_name" required>
                                    <div class="invalid-feedback">Vui lòng nhập họ và tên</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label required">Số điện thoại</label>
                                    <input type="tel" class="form-control" name="guest_phone" id="guest_phone" required>
                                    <div class="invalid-feedback">Vui lòng nhập số điện thoại hợp lệ</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Email</label>
                            <input type="email" class="form-control" name="guest_email" id="guest_email" required>
                            <div class="invalid-feedback">Vui lòng nhập email hợp lệ</div>
                        </div>
                        
                        <!-- Địa chỉ giao hàng -->
                        <div class="section-title">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Địa chỉ giao hàng
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">Địa chỉ chi tiết</label>
                            <textarea class="form-control" name="delivery_address" id="delivery_address" rows="3" required placeholder="Số nhà, tên đường, phường/xã..."></textarea>
                            <div class="invalid-feedback">Vui lòng nhập địa chỉ chi tiết</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label required">Tỉnh/Thành phố</label>
                                    <select class="form-select" name="delivery_city" id="delivery_city" required>
                                        <option value="">Chọn tỉnh/thành phố</option>
                                        <option value="An Giang">An Giang</option>
                                        <option value="Bà Rịa - Vũng Tàu">Bà Rịa - Vũng Tàu</option>
                                        <option value="Bắc Giang">Bắc Giang</option>
                                        <option value="Bắc Kạn">Bắc Kạn</option>
                                        <option value="Bạc Liêu">Bạc Liêu</option>
                                        <option value="Bắc Ninh">Bắc Ninh</option>
                                        <option value="Bến Tre">Bến Tre</option>
                                        <option value="Bình Định">Bình Định</option>
                                        <option value="Bình Dương">Bình Dương</option>
                                        <option value="Bình Phước">Bình Phước</option>
                                        <option value="Bình Thuận">Bình Thuận</option>
                                        <option value="Cà Mau">Cà Mau</option>
                                        <option value="Cần Thơ">Cần Thơ</option>
                                        <option value="Cao Bằng">Cao Bằng</option>
                                        <option value="Đà Nẵng">Đà Nẵng</option>
                                        <option value="Đắk Lắk">Đắk Lắk</option>
                                        <option value="Đắk Nông">Đắk Nông</option>
                                        <option value="Điện Biên">Điện Biên</option>
                                        <option value="Đồng Nai">Đồng Nai</option>
                                        <option value="Đồng Tháp">Đồng Tháp</option>
                                        <option value="Gia Lai">Gia Lai</option>
                                        <option value="Hà Giang">Hà Giang</option>
                                        <option value="Hà Nam">Hà Nam</option>
                                        <option value="Hà Nội">Hà Nội</option>
                                        <option value="Hà Tĩnh">Hà Tĩnh</option>
                                        <option value="Hải Dương">Hải Dương</option>
                                        <option value="Hải Phòng">Hải Phòng</option>
                                        <option value="Hậu Giang">Hậu Giang</option>
                                        <option value="Hòa Bình">Hòa Bình</option>
                                        <option value="Hưng Yên">Hưng Yên</option>
                                        <option value="Khánh Hòa">Khánh Hòa</option>
                                        <option value="Kiên Giang">Kiên Giang</option>
                                        <option value="Kon Tum">Kon Tum</option>
                                        <option value="Lai Châu">Lai Châu</option>
                                        <option value="Lâm Đồng">Lâm Đồng</option>
                                        <option value="Lạng Sơn">Lạng Sơn</option>
                                        <option value="Lào Cai">Lào Cai</option>
                                        <option value="Long An">Long An</option>
                                        <option value="Nam Định">Nam Định</option>
                                        <option value="Nghệ An">Nghệ An</option>
                                        <option value="Ninh Bình">Ninh Bình</option>
                                        <option value="Ninh Thuận">Ninh Thuận</option>
                                        <option value="Phú Thọ">Phú Thọ</option>
                                        <option value="Phú Yên">Phú Yên</option>
                                        <option value="Quảng Bình">Quảng Bình</option>
                                        <option value="Quảng Nam">Quảng Nam</option>
                                        <option value="Quảng Ngãi">Quảng Ngãi</option>
                                        <option value="Quảng Ninh">Quảng Ninh</option>
                                        <option value="Quảng Trị">Quảng Trị</option>
                                        <option value="Sóc Trăng">Sóc Trăng</option>
                                        <option value="Sơn La">Sơn La</option>
                                        <option value="Tây Ninh">Tây Ninh</option>
                                        <option value="Thái Bình">Thái Bình</option>
                                        <option value="Thái Nguyên">Thái Nguyên</option>
                                        <option value="Thanh Hóa">Thanh Hóa</option>
                                        <option value="Thừa Thiên Huế">Thừa Thiên Huế</option>
                                        <option value="Tiền Giang">Tiền Giang</option>
                                        <option value="TP.HCM">TP.HCM</option>
                                        <option value="Trà Vinh">Trà Vinh</option>
                                        <option value="Tuyên Quang">Tuyên Quang</option>
                                        <option value="Vĩnh Long">Vĩnh Long</option>
                                        <option value="Vĩnh Phúc">Vĩnh Phúc</option>
                                        <option value="Yên Bái">Yên Bái</option>
                                    </select>
                                    <div class="invalid-feedback">Vui lòng chọn tỉnh/thành phố</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Quận/Huyện</label>
                                    <select class="form-select" name="delivery_district" id="delivery_district" required>
                                        <option value="">Chọn quận/huyện</option>
                                    </select>
                                    <div class="invalid-feedback">Vui lòng chọn quận/huyện</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Phường/Xã</label>
                                    <select class="form-select" name="delivery_ward" id="delivery_ward" required>
                                        <option value="">Chọn phường/xã</option>
                                    </select>
                                    <div class="invalid-feedback">Vui lòng chọn phường/xã</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Ghi chú giao hàng</label>
                            <textarea class="form-control" name="delivery_notes" id="delivery_notes" rows="2" placeholder="Hướng dẫn giao hàng, thời gian nhận hàng..."></textarea>
                        </div>
                        
                        <!-- Thông tin bổ sung -->
                        <div class="section-title">
                            <i class="fas fa-info-circle me-2"></i>
                            Thông tin bổ sung
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Công ty (nếu có)</label>
                                    <input type="text" class="form-control" name="company_name" id="company_name" placeholder="Tên công ty">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Mã số thuế</label>
                                    <input type="text" class="form-control" name="tax_code" id="tax_code" placeholder="Mã số thuế (nếu có)">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Phương thức thanh toán -->
                        <div class="section-title">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            Phương thức thanh toán
                        </div>
                        
                        <div class="payment-methods">
                            <label class="payment-method">
                                <input type="radio" name="payment_method" value="cash" checked>
                                <i class="fas fa-money-bill-wave text-success"></i>
                                <div>Tiền mặt</div>
                                <small>Thanh toán khi nhận hàng</small>
                            </label>
                        </div>
                        
                        <!-- Thông tin thanh toán -->
                        <div class="payment-info">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                <strong>Lưu ý:</strong> Bạn sẽ thanh toán bằng tiền mặt khi nhận hàng. Vui lòng chuẩn bị đủ tiền để thanh toán.
                            </div>
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
                            <textarea class="form-control" name="notes" id="notes" rows="3" placeholder="Ghi chú thêm về đơn hàng..."></textarea>
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
                            <?php if (!empty($item['product_image'])): ?>
                                <img src="<?php echo htmlspecialchars($item['product_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                     class="cart-item-image">
                            <?php else: ?>
                                <img src="assets/uploads/product-default.jpg" 
                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                     class="cart-item-image">
                            <?php endif; ?>
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
                                    <?php echo number_format($item['price']); ?>₫ x <?php echo $item['quantity']; ?>
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

// Validation functions
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    // Loại bỏ tất cả ký tự không phải số
    const cleanPhone = phone.replace(/\D/g, '');
    return cleanPhone.length >= 10 && cleanPhone.length <= 11;
}

function validateField(field, validationFn, errorMessage) {
    const value = field.value.trim();
    const isValid = validationFn ? validationFn(value) : value.length > 0;
    
    
    
    if (!isValid) {
        field.classList.add('is-invalid');
        return false;
    } else {
        field.classList.remove('is-invalid');
        return true;
    }
}

// Real-time validation
document.getElementById('guest_name').addEventListener('blur', function() {
    validateField(this, null, 'Vui lòng nhập họ và tên');
});

document.getElementById('guest_email').addEventListener('blur', function() {
    validateField(this, validateEmail, 'Vui lòng nhập email hợp lệ');
});

document.getElementById('guest_phone').addEventListener('blur', function() {
    validateField(this, validatePhone, 'Vui lòng nhập số điện thoại hợp lệ');
});

document.getElementById('delivery_address').addEventListener('blur', function() {
    validateField(this, null, 'Vui lòng nhập địa chỉ chi tiết');
});

document.getElementById('delivery_city').addEventListener('change', function() {
    validateField(this, null, 'Vui lòng chọn tỉnh/thành phố');
});

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
        // Silent error handling
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
    
    // Cập nhật nội dung thanh toán nếu đang hiển thị
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    if (paymentMethod === 'cash') {
        // updatePaymentContent(); // Không cần gọi nữa vì không có xử lý thanh toán khác
    }
}

// Đặt hàng
function placeOrder() {
    const form = document.getElementById('checkout-form');
    
    // Validate all required fields
    const requiredFields = [
        { field: 'guest_name', validation: null },
        { field: 'guest_email', validation: validateEmail },
        { field: 'guest_phone', validation: validatePhone },
        { field: 'delivery_address', validation: null },
        { field: 'delivery_city', validation: null }
    ];
    
    let isValid = true;
    
    requiredFields.forEach(({ field, validation }) => {
        const element = document.getElementById(field);
        if (element && !validateField(element, validation)) {
            isValid = false;
        }
    });
    
    if (!isValid) {
        alert('Vui lòng điền đầy đủ và chính xác thông tin bắt buộc');
        return;
    }
    
    const formData = new FormData(form);
    
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
        company_name: formData.get('company_name'),
        tax_code: formData.get('tax_code'),
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
    .then(response => {
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const paymentMethod = formData.get('payment_method');
            const orderId = data.order_id;
            
            if (paymentMethod === 'cash') {
                alert('Đặt hàng thành công! Mã đơn hàng: ' + orderId + '\nVui lòng chuẩn bị tiền mặt khi nhận hàng.');
                window.location.href = `index.php?page=order&id=${orderId}`;
            } else {
                alert('Phương thức thanh toán không hợp lệ.');
            }
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        alert('Có lỗi xảy ra khi đặt hàng');
    })
    .finally(() => {
        orderBtn.disabled = false;
        orderBtn.innerHTML = '<i class="fas fa-shopping-cart me-2"></i>Đặt hàng ngay';
    });
}



// Không còn xử lý gì cho các phương thức thanh toán khác ngoài 'cash'.
// Nếu cần, có thể giữ lại dòng này để luôn chọn mặc định phương thức cash:
document.querySelector('input[name="payment_method"]').checked = true;

// Hàm format số
function number_format(number) {
    return new Intl.NumberFormat('vi-VN').format(number);
}

// Dữ liệu địa chỉ Việt Nam
const vietnamAddresses = {
    "TP.HCM": {
        districts: {
        "Quận 1": ["Phường Bến Nghé", "Phường Bến Thành", "Phường Cầu Kho", "Phường Cầu Ông Lãnh", "Phường Cô Giang", "Phường Đa Kao", "Phường Nguyễn Cư Trinh", "Phường Nguyễn Thái Bình", "Phường Phạm Ngũ Lão", "Phường Tân Định"],
        "Quận 2": ["Phường An Khánh", "Phường An Lợi Đông", "Phường An Phú", "Phường Bình An", "Phường Bình Khánh", "Phường Bình Trưng Đông", "Phường Bình Trưng Tây", "Phường Cát Lái", "Phường Thạnh Mỹ Lợi", "Phường Thảo Điền", "Phường Thủ Thiêm"],
        "Quận 3": ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14"],
        "Quận 4": ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 8", "Phường 9", "Phường 10", "Phường 12", "Phường 13", "Phường 14", "Phường 15", "Phường 16", "Phường 18"],
        "Quận 5": ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15"],
        "Quận 6": ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14"],
        "Quận 7": ["Phường Bình Thuận", "Phường Phú Mỹ", "Phường Phú Thuận", "Phường Tân Hưng", "Phường Tân Kiểng", "Phường Tân Phong", "Phường Tân Phú", "Phường Tân Quy", "Phường Tân Thuận Đông", "Phường Tân Thuận Tây"],
        "Quận 8": ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15", "Phường 16"],
        "Quận 9": ["Phường Hiệp Phú", "Phường Long Bình", "Phường Long Phước", "Phường Long Thạnh Mỹ", "Phường Long Trường", "Phường Phú Hữu", "Phường Phước Bình", "Phường Phước Long A", "Phường Phước Long B", "Phường Tăng Nhơn Phú A", "Phường Tăng Nhơn Phú B", "Phường Tân Phú", "Phường Trường Thạnh"],
        "Quận 10": ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15"],
        "Quận 11": ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15", "Phường 16"],
            "Quận 12": ["Phường An Phú Đông", "Phường Đông Hưng Thuận", "Phường Hiệp Thành", "Phường Tân Chánh Hiệp", "Phường Tân Hưng Thuận", "Phường Tân Thới Hiệp", "Phường Tân Thới Nhất", "Phường Thạnh Lộc", "Phường Thạnh Xuân", "Phường Thới An", "Phường Trung Mỹ Tây"],
            "Quận Bình Tân": ["Phường An Lạc", "Phường An Lạc A", "Phường Bình Hưng Hòa", "Phường Bình Hưng Hòa A", "Phường Bình Hưng Hòa B", "Phường Bình Trị Đông", "Phường Bình Trị Đông A", "Phường Bình Trị Đông B", "Phường Tân Tạo", "Phường Tân Tạo A"],
            "Quận Bình Thạnh": ["Phường 1", "Phường 2", "Phường 3", "Phường 5", "Phường 6", "Phường 7", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15", "Phường 17", "Phường 19", "Phường 21", "Phường 22", "Phường 24", "Phường 25", "Phường 26", "Phường 27", "Phường 28"],
            "Quận Gò Vấp": ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15", "Phường 16", "Phường 17"],
            "Quận Phú Nhuận": ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15", "Phường 17"],
            "Quận Tân Bình": ["Phường 1", "Phường 2", "Phường 3", "Phường 4", "Phường 5", "Phường 6", "Phường 7", "Phường 8", "Phường 9", "Phường 10", "Phường 11", "Phường 12", "Phường 13", "Phường 14", "Phường 15"],
            "Quận Tân Phú": ["Phường Hiệp Tân", "Phường Hòa Thạnh", "Phường Phú Thạnh", "Phường Phú Thọ Hòa", "Phường Phú Trung", "Phường Sơn Kỳ", "Phường Tân Quý", "Phường Tân Sơn Nhì", "Phường Tân Thành", "Phường Tân Thới Hòa", "Phường Tây Thạnh"],
            "Quận Thủ Đức": ["Phường An Khánh", "Phường An Lợi Đông", "Phường An Phú", "Phường Bình Chiểu", "Phường Bình Thọ", "Phường Hiệp Bình Chánh", "Phường Hiệp Bình Phước", "Phường Linh Chiểu", "Phường Linh Đông", "Phường Linh Tây", "Phường Linh Trung", "Phường Linh Xuân", "Phường Tam Bình", "Phường Tam Phú", "Phường Trường Thọ"]
        }
    },
    "Hà Nội": {
        districts: {
        "Quận Ba Đình": ["Phường Cống Vị", "Phường Điện Biên", "Phường Đội Cấn", "Phường Giảng Võ", "Phường Kim Mã", "Phường Liễu Giai", "Phường Ngọc Hà", "Phường Ngọc Khánh", "Phường Nguyễn Trung Trực", "Phường Phúc Xá", "Phường Quán Thánh", "Phường Thành Công", "Phường Trúc Bạch", "Phường Vĩnh Phúc"],
        "Quận Hoàn Kiếm": ["Phường Chương Dương", "Phường Cửa Đông", "Phường Cửa Nam", "Phường Đồng Xuân", "Phường Hàng Bạc", "Phường Hàng Bài", "Phường Hàng Bồ", "Phường Hàng Bông", "Phường Hàng Buồm", "Phường Hàng Đào", "Phường Hàng Gai", "Phường Hàng Mã", "Phường Hàng Trống", "Phường Lý Thái Tổ", "Phường Phan Chu Trinh", "Phường Phúc Tân", "Phường Trần Hưng Đạo", "Phường Tràng Tiền"],
        "Quận Hai Bà Trưng": ["Phường Bách Khoa", "Phường Bạch Đằng", "Phường Bạch Mai", "Phường Bùi Thị Xuân", "Phường Cầu Dền", "Phường Đống Mác", "Phường Đồng Nhân", "Phường Đồng Tâm", "Phường Lê Đại Hành", "Phường Minh Khai", "Phường Ngô Thì Nhậm", "Phường Nguyễn Du", "Phường Phạm Đình Hổ", "Phường Phố Huế", "Phường Quỳnh Lôi", "Phường Quỳnh Mai", "Phường Thanh Lương", "Phường Thanh Nhàn", "Phường Trương Định", "Phường Vĩnh Tuy"],
            "Quận Đống Đa": ["Phường Cát Linh", "Phường Hàng Bột", "Phường Khâm Thiên", "Phường Khương Thượng", "Phường Kim Liên", "Phường Láng Hạ", "Phường Láng Thượng", "Phường Nam Đồng", "Phường Ngã Tư Sở", "Phường Ô Chợ Dừa", "Phường Phương Liên", "Phường Phương Mai", "Phường Quang Trung", "Phường Quốc Tử Giám", "Phường Thịnh Quang", "Phường Thổ Quan", "Phường Trung Liệt", "Phường Trung Phụng", "Phường Trung Tự", "Phường Văn Chương", "Phường Văn Miếu"],
        "Quận Tây Hồ": ["Phường Bưởi", "Phường Nhật Tân", "Phường Phú Thượng", "Phường Quảng An", "Phường Thụy Khuê", "Phường Tứ Liên", "Phường Xuân La", "Phường Yên Phụ"],
        "Quận Cầu Giấy": ["Phường Dịch Vọng", "Phường Dịch Vọng Hậu", "Phường Mai Dịch", "Phường Nghĩa Đô", "Phường Nghĩa Tân", "Phường Quan Hoa", "Phường Trung Hòa", "Phường Yên Hòa"],
        "Quận Thanh Xuân": ["Phường Đại Kim", "Phường Định Công", "Phường Giáp Bát", "Phường Hoàng Liệt", "Phường Hoàng Văn Thụ", "Phường Lĩnh Nam", "Phường Mai Động", "Phường Tân Mai", "Phường Thanh Trì", "Phường Thịnh Liệt", "Phường Trần Phú", "Phường Tương Mai", "Phường Vĩnh Hưng", "Phường Yên Sở"],
        "Quận Hoàng Mai": ["Phường Đại Kim", "Phường Định Công", "Phường Giáp Bát", "Phường Hoàng Liệt", "Phường Hoàng Văn Thụ", "Phường Lĩnh Nam", "Phường Mai Động", "Phường Tân Mai", "Phường Thanh Trì", "Phường Thịnh Liệt", "Phường Trần Phú", "Phường Tương Mai", "Phường Vĩnh Hưng", "Phường Yên Sở"],
        "Quận Long Biên": ["Phường Bồ Đề", "Phường Cự Khối", "Phường Đức Giang", "Phường Gia Thụy", "Phường Giang Biên", "Phường Long Biên", "Phường Ngọc Lâm", "Phường Ngọc Thụy", "Phường Phúc Đồng", "Phường Phúc Lợi", "Phường Sài Đồng", "Phường Thạch Bàn", "Phường Thượng Thanh", "Phường Việt Hưng"],
            "Quận Nam Từ Liêm": ["Phường Cầu Diễn", "Phường Đại Mỗ", "Phường Mễ Trì", "Phường Mỹ Đình 1", "Phường Mỹ Đình 2", "Phường Phú Đô", "Phường Phương Canh", "Phường Tây Mỗ", "Phường Trung Văn", "Phường Xuân Phương"],
            "Quận Bắc Từ Liêm": ["Phường Cổ Nhuế 1", "Phường Cổ Nhuế 2", "Phường Đông Ngạc", "Phường Đức Thắng", "Phường Liên Mạc", "Phường Minh Khai", "Phường Phú Diễn", "Phường Phúc Diễn", "Phường Tây Tựu", "Phường Thượng Cát", "Phường Thụy Phương", "Phường Xuân Đỉnh", "Phường Xuân Tảo"],
            "Huyện Thanh Trì": ["Xã Đại Áng", "Xã Đông Mỹ", "Xã Duyên Hà", "Xã Hữu Hoà", "Xã Liên Ninh", "Xã Ngọc Hồi", "Xã Ngũ Hiệp", "Xã Tả Thanh Oai", "Xã Tam Hiệp", "Xã Tân Triều", "Xã Thanh Liệt", "Xã Tứ Hiệp", "Xã Vạn Phúc", "Xã Vĩnh Quỳnh", "Xã Yên Mỹ"],
            "Huyện Gia Lâm": ["Thị trấn Trâu Quỳ", "Xã Bát Tràng", "Xã Cổ Bi", "Xã Đa Tốn", "Xã Đặng Xá", "Xã Đình Xuyên", "Xã Đông Dư", "Xã Dương Hà", "Xã Dương Quang", "Xã Dương Xá", "Xã Kiêu Kỵ", "Xã Kim Lan", "Xã Kim Sơn", "Xã Lệ Chi", "Xã Ninh Hiệp", "Xã Phù Đổng", "Xã Phú Thị", "Xã Trung Mầu", "Xã Văn Đức", "Xã Yên Thường", "Xã Yên Viên"],
            "Huyện Đông Anh": ["Thị trấn Đông Anh", "Xã Bắc Hồng", "Xã Cổ Loa", "Xã Đại Mạch", "Xã Đông Hội", "Xã Dục Tú", "Xã Hải Bối", "Xã Kim Chung", "Xã Kim Nỗ", "Xã Liên Hà", "Xã Mai Lâm", "Xã Nam Hồng", "Xã Nguyên Khê", "Xã Tầm Xá", "Xã Thụy Lâm", "Xã Tiên Dương", "Xã Uy Nỗ", "Xã Vân Hà", "Xã Vân Nội", "Xã Việt Hùng", "Xã Vĩnh Ngọc", "Xã Võng La", "Xã Xuân Canh", "Xã Xuân Nộn"],
            "Huyện Sóc Sơn": ["Thị trấn Sóc Sơn", "Xã Bắc Phú", "Xã Đông Xuân", "Xã Đức Hoà", "Xã Hiền Ninh", "Xã Hồng Kỳ", "Xã Kim Lũ", "Xã Mai Đình", "Xã Minh Phú", "Xã Minh Trí", "Xã Nam Sơn", "Xã Phú Cường", "Xã Phú Linh", "Xã Phú Lương", "Xã Phù Linh", "Xã Phù Lỗ", "Xã Phú Minh", "Xã Quang Tiến", "Xã Tân Dân", "Xã Tân Hưng", "Xã Tân Minh", "Xã Thanh Xuân", "Xã Tiên Dược", "Xã Trung Giã", "Xã Việt Long", "Xã Xuân Giang", "Xã Xuân Thu"],
            "Huyện Ba Vì": ["Thị trấn Tây Đằng", "Xã Ba Trại", "Xã Ba Vì", "Xã Cẩm Lĩnh", "Xã Cam Thượng", "Xã Châu Sơn", "Xã Chu Minh", "Xã Cổ Đô", "Xã Đông Quang", "Xã Đồng Thái", "Xã Khánh Thượng", "Xã Minh Châu", "Xã Minh Quang", "Xã Phong Vân", "Xã Phú Châu", "Xã Phú Cường", "Xã Phú Đông", "Xã Phú Phương", "Xã Phú Sơn", "Xã Sơn Đà", "Xã Tản Hồng", "Xã Tản Lĩnh", "Xã Thái Hòa", "Xã Thuần Mỹ", "Xã Thụy An", "Xã Tiên Phong", "Xã Tòng Bạt", "Xã Vân Hòa", "Xã Vạn Thắng", "Xã Vật Lại", "Xã Yên Bài"],
            "Huyện Phúc Thọ": ["Thị trấn Phúc Thọ", "Xã Cẩm Đình", "Xã Hát Môn", "Xã Hiệp Thuận", "Xã Liên Hiệp", "Xã Long Xuyên", "Xã Ngọc Tảo", "Xã Phúc Hòa", "Xã Phụng Thượng", "Xã Phương Độ", "Xã Sen Chiểu", "Xã Tam Hiệp", "Xã Tam Thuấn", "Xã Thanh Đa", "Xã Thọ Lộc", "Xã Thượng Cốc", "Xã Tích Giang", "Xã Trạch Mỹ Lộc", "Xã Vân Côn", "Xã Vân Hà", "Xã Vân Nam", "Xã Vân Phúc", "Xã Võng Xuyên", "Xã Xuân Phú"],
            "Huyện Đan Phượng": ["Thị trấn Phùng", "Xã Đan Phượng", "Xã Đồng Tháp", "Xã Hạ Mỗ", "Xã Hồng Hà", "Xã Liên Hà", "Xã Liên Hồng", "Xã Liên Trung", "Xã Phương Đình", "Xã Song Phượng", "Xã Tân Hội", "Xã Tân Lập", "Xã Thọ An", "Xã Thọ Xuân", "Xã Thượng Mỗ", "Xã Trung Châu"],
            "Huyện Hoài Đức": ["Thị trấn Trạm Trôi", "Xã An Khánh", "Xã An Thượng", "Xã Cát Quế", "Xã Đắc Sở", "Xã Di Trạch", "Xã Đông La", "Xã Đức Giang", "Xã Đức Thượng", "Xã Dương Liễu", "Xã Kim Chung", "Xã La Phù", "Xã Lại Yên", "Xã Minh Khai", "Xã Sơn Đồng", "Xã Song Phương", "Xã Tiền Yên", "Xã Vân Canh", "Xã Vân Côn", "Xã Yên Sở"],
            "Huyện Quốc Oai": ["Thị trấn Quốc Oai", "Xã Cấn Hữu", "Xã Cộng Hòa", "Xã Đại Thành", "Xã Đồng Quang", "Xã Đông Xuân", "Xã Đông Yên", "Xã Hòa Thạch", "Xã Liệp Tuyết", "Xã Nghĩa Hương", "Xã Ngọc Liệp", "Xã Ngọc Mỹ", "Xã Phú Cát", "Xã Phú Mãn", "Xã Phượng Cách", "Xã Sài Sơn", "Xã Tân Hòa", "Xã Tân Phú", "Xã Thạch Thán", "Xã Tuyết Nghĩa", "Xã Yên Sơn"],
            "Huyện Thạch Thất": ["Thị trấn Liên Quan", "Xã Bình Phú", "Xã Bình Yên", "Xã Cẩm Yên", "Xã Cần Kiệm", "Xã Canh Nậu", "Xã Chàng Sơn", "Xã Đại Đồng", "Xã Dị Nậu", "Xã Đồng Trúc", "Xã Hạ Bằng", "Xã Hương Ngải", "Xã Hữu Bằng", "Xã Kim Quan", "Xã Lại Thượng", "Xã Phú Kim", "Xã Phùng Xá", "Xã Tân Xã", "Xã Thạch Hoà", "Xã Thạch Xá", "Xã Tiến Xuân", "Xã Yên Bình", "Xã Yên Trung"],
            "Huyện Chương Mỹ": ["Thị trấn Chúc Sơn", "Thị trấn Xuân Mai", "Xã Đại Yên", "Xã Đồng Lạc", "Xã Đồng Phú", "Xã Đông Phương Yên", "Xã Đông Sơn", "Xã Hoà Chính", "Xã Hoàng Diệu", "Xã Hoàng Văn Thụ", "Xã Hồng Phong", "Xã Hợp Đồng", "Xã Hữu Văn", "Xã Lam Điền", "Xã Mỹ Lương", "Xã Nam Phương Tiến", "Xã Ngọc Hòa", "Xã Phú Nam An", "Xã Phú Nghĩa", "Xã Phụng Châu", "Xã Quảng Bị", "Xã Tân Tiến", "Xã Thanh Bình", "Xã Thành Công", "Xã Thượng Vực", "Xã Thụy Hương", "Xã Tốt Động", "Xã Trần Phú", "Xã Trung Hòa", "Xã Trường Yên", "Xã Văn Võ", "Xã Xuân Mai"],
            "Huyện Thanh Oai": ["Thị trấn Kim Bài", "Xã Bích Hòa", "Xã Bình Minh", "Xã Cao Dương", "Xã Cao Viên", "Xã Cự Khê", "Xã Dân Hòa", "Xã Đỗ Động", "Xã Hồng Dương", "Xã Kim An", "Xã Kim Thư", "Xã Liên Châu", "Xã Mỹ Hưng", "Xã Phương Trung", "Xã Tam Hưng", "Xã Tân Ước", "Xã Thanh Cao", "Xã Thanh Mai", "Xã Thanh Thùy", "Xã Thanh Văn", "Xã Xuân Dương"],
            "Huyện Mỹ Đức": ["Thị trấn Đại Nghĩa", "Xã An Mỹ", "Xã An Phú", "Xã An Tiến", "Xã Bột Xuyên", "Xã Đại Hưng", "Xã Đốc Tín", "Xã Đồng Tâm", "Xã Hồng Sơn", "Xã Hợp Thanh", "Xã Hợp Tiến", "Xã Hùng Tiến", "Xã Hương Sơn", "Xã Lê Thanh", "Xã Mỹ Thành", "Xã Phù Lưu Tế", "Xã Phúc Lâm", "Xã Phùng Xá", "Xã Thượng Lâm", "Xã Tuy Lai", "Xã Vạn Kim", "Xã Xuy Xá"],
            "Huyện Ứng Hòa": ["Thị trấn Vân Đình", "Xã Cao Thành", "Xã Đại Cường", "Xã Đại Hùng", "Xã Đội Bình", "Xã Đông Lỗ", "Xã Đồng Tân", "Xã Đồng Tiến", "Xã Hòa Lâm", "Xã Hòa Nam", "Xã Hòa Phú", "Xã Hoa Sơn", "Xã Hòa Xá", "Xã Hồng Quang", "Xã Kim Đường", "Xã Liên Bạt", "Xã Lưu Hoàng", "Xã Minh Đức", "Xã Phù Lưu", "Xã Phương Tú", "Xã Quảng Phú Cầu", "Xã Sơn Công", "Xã Tảo Dương Văn", "Xã Trầm Lộng", "Xã Trung Tú", "Xã Trường Thịnh", "Xã Vạn Thái", "Xã Viên An", "Xã Viên Nội"],
            "Huyện Thường Tín": ["Thị trấn Thường Tín", "Xã Chương Dương", "Xã Dũng Tiến", "Xã Duyên Thái", "Xã Hà Hồi", "Xã Hiền Giang", "Xã Hòa Bình", "Xã Hồng Vân", "Xã Khánh Hà", "Xã Lê Lợi", "Xã Liên Phương", "Xã Minh Cường", "Xã Nghiêm Xuyên", "Xã Nguyễn Trãi", "Xã Nhị Khê", "Xã Ninh Sở", "Xã Quất Động", "Xã Tân Minh", "Xã Thắng Lợi", "Xã Thống Nhất", "Xã Thư Phú", "Xã Tiền Phong", "Xã Tô Hiệu", "Xã Tự Nhiên", "Xã Văn Bình", "Xã Vạn Điểm", "Xã Văn Phú", "Xã Vân Tảo", "Xã Xuân Phú"],
            "Huyện Phú Xuyên": ["Thị trấn Phú Xuyên", "Thị trấn Phú Minh", "Xã Bạch Hạ", "Xã Châu Can", "Xã Chuyên Mỹ", "Xã Đại Thắng", "Xã Đại Xuyên", "Xã Hoàng Long", "Xã Hồng Minh", "Xã Hồng Thái", "Xã Khai Thái", "Xã Minh Tân", "Xã Nam Phong", "Xã Nam Triều", "Xã Phú Túc", "Xã Phú Yên", "Xã Phúc Tiến", "Xã Phượng Dực", "Xã Quang Lãng", "Xã Quang Trung", "Xã Sơn Hà", "Xã Tân Dân", "Xã Thụy Phú", "Xã Tri Thủy", "Xã Tri Trung", "Xã Văn Hoàng", "Xã Văn Nhân", "Xã Vân Từ"],
            "Huyện Mê Linh": ["Thị trấn Chi Đông", "Thị trấn Quang Minh", "Xã Chu Phan", "Xã Đại Thịnh", "Xã Hoàng Kim", "Xã Kim Hoa", "Xã Liên Mạc", "Xã Mê Linh", "Xã Tam Đồng", "Xã Thạch Đà", "Xã Thanh Lâm", "Xã Tiền Phong", "Xã Tiến Thắng", "Xã Tiến Thịnh", "Xã Tráng Việt", "Xã Tự Lập", "Xã Văn Khê", "Xã Vạn Yên"]
        }
    }
    // Thêm các tỉnh khác với dữ liệu mẫu
};

// Hàm cập nhật quận/huyện
function updateDistricts() {
    const citySelect = document.getElementById('delivery_city');
    const districtSelect = document.getElementById('delivery_district');
    const wardSelect = document.getElementById('delivery_ward');
    
    const selectedCity = citySelect.value;
    
    // Reset district và ward
    districtSelect.innerHTML = '<option value="">Chọn quận/huyện</option>';
    wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
    
    if (selectedCity && vietnamAddresses[selectedCity]) {
        const districts = Object.keys(vietnamAddresses[selectedCity].districts);
        districts.forEach(district => {
            const option = document.createElement('option');
            option.value = district;
            option.textContent = district;
            districtSelect.appendChild(option);
        });
    }
}

// Hàm cập nhật phường/xã
function updateWards() {
    const citySelect = document.getElementById('delivery_city');
    const districtSelect = document.getElementById('delivery_district');
    const wardSelect = document.getElementById('delivery_ward');
    
    const selectedCity = citySelect.value;
    const selectedDistrict = districtSelect.value;
    
    // Reset ward
    wardSelect.innerHTML = '<option value="">Chọn phường/xã</option>';
    
    if (selectedCity && selectedDistrict && vietnamAddresses[selectedCity] && vietnamAddresses[selectedCity].districts[selectedDistrict]) {
        const wards = vietnamAddresses[selectedCity].districts[selectedDistrict];
        wards.forEach(ward => {
            const option = document.createElement('option');
            option.value = ward;
            option.textContent = ward;
            wardSelect.appendChild(option);
        });
    }
}

// Thêm event listeners
document.getElementById('delivery_city').addEventListener('change', function() {
    updateDistricts();
    // Tính phí vận chuyển
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
        // Silent error handling
    });
});

document.getElementById('delivery_district').addEventListener('change', updateWards);
</script> 