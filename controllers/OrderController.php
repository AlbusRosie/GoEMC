<?php
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Product.php';

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../phpmailer/Exception.php';
require_once __DIR__ . '/../phpmailer/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/SMTP.php';

class OrderController {
    private $order;
    private $cart;
    private $product;
    
    public function __construct($conn) {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Start output buffering to prevent headers already sent error
        if (!ob_get_level()) {
            ob_start();
        }
        
        $this->order = new Order($conn);
        $this->cart = new Cart($conn);
        $this->product = new Product($conn);
    }
    
    /**
     * Tạo đơn hàng mới
     */
    public function createOrder() {
        // Đảm bảo không có output trước khi gửi JSON
        if (ob_get_length()) ob_clean();
        
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
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
                return;
            }
        }
        
        $userId = $_SESSION['user_id'] ?? null;
        $sessionId = session_id();
        
        // Lấy giỏ hàng
        $cartItems = $this->cart->getCart($userId, $sessionId);
        if (empty($cartItems)) {
            header('Content-Type: application/json');
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
            'status' => 'confirmed',
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
            
            // Gửi email thông báo
            $this->sendOrderNotification($orderId, $orderData, $orderItems);
            
            // Clear any output buffer and send JSON response
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Đặt hàng thành công',
                'order_id' => $orderId
            ]);
        } else {
            
            // Clear any output buffer and send JSON response
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
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
        
        // Cho phép xem đơn hàng nếu là guest order (user_id = null) hoặc user đã đăng nhập
        // Guest orders (user_id = null) có thể được xem bởi bất kỳ ai
        // User orders chỉ có thể được xem bởi chính user đó
        if ($order['user_id'] && $order['user_id'] != $userId) {
            header('Location: index.php');
            exit;
        }
        
        // Lấy chi tiết đơn hàng
        $orderDetails = $this->order->getOrderDetails($orderId);
        
        // Hiển thị view order detail
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
        // Đảm bảo không có output trước khi gửi JSON
        if (ob_get_length()) ob_clean();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $couponCode = $input['coupon_code'] ?? '';
        $subtotal = (float)($input['subtotal'] ?? 0);
        
        if (empty($couponCode)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã giảm giá']);
            return;
        }
        
        $result = $this->order->applyCoupon($couponCode, $subtotal);
        header('Content-Type: application/json');
        echo json_encode($result);
    }
    
    /**
     * Tính phí vận chuyển
     */
    public function calculateShipping() {
        // Đảm bảo không có output trước khi gửi JSON
        if (ob_get_length()) ob_clean();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $subtotal = (float)($input['subtotal'] ?? 0);
        $deliveryCity = $input['delivery_city'] ?? null;
        
        $shippingFee = $this->order->calculateShippingFee($subtotal, $deliveryCity);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'shipping_fee' => $shippingFee
        ]);
    }
    
    /**
     * Hủy đơn hàng
     */
    public function cancelOrder() {
        // Đảm bảo không có output trước khi gửi JSON
        if (ob_get_length()) ob_clean();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $orderId = (int)($input['order_id'] ?? 0);
        
        if (!$orderId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }
        
        // Kiểm tra quyền hủy đơn hàng
        $userId = $_SESSION['user_id'] ?? null;
        $order = $this->order->getById($orderId);
        
        if (!$order || ($order['user_id'] && $order['user_id'] != $userId)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Không có quyền hủy đơn hàng này']);
            return;
        }
        
        // Chỉ cho phép hủy đơn hàng ở trạng thái pending
        if ($order['status'] !== 'pending') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Không thể hủy đơn hàng ở trạng thái này']);
            return;
        }
        
        if ($this->order->updateOrderStatus($orderId, 'cancelled', 'Khách hàng hủy đơn hàng')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Đã hủy đơn hàng']);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }
    
    // Kiểm tra trạng thái thanh toán
    public function checkPayment() {
        // Đảm bảo không có output trước khi gửi JSON
        if (ob_get_length()) ob_clean();
        
        $input = json_decode(file_get_contents('php://input'), true);
        $orderId = $input['order_id'] ?? null;
        
        if (!$orderId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Thiếu mã đơn hàng']);
            return;
        }
        
        $order = $this->order->getById($orderId);
        if (!$order) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']);
            return;
        }
        
        // Kiểm tra trạng thái thanh toán
        $paymentStatus = $order['payment_status'];
        $orderStatus = $order['status'];
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'payment_status' => $paymentStatus,
            'order_status' => $orderStatus,
            'is_paid' => $paymentStatus === 'paid',
            'message' => $this->getPaymentStatusMessage($paymentStatus)
        ]);
    }
    
    // Cập nhật trạng thái thanh toán (cho admin)
    public function updatePaymentStatus() {
        // Đảm bảo không có output trước khi gửi JSON
        if (ob_get_length()) ob_clean();
        
        $input = json_decode(file_get_contents('php://input'), true);
        $orderId = $input['order_id'] ?? null;
        $paymentStatus = $input['payment_status'] ?? null;
        
        if (!$orderId || !$paymentStatus) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
            return;
        }
        
        $result = $this->order->updatePaymentStatus($orderId, $paymentStatus);
        if ($result) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thành công']);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại']);
        }
    }
    
    // Lấy thông báo trạng thái thanh toán
    private function getPaymentStatusMessage($status) {
        switch ($status) {
            case 'pending':
                return 'Chờ thanh toán';
            case 'paid':
                return 'Đã thanh toán';
            case 'failed':
                return 'Thanh toán thất bại';
            case 'cancelled':
                return 'Đã hủy';
            default:
                return 'Không xác định';
        }
    }
    
    /**
     * Gửi email thông báo đơn hàng mới
     */
    private function sendOrderNotification($orderId, $orderData, $orderItems) {
        try {
            $mail = new PHPMailer(true);
            
            // Cấu hình SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'nthoaithuong.forwork@gmail.com';
            $mail->Password = 'tmdl sgxg fsgx ozjq'; // Thay bằng mật khẩu ứng dụng Gmail
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            
            // Cấu hình người gửi
            $mail->setFrom('nthoaithuong.forwork@gmail.com', 'EMCWood Furniture');
            
            // Gửi email thông báo cho admin
            $mail->clearAddresses();
            $mail->addAddress('nthoaithuong.forwork@gmail.com', 'EMCWood');
            $mail->isHTML(true);
            $mail->Subject = 'Đơn hàng mới #' . $orderId . ' - MOHO Furniture';
            
            // Tạo nội dung email cho admin
            $adminBody = $this->generateAdminEmailBody($orderId, $orderData, $orderItems);
            $mail->Body = $adminBody;
            $mail->AltBody = $this->generatePlainTextBody($orderId, $orderData, $orderItems);
            
            $mail->send();
            
            // Gửi email xác nhận cho khách hàng
            $mail->clearAddresses();
            $mail->addAddress($orderData['guest_email'], $orderData['guest_name']);
            $mail->Subject = 'Xác nhận đơn hàng #' . $orderId . ' - MOHO Furniture';
            
            // Tạo nội dung email cho khách hàng
            $customerBody = $this->generateCustomerEmailBody($orderId, $orderData, $orderItems);
            $mail->Body = $customerBody;
            
            $mail->send();
            
        } catch (Exception $e) {
            // Không làm gì nếu gửi email thất bại để không ảnh hưởng đến quá trình đặt hàng
        }
    }
    
    /**
     * Tạo nội dung email cho admin
     */
    private function generateAdminEmailBody($orderId, $orderData, $orderItems) {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="color-scheme" content="light">
            <meta name="supported-color-schemes" content="light">
            <title>Đơn hàng mới - EMCWood</title>
            <style>
                :root {
                    color-scheme: light;
                }
                body { 
                    font-family: "Segoe UI", Arial, sans-serif; 
                    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 30%, #fafafa 70%, #f8f9fa 100%) !important;
                    color: #1a1a1a !important; 
                    margin: 0; 
                    padding: 20px;
                }
                .container { 
                    max-width: 700px; 
                    margin: 0 auto; 
                    background: #fff !important; 
                    border-radius: 18px; 
                    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
                    overflow: hidden; 
                }
                .hero-section { 
                    background: linear-gradient(135deg, #ff6b35, #ff8c42) !important; 
                    color: white !important; 
                    padding: 40px 30px; 
                    text-align: center; 
                    position: relative;
                }
                .hero-section::before {
                    content: "";
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: url("data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'><defs><pattern id=\'grain\' width=\'100\' height=\'100\' patternUnits=\'userSpaceOnUse\'><circle cx=\'50\' cy=\'50\' r=\'0.3\' fill=\'%23fff\' opacity=\'0.1\'/></pattern></defs><rect width=\'100\' height=\'100\' fill=\'url(%23grain)\'/></svg>");
                    pointer-events: none;
                }
                .hero-content {
                    position: relative;
                    z-index: 2;
                }
                .hero-badge { 
                    display: inline-block; 
                    background: rgba(255,255,255,0.2) !important; 
                    color: white !important; 
                    padding: 8px 16px; 
                    border-radius: 4px; 
                    font-size: 0.8rem; 
                    font-weight: 600; 
                    margin-bottom: 20px; 
                    text-transform: uppercase; 
                    letter-spacing: 2px; 
                    border-left: 3px solid rgba(255,255,255,0.8);
                }
                .hero-title { 
                    font-size: 2.5rem; 
                    font-weight: 700; 
                    margin: 0 0 10px 0; 
                    letter-spacing: 1px; 
                    color: white !important;
                }
                .hero-subtitle { 
                    font-size: 1.2rem; 
                    font-weight: 400; 
                    margin: 0 0 15px 0; 
                    opacity: 0.9;
                    color: white !important;
                }
                .hero-order-id {
                    font-size: 1.1rem;
                    background: rgba(255,255,255,0.2) !important;
                    padding: 10px 20px;
                    border-radius: 8px;
                    display: inline-block;
                    color: white !important;
                }
                .content { 
                    padding: 40px 30px; 
                    background: #fff !important;
                }
                .section { 
                    margin-bottom: 35px; 
                }
                .section-title { 
                    font-size: 1.3rem; 
                    font-weight: 600; 
                    color: #ff6b35 !important; 
                    margin-bottom: 20px; 
                    letter-spacing: 1px;
                }
                .info-card {
                    background: #f8f9fa !important;
                    border-radius: 12px;
                    padding: 20px;
                    border: 1px solid #f0f0f0;
                    margin-bottom: 15px;
                }
                .info-table { 
                    width: 100%; 
                    border-collapse: collapse; 
                }
                .info-table td { 
                    padding: 8px 0; 
                    vertical-align: top; 
                }
                .info-table .label { 
                    color: #666 !important; 
                    width: 140px; 
                    font-weight: 500; 
                }
                .info-table .value { 
                    color: #1a1a1a !important; 
                    font-weight: 500; 
                }
                .order-items { 
                    border: 1px solid #f0f0f0; 
                    border-radius: 12px; 
                    overflow: hidden; 
                    background: #f8f9fa !important;
                }
                .order-item { 
                    border-bottom: 1px solid #f0f0f0; 
                    padding: 15px 20px; 
                    background: white !important;
                    margin: 8px;
                    border-radius: 8px;
                }
                .order-item:last-child { 
                    border-bottom: none; 
                }
                .order-item-name { 
                    font-weight: 600; 
                    color: #1a1a1a !important; 
                    font-size: 1.1rem;
                    margin-bottom: 5px;
                }
                .order-item-options { 
                    color: #666 !important; 
                    font-size: 0.95rem; 
                    margin-bottom: 5px; 
                }
                .order-item-qty { 
                    color: #444 !important; 
                    margin-bottom: 5px;
                }
                .order-item-price { 
                    color: #ff6b35 !important; 
                    font-weight: 600; 
                    font-size: 1.1rem;
                }
                
                /* Thiết kế mới cho khung tổng thanh toán */
                .summary-card {
                    background: #2c3e50 !important;
                    border: 2px solid #34495e !important;
                    border-radius: 8px !important;
                    padding: 20px !important;
                    margin-top: 15px !important;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
                }
                
                .summary-table { 
                    width: 100% !important; 
                    border-collapse: collapse !important;
                }
                
                .summary-table td { 
                    padding: 10px 0 !important; 
                    border-bottom: 1px solid #34495e !important;
                }
                
                .summary-table tr:last-child td {
                    border-bottom: none !important;
                }
                
                .summary-table .label { 
                    color: #ecf0f1 !important; 
                    font-weight: 500 !important;
                }
                
                .summary-table .value { 
                    color: #ffffff !important; 
                    font-weight: 600 !important; 
                    text-align: right !important; 
                }
                
                .summary-table .total { 
                    color: #ff6b35 !important; 
                    font-size: 1.2em !important; 
                    font-weight: 700 !important; 
                }

                .alert-card { 
                    background: linear-gradient(135deg, #fff3cd, #ffeaa7) !important; 
                    color: #856404 !important; 
                    border-radius: 12px; 
                    padding: 25px; 
                    margin: 30px 0 0 0; 
                    text-align: center; 
                    font-size: 1.1em;
                    border: 1px solid #ffeaa7;
                    box-shadow: 0 5px 25px rgba(0,0,0,0.08);
                }
                .alert-card .alert-title {
                    font-size: 1.2rem;
                    font-weight: 600;
                    margin-bottom: 15px;
                    display: block;
                    color: #856404 !important;
                }
                .footer { 
                    background: #f8f9fa !important; 
                    color: #666 !important; 
                    text-align: center; 
                    font-size: 0.95em; 
                    padding: 25px 0 20px 0; 
                    border-top: 1px solid #eee; 
                }
                .footer-brand {
                    color: #ff6b35 !important;
                    font-weight: 600;
                    font-size: 1.1rem;
                }
                @media (max-width: 600px) { 
                    .container, .content { padding: 15px !important; } 
                    .hero-title { font-size: 2rem; }
                }
                @media (prefers-color-scheme: dark) {
                    body, .container, .content, .info-card, .order-items, .order-item, .footer {
                        background: inherit !important;
                        color: inherit !important;
                    }

                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="hero-section">
                    <div class="hero-content">
                        <div class="hero-badge">Đơn hàng mới</div>
                        <h1 class="hero-title">EMCWood Furniture</h1>
                        <div class="hero-subtitle">Thương hiệu nội thất gỗ cao cấp</div>
                        <div class="hero-order-id">
                            Mã đơn hàng: <b>#' . $orderId . '</b>
                        </div>
                    </div>
                </div>
                <div class="content">
                    <div class="section">
                        <div class="section-title">Thông tin đơn hàng</div>
                        <div class="info-card">
                            <table class="info-table">
                                <tr><td class="label">Ngày đặt:</td><td class="value">' . date('d/m/Y H:i:s') . '</td></tr>
                                <tr><td class="label">Thanh toán:</td><td class="value">' . ($orderData['payment_method'] == 'cash' ? 'Tiền mặt khi nhận hàng' : $orderData['payment_method']) . '</td></tr>
                                <tr><td class="label">Trạng thái:</td><td class="value" style="color:#ff6b35;font-weight:700;">Đã xác nhận</td></tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="section">
                        <div class="section-title">Thông tin khách hàng</div>
                        <div class="info-card">
                            <table class="info-table">
                                <tr><td class="label">Họ tên:</td><td class="value">' . htmlspecialchars($orderData['guest_name']) . '</td></tr>
                                <tr><td class="label">Email:</td><td class="value">' . htmlspecialchars($orderData['guest_email']) . '</td></tr>
                                <tr><td class="label">Điện thoại:</td><td class="value">' . htmlspecialchars($orderData['guest_phone']) . '</td></tr>
                                <tr><td class="label">Địa chỉ:</td><td class="value">' . htmlspecialchars($orderData['delivery_address']) . '</td></tr>
                                ' . ($orderData['delivery_city'] ? '<tr><td class="label">Tỉnh/Thành:</td><td class="value">' . htmlspecialchars($orderData['delivery_city']) . '</td></tr>' : '') . '
                                ' . ($orderData['delivery_notes'] ? '<tr><td class="label">Ghi chú:</td><td class="value">' . htmlspecialchars($orderData['delivery_notes']) . '</td></tr>' : '') . '
                            </table>
                        </div>
                    </div>
                    
                    <div class="section">
                        <div class="section-title">Sản phẩm đã đặt</div>
                        <div class="order-items">';
        foreach ($orderItems as $item) {
            $html .= '<div class="order-item">
                <div class="order-item-name">' . htmlspecialchars($item['product_name']) . '</div>';
            if (!empty($item['selected_options'])) {
                $html .= '<div class="order-item-options">Tùy chọn: ' . htmlspecialchars(implode(', ', $item['selected_options'])) . '</div>';
            }
            $html .= '<div class="order-item-qty">Số lượng: <b>' . $item['quantity'] . '</b></div>';
            $html .= '<div class="order-item-price">' . number_format($item['price']) . '₫</div>';
            $html .= '</div>';
        }
        $html .= '</div></div>
                    
                    <div class="section">
                        <div class="section-title">Tổng thanh toán</div>
                        <div class="summary-card">
                            <table class="summary-table">
                                <tr><td class="label">Tạm tính:</td><td class="value">' . number_format($orderData['subtotal']) . '₫</td></tr>
                                <tr><td class="label">Phí vận chuyển:</td><td class="value">' . number_format($orderData['shipping_fee']) . '₫</td></tr>';
        if ($orderData['discount_amount'] > 0) {
            $html .= '<tr><td class="label">Giảm giá:</td><td class="value">-' . number_format($orderData['discount_amount']) . '₫</td></tr>';
        }
        $html .= '<tr><td class="label total" style="color:#ff6b35 !important;">Tổng cộng:</td><td class="value total" style="color:#ff6b35 !important;">' . number_format($orderData['total']) . '₫</td></tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="alert-card">
                        <div class="alert-title">Hành động cần thiết</div>
                        <div>Vui lòng liên hệ với khách hàng sớm nhất để xác nhận và xử lý đơn hàng!</div>
                        <div style="margin-top:15px;font-weight:500;">
                            Email: <b>' . htmlspecialchars($orderData['guest_email']) . '</b><br>
                            Điện thoại: <b>' . htmlspecialchars($orderData['guest_phone']) . '</b>
                        </div>
                    </div>
                </div>
                <div class="footer">
                    <div class="footer-brand">EMCWood Furniture</div>
                    © ' . date('Y') . ' - Đơn hàng mới từ website<br>
                    <span style="color:#ff6b35;font-weight:600;">Hotline: 090-123-4567</span>
                </div>
            </div>
        </body>
        </html>';
        return $html;
    }

    private function generateCustomerEmailBody($orderId, $orderData, $orderItems) {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="color-scheme" content="light only">
            <meta name="supported-color-schemes" content="light only">
            <meta name="forced-colors" content="none">
            <meta name="prefers-color-scheme" content="light">
            <title>Xác nhận đơn hàng - EMCWood</title>
            <style>
                /* Force light mode for all email clients */
                :root {
                    color-scheme: light only !important;
                    forced-color-adjust: none !important;
                }
                
                /* Override any dark mode preferences */
                html[data-color-scheme="dark"] *,
                html[data-theme="dark"] *,
                [data-color-scheme="dark"] *,
                [data-theme="dark"] * {
                    color-scheme: light only !important;
                    forced-color-adjust: none !important;
                }
                
                body { 
                    font-family: "Segoe UI", Arial, sans-serif !important; 
                    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 30%, #fafafa 70%, #f8f9fa 100%) !important;
                    color: #1a1a1a !important; 
                    margin: 0 !important; 
                    padding: 20px !important;
                    color-scheme: light only !important;
                }
                
                .container { 
                    max-width: 700px !important; 
                    margin: 0 auto !important; 
                    background: #ffffff !important; 
                    border-radius: 18px !important; 
                    box-shadow: 0 20px 60px rgba(0,0,0,0.15) !important;
                    overflow: hidden !important; 
                    color-scheme: light only !important;
                }
                
                .hero-section { 
                    background: linear-gradient(135deg, #ff6b35, #ff8c42) !important; 
                    color: #ffffff !important; 
                    padding: 40px 30px !important; 
                    text-align: center !important; 
                    position: relative !important;
                }
                
                .hero-section::before {
                    content: "" !important;
                    position: absolute !important;
                    top: 0 !important;
                    left: 0 !important;
                    right: 0 !important;
                    bottom: 0 !important;
                    background: url("data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'><defs><pattern id=\'grain\' width=\'100\' height=\'100\' patternUnits=\'userSpaceOnUse\'><circle cx=\'50\' cy=\'50\' r=\'0.3\' fill=\'%23fff\' opacity=\'0.1\'/></pattern></defs><rect width=\'100\' height=\'100\' fill=\'url(%23grain)\'/></svg>") !important;
                    pointer-events: none !important;
                }
                
                .hero-content {
                    position: relative !important;
                    z-index: 2 !important;
                }
                
                .hero-badge { 
                    display: inline-block !important; 
                    background: rgba(255,255,255,0.2) !important; 
                    color: #ffffff !important; 
                    padding: 8px 16px !important; 
                    border-radius: 4px !important; 
                    font-size: 0.8rem !important; 
                    font-weight: 600 !important; 
                    margin-bottom: 20px !important; 
                    text-transform: uppercase !important; 
                    letter-spacing: 2px !important; 
                    border-left: 3px solid rgba(255,255,255,0.8) !important;
                }
                
                .hero-title { 
                    font-size: 2.5rem !important; 
                    font-weight: 700 !important; 
                    margin: 0 0 10px 0 !important; 
                    letter-spacing: 1px !important; 
                    color: #ffffff !important;
                }
                
                .hero-subtitle { 
                    font-size: 1.2rem !important; 
                    font-weight: 400 !important; 
                    margin: 0 0 15px 0 !important; 
                    opacity: 0.9 !important;
                    color: #ffffff !important;
                }
                
                .hero-order-id {
                    font-size: 1.1rem !important;
                    background: rgba(255,255,255,0.2) !important;
                    padding: 10px 20px !important;
                    border-radius: 8px !important;
                    display: inline-block !important;
                    color: #ffffff !important;
                }
                
                .content { 
                    padding: 40px 30px !important; 
                    background: #ffffff !important;
                    color: #1a1a1a !important;
                }
                
                .section { 
                    margin-bottom: 35px !important; 
                }
                
                .section-title { 
                    font-size: 1.3rem !important; 
                    font-weight: 600 !important; 
                    color: #ff6b35 !important; 
                    margin-bottom: 20px !important; 
                    letter-spacing: 1px !important;
                }
                
                .info-card {
                    background: #f8f9fa !important;
                    border-radius: 12px !important;
                    padding: 20px !important;
                    border: 1px solid #f0f0f0 !important;
                    margin-bottom: 15px !important;
                    color: #1a1a1a !important;
                }
                
                .info-table { 
                    width: 100% !important; 
                    border-collapse: collapse !important; 
                }
                
                .info-table td { 
                    padding: 8px 0 !important; 
                    vertical-align: top !important; 
                }
                
                .info-table .label { 
                    color: #666666 !important; 
                    width: 140px !important; 
                    font-weight: 500 !important; 
                }
                
                .info-table .value { 
                    color: #1a1a1a !important; 
                    font-weight: 500 !important; 
                }
                
                .order-items { 
                    border: 1px solid #f0f0f0 !important; 
                    border-radius: 12px !important; 
                    overflow: hidden !important; 
                    background: #f8f9fa !important;
                }
                
                .order-item { 
                    border-bottom: 1px solid #f0f0f0 !important; 
                    padding: 15px 20px !important; 
                    background: #ffffff !important;
                    margin: 8px !important;
                    border-radius: 8px !important;
                    color: #1a1a1a !important;
                }
                
                .order-item:last-child { 
                    border-bottom: none !important; 
                }
                
                .order-item-name { 
                    font-weight: 600 !important; 
                    color: #1a1a1a !important; 
                    font-size: 1.1rem !important;
                    margin-bottom: 5px !important;
                }
                
                .order-item-options { 
                    color: #666666 !important; 
                    font-size: 0.95rem !important; 
                    margin-bottom: 5px !important; 
                }
                
                .order-item-qty { 
                    color: #444444 !important; 
                    margin-bottom: 5px !important;
                }
                
                .order-item-price { 
                    color: #ff6b35 !important; 
                    font-weight: 600 !important; 
                    font-size: 1.1rem !important;
                }
                
                /* Thiết kế mới cho khung tổng thanh toán */
                .summary-card {
                    background:rgb(255, 255, 255) !important;
                    border: 2px solidrgb(49, 49, 49) !important;
                    border-radius: 8px !important;
                    padding: 20px !important;
                    margin-top: 15px !important;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
                }
                
                .summary-table { 
                    width: 100% !important; 
                    border-collapse: collapse !important;
                }
                
                .summary-table td { 
                    padding: 10px 0 !important; 
                    border-bottom: 1px solid #34495e !important;
                }
                
                .summary-table tr:last-child td {
                    border-bottom: none !important;
                }
                
                .summary-table .label { 
                    color:rgb(0, 0, 0) !important; 
                    font-weight: 500 !important;
                }
                
                .summary-table .value { 
                    color:rgb(0, 0, 0) !important; 
                    font-weight: 600 !important; 
                    text-align: right !important; 
                }
                
                .summary-table .total { 
                    color: #ff6b35 !important; 
                    font-size: 1.2em !important; 
                    font-weight: 700 !important; 
                }
                

                
                .thank-card { 
                    background: linear-gradient(135deg, #d4edda, #c3e6cb) !important; 
                    color: #155724 !important; 
                    border-radius: 12px !important; 
                    padding: 25px !important; 
                    margin: 30px 0 !important; 
                    text-align: center !important; 
                    font-size: 1.1em !important;
                    border: 1px solid #c3e6cb !important;
                    box-shadow: 0 5px 25px rgba(0,0,0,0.08) !important;
                }
                
                .thank-card .thank-title {
                    font-size: 1.2rem !important;
                    font-weight: 600 !important;
                    margin-bottom: 15px !important;
                    display: block !important;
                    color: #155724 !important;
                }
                
                .footer { 
                    background: #f8f9fa !important; 
                    color: #666666 !important; 
                    text-align: center !important; 
                    font-size: 0.95em !important; 
                    padding: 25px 0 20px 0 !important; 
                    border-top: 1px solid #eeeeee !important; 
                }
                
                .footer-brand {
                    color: #ff6b35 !important;
                    font-weight: 600 !important;
                    font-size: 1.1rem !important;
                }
                
                @media (max-width: 600px) { 
                    .container, .content { padding: 15px !important; } 
                    .hero-title { font-size: 2rem !important; }
                }
                
                /* Disable dark mode completely */
                @media (prefers-color-scheme: dark) {
                    body, .container, .content, .info-card, .order-items, .order-item, .footer {
                        background: inherit !important;
                        color: inherit !important;
                    }
                    /* Đảm bảo khung tổng thanh toán luôn hiển thị đúng */
                    .summary-card {
                        background: #2c3e50 !important;
                        border-color: #34495e !important;
                    }
                    .summary-table .label {
                        color: #ecf0f1 !important;
                    }
                    .summary-table .value {
                        color: #ffffff !important;
                    }
                    .summary-table .total {
                        color: #ff6b35 !important;
                    }
                }
                
                /* Force light colors for all elements */
                * {
                    color-scheme: light only !important;
                    forced-color-adjust: none !important;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="hero-section">
                    <div class="hero-content">
                        <div class="hero-badge">Đơn hàng xác nhận</div>
                        <h1 class="hero-title">EMCWood Furniture</h1>
                        <div class="hero-subtitle">Thương hiệu nội thất gỗ cao cấp</div>
                        <div class="hero-order-id">
                            Mã đơn hàng: <b>#' . $orderId . '</b>
                        </div>
                    </div>
                </div>
                <div class="content">
                    <div class="thank-card">
                        <div class="thank-title">Đơn hàng đã được tiếp nhận</div>
                        <div style="color:#155724 !important;">Chúng tôi đã nhận được đơn hàng của bạn và đang tiến hành xử lý.</div>
                        <div style="margin-top:15px;color:#155724 !important;font-weight:500 !important;">
                            Nhân viên sẽ liên hệ với bạn trong thời gian sớm nhất để xác nhận chi tiết và sắp xếp giao hàng.
                        </div>
                    </div>
                    
                    <div class="section">
                        <div class="section-title">Chi tiết đơn hàng</div>
                        <div class="info-card">
                            <table class="info-table">
                                <tr><td class="label">Ngày đặt:</td><td class="value">' . date('d/m/Y H:i:s') . '</td></tr>
                                <tr><td class="label">Thanh toán:</td><td class="value">' . ($orderData['payment_method'] == 'cash' ? 'Tiền mặt khi nhận hàng' : $orderData['payment_method']) . '</td></tr>
                                <tr><td class="label">Trạng thái:</td><td class="value" style="color:#28a745 !important;font-weight:700 !important;">Đã xác nhận</td></tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="section">
                        <div class="section-title">Sản phẩm đã đặt</div>
                        <div class="order-items">';
        foreach ($orderItems as $item) {
            $html .= '<div class="order-item">
                <div class="order-item-name">' . htmlspecialchars($item['product_name']) . '</div>';
            if (!empty($item['selected_options'])) {
                $html .= '<div class="order-item-options">Tùy chọn: ' . htmlspecialchars(implode(', ', $item['selected_options'])) . '</div>';
            }
            $html .= '<div class="order-item-qty">Số lượng: <b>' . $item['quantity'] . '</b></div>';
            $html .= '<div class="order-item-price">' . number_format($item['price']) . '₫</div>';
            $html .= '</div>';
        }
        $html .= '</div></div>
                    
                    <div class="section">
                        <div class="section-title">Tổng thanh toán</div>
                        <div class="summary-card">
                            <table class="summary-table">
                                <tr><td class="label">Tạm tính:</td><td class="value">' . number_format($orderData['subtotal']) . '₫</td></tr>
                                <tr><td class="label">Phí vận chuyển:</td><td class="value">' . number_format($orderData['shipping_fee']) . '₫</td></tr>';
        if ($orderData['discount_amount'] > 0) {
            $html .= '<tr><td class="label">Giảm giá:</td><td class="value">-' . number_format($orderData['discount_amount']) . '₫</td></tr>';
        }
        $html .= '<tr><td class="label total" style="color:#ff6b35 !important;">Tổng cộng:</td><td class="value total" style="color:#ff6b35 !important;">' . number_format($orderData['total']) . '₫</td></tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="section">
                        <div class="section-title">Thông tin liên hệ</div>
                        <div class="info-card">
                            <table class="info-table">
                                <tr><td class="label">Họ tên:</td><td class="value">' . htmlspecialchars($orderData['guest_name']) . '</td></tr>
                                <tr><td class="label">Email:</td><td class="value" style="color:#1a1a1a !important;">' . htmlspecialchars($orderData['guest_email']) . '</td></tr>
                                <tr><td class="label">Điện thoại:</td><td class="value">' . htmlspecialchars($orderData['guest_phone']) . '</td></tr>
                                <tr><td class="label">Địa chỉ:</td><td class="value">' . htmlspecialchars($orderData['delivery_address']) . '</td></tr>
                                ' . ($orderData['delivery_city'] ? '<tr><td class="label">Tỉnh/Thành:</td><td class="value">' . htmlspecialchars($orderData['delivery_city']) . '</td></tr>' : '') . '
                            </table>
                        </div>
                    </div>
                </div>
                <div class="footer">
                    <div class="footer-brand">EMCWood Furniture</div>
                    <div style="color:#666666 !important;">© ' . date('Y') . ' - Cảm ơn bạn đã tin tưởng chúng tôi</div>
                    <div style="color:#ff6b35 !important;font-weight:600 !important;margin-top:10px;">Hotline: 090-123-4567</div>
                </div>
            </div>
        </body>
        </html>';
        return $html;
    }
    
    /**
     * Tạo nội dung text đơn giản
     */
    private function generatePlainTextBody($orderId, $orderData, $orderItems) {
        $text = "ĐƠN HÀNG MỚI #" . $orderId . "\n\n";
        $text .= "Thông tin khách hàng:\n";
        $text .= "Tên: " . $orderData['guest_name'] . "\n";
        $text .= "Email: " . $orderData['guest_email'] . "\n";
        $text .= "Điện thoại: " . $orderData['guest_phone'] . "\n";
        $text .= "Địa chỉ: " . $orderData['delivery_address'] . "\n\n";
        
        $text .= "Sản phẩm:\n";
        foreach ($orderItems as $item) {
            $text .= "- " . $item['product_name'] . " (SL: " . $item['quantity'] . ", Giá: " . number_format($item['price']) . "₫)\n";
        }
        
        $text .= "\nTổng cộng: " . number_format($orderData['total']) . "₫\n";
        $text .= "\nVui lòng liên hệ với khách hàng sớm nhất!";
        
        return $text;
    }
}
?> 