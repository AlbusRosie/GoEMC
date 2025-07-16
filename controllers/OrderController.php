<?php
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Product.php';

class OrderController {
    private $order;
    private $cart;
    private $product;
    
    public function __construct($conn) {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->order = new Order($conn);
        $this->cart = new Cart($conn);
        $this->product = new Product($conn);
    }
    
    /**
     * Tạo đơn hàng mới
     */
    public function createOrder() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate dữ liệu đầu vào
        $requiredFields = ['guest_name', 'guest_email', 'guest_phone', 'delivery_address'];
        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
                return;
            }
        }
        
        $userId = $_SESSION['user_id'] ?? null;
        $sessionId = session_id();
        
        // Lấy giỏ hàng
        $cartItems = $this->cart->getCart($userId, $sessionId);
        if (empty($cartItems)) {
            echo json_encode(['success' => false, 'message' => 'Giỏ hàng trống']);
            return;
        }
        
        // Tính toán giá
        $subtotal = 0;
        $orderItems = [];
        
        foreach ($cartItems as $item) {
            $subtotal += $item['total_price'];
            $orderItems[] = [
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'quantity' => $item['quantity'],
                'price' => $item['current_price'],
                'selected_options' => $item['selected_options_array']
            ];
        }
        
        // Tính phí vận chuyển
        $shippingFee = $this->order->calculateShippingFee($subtotal, $input['delivery_city'] ?? null);
        
        // Áp dụng mã giảm giá nếu có
        $discountAmount = 0;
        $couponCode = null;
        $couponDiscount = 0;
        
        if (!empty($input['coupon_code'])) {
            $couponResult = $this->order->applyCoupon($input['coupon_code'], $subtotal);
            if ($couponResult['success']) {
                $discountAmount = $couponResult['discount'];
                $couponCode = $input['coupon_code'];
                $couponDiscount = $discountAmount;
            }
        }
        
        // Tính tổng tiền
        $total = $subtotal + $shippingFee - $discountAmount;
        
        // Tạo dữ liệu đơn hàng
        $orderData = [
            'user_id' => $userId,
            'guest_name' => $input['guest_name'],
            'guest_email' => $input['guest_email'],
            'guest_phone' => $input['guest_phone'],
            'total' => $total,
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'discount_amount' => $discountAmount,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => $input['payment_method'] ?? null,
            'delivery_address' => $input['delivery_address'],
            'delivery_city' => $input['delivery_city'] ?? null,
            'delivery_district' => $input['delivery_district'] ?? null,
            'delivery_ward' => $input['delivery_ward'] ?? null,
            'delivery_notes' => $input['delivery_notes'] ?? null,
            'coupon_code' => $couponCode,
            'coupon_discount' => $couponDiscount,
            'notes' => $input['notes'] ?? null,
            'items' => $orderItems
        ];
        
        // Tạo đơn hàng
        $orderId = $this->order->createOrder($orderData);
        
        if ($orderId) {
            // Cập nhật số lượng sử dụng mã giảm giá
            if ($couponCode) {
                $this->order->updateCouponUsage($couponCode);
            }
            
            // Xóa giỏ hàng
            $this->cart->clearCart($userId, $sessionId);
            
            echo json_encode([
                'success' => true,
                'message' => 'Đặt hàng thành công',
                'order_id' => $orderId
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi tạo đơn hàng']);
        }
    }
    
    /**
     * Hiển thị trang checkout
     */
    public function showCheckout() {
        $userId = $_SESSION['user_id'] ?? null;
        $sessionId = session_id();
        
        $cartItems = $this->cart->getCart($userId, $sessionId);
        $cartTotal = $this->cart->getCartTotal($userId, $sessionId);
        
        if (empty($cartItems)) {
            header('Location: index.php?page=cart');
            exit;
        }
        
        // Tính toán giá
        $subtotal = $cartTotal;
        $shippingFee = $this->order->calculateShippingFee($subtotal);
        $total = $subtotal + $shippingFee;
        
        // Hiển thị view checkout
        include __DIR__ . '/../pages/checkout.php';
    }
    
    /**
     * Hiển thị trang đơn hàng
     */
    public function showOrder($orderId) {
        $order = $this->order->getById($orderId);
        
        if (!$order) {
            header('Location: index.php');
            exit;
        }
        
        // Kiểm tra quyền xem đơn hàng
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId || ($order['user_id'] && $order['user_id'] != $userId)) {
            header('Location: index.php');
            exit;
        }
        
        include __DIR__ . '/../pages/order-detail.php';
    }
    
    /**
     * Hiển thị danh sách đơn hàng của user
     */
    public function showUserOrders() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            header('Location: index.php?page=login');
            exit;
        }
        
        $orders = $this->order->getByUserId($userId);
        
        include __DIR__ . '/../pages/user-orders.php';
    }
    
    /**
     * Áp dụng mã giảm giá
     */
    public function applyCoupon() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $couponCode = $input['coupon_code'] ?? '';
        $subtotal = (float)($input['subtotal'] ?? 0);
        
        if (empty($couponCode)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã giảm giá']);
            return;
        }
        
        $result = $this->order->applyCoupon($couponCode, $subtotal);
        echo json_encode($result);
    }
    
    /**
     * Tính phí vận chuyển
     */
    public function calculateShipping() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $subtotal = (float)($input['subtotal'] ?? 0);
        $deliveryCity = $input['delivery_city'] ?? null;
        
        $shippingFee = $this->order->calculateShippingFee($subtotal, $deliveryCity);
        
        echo json_encode([
            'success' => true,
            'shipping_fee' => $shippingFee
        ]);
    }
    
    /**
     * Hủy đơn hàng
     */
    public function cancelOrder() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $orderId = (int)($input['order_id'] ?? 0);
        
        if (!$orderId) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }
        
        // Kiểm tra quyền hủy đơn hàng
        $userId = $_SESSION['user_id'] ?? null;
        $order = $this->order->getById($orderId);
        
        if (!$order || ($order['user_id'] && $order['user_id'] != $userId)) {
            echo json_encode(['success' => false, 'message' => 'Không có quyền hủy đơn hàng này']);
            return;
        }
        
        // Chỉ cho phép hủy đơn hàng ở trạng thái pending
        if ($order['status'] !== 'pending') {
            echo json_encode(['success' => false, 'message' => 'Không thể hủy đơn hàng ở trạng thái này']);
            return;
        }
        
        if ($this->order->updateOrderStatus($orderId, 'cancelled', 'Khách hàng hủy đơn hàng')) {
            echo json_encode(['success' => true, 'message' => 'Đã hủy đơn hàng']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }
}
?> 