<?php
// Kiểm tra nếu không có sản phẩm nào trong giỏ hàng
if (empty($cartItems)) {
    ?>
    <section class="empty-cart-section py-5">
        <div class="container">
            <div class="text-center">
                <div class="empty-cart-icon mb-4">
                    <i class="fas fa-shopping-cart" style="font-size: 4rem; color: #ddd;"></i>
                </div>
                <h3 class="mb-3">Giỏ hàng trống</h3>
                <p class="text-muted mb-4">Bạn chưa có sản phẩm nào trong giỏ hàng</p>
                <a href="index.php?page=products" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-bag me-2"></i>Tiếp tục mua sắm
                </a>
            </div>
        </div>
    </section>
    <?php
    return;
}
?>

<style>
.cart-section {
    background: #f8f9fa;
    min-height: 100vh;
}

.cart-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
}

.cart-header {
    background: #f8f9fa;
    padding: 20px 30px;
    border-bottom: 1px solid #e9ecef;
}

.cart-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #222;
    margin: 0;
}

.cart-item {
    padding: 20px 30px;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s ease;
}

.cart-item:hover {
    background-color: #f8f9fa;
}

.cart-item:last-child {
    border-bottom: none;
}

.product-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.product-info h5 {
    font-size: 1rem;
    font-weight: 600;
    color: #222;
    margin-bottom: 5px;
    line-height: 1.3;
}

.product-options {
    font-size: 0.85rem;
    color: #666;
    margin-bottom: 8px;
}

