<?php
session_start();
require_once 'config/database.php';
require_once 'models/Cart.php';
require_once 'models/Product.php';

// Khởi tạo kết nối database
$db = new Database();
$conn = $db->getConnection();

// Khởi tạo model
$cart = new Cart($conn);
$product = new Product($conn);

echo "<h2>Test Cart Functionality</h2>";

// Test 1: Kiểm tra sản phẩm có options
echo "<h3>Test 1: Kiểm tra sản phẩm có options</h3>";
$testProduct = $product->getById(1);
if ($testProduct) {
    echo "Product: " . $testProduct['name'] . "<br>";
    echo "Price: " . $testProduct['price'] . "<br>";
    echo "Sale: " . ($testProduct['sale'] ?? 'No sale') . "<br>";
} else {
    echo "Product not found<br>";
}

// Test 2: Kiểm tra thêm vào giỏ hàng không có options
echo "<h3>Test 2: Thêm vào giỏ hàng không có options</h3>";
$result = $cart->addToCart(1, 2, null, null, session_id());
echo "Add to cart result: " . ($result ? 'Success' : 'Failed') . "<br>";

// Test 3: Kiểm tra thêm vào giỏ hàng có options
echo "<h3>Test 3: Thêm vào giỏ hàng có options</h3>";
$options = ['Kích thước' => '4x8 feet', 'Màu sắc' => 'Đỏ tự nhiên'];
$result = $cart->addToCart(1, 1, $options, null, session_id());
echo "Add to cart with options result: " . ($result ? 'Success' : 'Failed') . "<br>";

// Test 4: Kiểm tra giỏ hàng hiện tại
echo "<h3>Test 4: Giỏ hàng hiện tại</h3>";
$cartItems = $cart->getCart(null, session_id());
echo "Cart items count: " . count($cartItems) . "<br>";
foreach ($cartItems as $item) {
    echo "- " . $item['product_name'] . " x" . $item['quantity'] . " (Options: " . ($item['selected_options'] ?: 'None') . ")<br>";
}

// Test 5: Kiểm tra cart count
echo "<h3>Test 5: Cart count</h3>";
$cartCount = $cart->getCartCount(null, session_id());
echo "Cart count: " . $cartCount . "<br>";

// Test 6: Kiểm tra cart total
echo "<h3>Test 6: Cart total</h3>";
$cartTotal = $cart->getCartTotal(null, session_id());
echo "Cart total: " . number_format($cartTotal) . " VND<br>";

echo "<h3>Test completed!</h3>";
?> 