.product-price {
    font-size: 1.1rem;
    font-weight: 700;
    color: #e74c3c;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

.quantity-btn {
    width: 32px;
    height: 32px;
    border: 1px solid #ddd;
    background: white;
    color: #333;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.quantity-btn:hover {
    background: #f8f9fa;
    border-color: #333;
}

.quantity-input {
    width: 50px;
    height: 32px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-weight: 600;
}

.remove-btn {
    color: #dc3545;
    background: none;
    border: none;
    font-size: 1.1rem;
    cursor: pointer;
    transition: color 0.2s ease;
}

.remove-btn:hover {
    color: #c82333;
}

.cart-summary {
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

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    font-size: 0.95rem;
}

.summary-row.total {
    font-size: 1.2rem;
    font-weight: 700;
    color: #e74c3c;
    border-top: 1px solid #e9ecef;
    padding-top: 15px;
    margin-top: 15px;
}

.checkout-btn {
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

.checkout-btn:hover {
    background: #c0392b;
}

.continue-shopping {
    text-align: center;
    margin-top: 20px;
}

.continue-shopping a {
    color: #666;
    text-decoration: none;
    font-size: 0.9rem;
}

.continue-shopping a:hover {
    color: #333;
}

@media (max-width: 768px) {
    .cart-header {
        padding: 15px 20px;
    }
    
    .cart-item {
        padding: 15px 20px;
    }
    
    .product-image {
        width: 60px;
        height: 60px;
    }
    
    .cart-summary {
        margin-top: 20px;
        padding: 20px;
    }
}
</style>

<section class="cart-section py-5">
    <div class="container">
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8 mb-4">
                <div class="cart-container">
                    <div class="cart-header">
                        <h2 class="cart-title">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Giỏ hàng (<?php echo $cartCount; ?> sản phẩm)
                        </h2>
                    </div>
                    
                    <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item" data-cart-id="<?php echo $item['id']; ?>">
                        <div class="row align-items-center">
                            <!-- Product Image -->
                            <div class="col-md-2 col-3">
                                <img src="<?php echo $item['image_'] ?: 'assets/uploads/product-default.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                     class="product-image">
                            </div>
                            
                            <!-- Product Info -->
                            <div class="col-md-4 col-6">
                                <div class="product-info">
                                    <h5><?php echo htmlspecialchars($item['product_name']); ?></h5>
                                    <?php if (!empty($item['selected_options_array'])): ?>
                                    <div class="product-options">
                                        <?php foreach ($item['selected_options_array'] as $optionName => $optionValue): ?>
                                        <div><strong><?php echo htmlspecialchars($optionName); ?>:</strong> <?php echo htmlspecialchars($optionValue); ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Price -->
                            <div class="col-md-2 col-3 text-center">
                                <div class="product-price">
                                    <?php echo number_format($item['current_price']); ?>₫
                                </div>
                            </div>
                            
                            <!-- Quantity -->
                            <div class="col-md-2 col-6">
                                <div class="quantity-controls">
                                    <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, -1)">-</button>
                                    <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" 
                                           min="1" max="99" onchange="updateQuantity(<?php echo $item['id']; ?>, this.value, true)">
                                    <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, 1)">+</button>
                                </div>
                            </div>
                            
                            <!-- Total Price -->
                            <div class="col-md-1 col-3 text-center">
                                <div class="product-price">
                                    <?php echo number_format($item['total_price']); ?>₫
                                </div>
                            </div>
                            
                            <!-- Remove Button -->
                            <div class="col-md-1 col-3 text-center">
                                <button class="remove-btn" onclick="removeFromCart(<?php echo $item['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Cart Summary -->
            <div class="col-lg-4">
                <div class="cart-summary">
                    <h3 class="summary-title">Tổng đơn hàng</h3>
                    
                    <div class="summary-row">
                        <span>Tạm tính:</span>
                        <span><?php echo number_format($cartTotal); ?>₫</span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Phí vận chuyển:</span>
                        <span id="shipping-fee">Đang tính...</span>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Tổng cộng:</span>
                        <span id="total-amount"><?php echo number_format($cartTotal); ?>₫</span>
                    </div>
                    
                    <button class="checkout-btn" onclick="proceedToCheckout()">
                        <i class="fas fa-credit-card me-2"></i>
                        Tiến hành thanh toán
                    </button>
                    
                    <div class="continue-shopping">
                        <a href="index.php?page=products">
                            <i class="fas fa-arrow-left me-1"></i>
                            Tiếp tục mua sắm
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Cập nhật số lượng sản phẩm
function updateQuantity(cartId, change, isDirectInput = false) {
    let quantity;
    
    if (isDirectInput) {
        quantity = parseInt(change);
    } else {
        const input = document.querySelector(`[data-cart-id="${cartId}"] .quantity-input`);
        quantity = parseInt(input.value) + parseInt(change);
    }
    
    if (quantity < 1) quantity = 1;
    if (quantity > 99) quantity = 99;
    
    // Cập nhật input
    const input = document.querySelector(`[data-cart-id="${cartId}"] .quantity-input`);
    input.value = quantity;
    
    // Gửi request cập nhật
    fetch('index.php?page=api/cart/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            cart_id: cartId,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload trang để cập nhật tổng tiền
            location.reload();
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi cập nhật số lượng');
    });
}

// Xóa sản phẩm khỏi giỏ hàng
function removeFromCart(cartId) {
    if (!confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
        return;
    }
    
    fetch('index.php?page=api/cart/remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            cart_id: cartId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Xóa item khỏi DOM
            const cartItem = document.querySelector(`[data-cart-id="${cartId}"]`);
            cartItem.remove();
            
            // Cập nhật cart count trong header
            const cartCountElement = document.querySelector('.cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = data.cart_count;
            }
            
            // Reload trang nếu giỏ hàng trống
            if (data.cart_count == 0) {
                location.reload();
            } else {
                // Cập nhật tổng tiền
                location.reload();
            }
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
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
            const total = subtotal + shippingFee;
            
            document.getElementById('shipping-fee').textContent = shippingFee > 0 ? 
                number_format(shippingFee) + '₫' : 'Miễn phí';
            document.getElementById('total-amount').textContent = number_format(total) + '₫';
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