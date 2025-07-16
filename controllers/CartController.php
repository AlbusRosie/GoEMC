<?php
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Product.php';

class CartController {
    private $cart;
    private $product;
    private $conn;
    
    public function __construct($conn) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->conn = $conn;
        $this->cart = new Cart($conn);
        $this->product = new Product($conn);
    }

    // Thêm sản phẩm vào giỏ hàng
    public function addToCart() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $productId = (int)($input['product_id'] ?? 0);
        $quantity = (int)($input['quantity'] ?? 1);
        $selectedOptions = $input['selected_options'] ?? null;
        if (!$productId || $quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }
        $userId = $_SESSION['user_id'] ?? null;
        $sessionId = session_id();
        if (!$this->cart->checkStock($productId, $quantity)) {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không đủ số lượng']);
            return;
        }
        $result = $this->cart->addToCart($productId, $quantity, $selectedOptions, $userId, $sessionId);
        if ($result) {
            $cartCount = $this->cart->getCartCount($userId, $sessionId);
            echo json_encode([
                'success' => true,
                'message' => 'Đã thêm sản phẩm vào giỏ hàng',
                'cart_count' => $cartCount
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi thêm vào giỏ hàng']);
        }
    }

    // Cập nhật số lượng sản phẩm trong giỏ
    public function updateCart() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $orderDetailId = (int)($input['order_detail_id'] ?? 0);
        $quantity = (int)($input['quantity'] ?? 1);
        if (!$orderDetailId || $quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }
        $result = $this->cart->updateCartItem($orderDetailId, $quantity);
        if ($result) {
            $userId = $_SESSION['user_id'] ?? null;
            $sessionId = session_id();
            $cartTotal = $this->cart->getCartTotal($userId, $sessionId);
            echo json_encode([
                'success' => true,
                'message' => 'Đã cập nhật giỏ hàng',
                'cart_total' => $cartTotal
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    // Xóa sản phẩm khỏi giỏ hàng
    public function removeFromCart() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $orderDetailId = (int)($input['order_detail_id'] ?? 0);
        if (!$orderDetailId) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }
        $result = $this->cart->removeFromCart($orderDetailId);
        if ($result) {
            $userId = $_SESSION['user_id'] ?? null;
            $sessionId = session_id();
            $cartCount = $this->cart->getCartCount($userId, $sessionId);
            $cartTotal = $this->cart->getCartTotal($userId, $sessionId);
            echo json_encode([
                'success' => true,
                'message' => 'Đã xóa sản phẩm khỏi giỏ hàng',
                'cart_count' => $cartCount,
                'cart_total' => $cartTotal
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    // Lấy thông tin giỏ hàng
    public function getCart() {
        $userId = $_SESSION['user_id'] ?? null;
        $sessionId = session_id();
        $cartItems = $this->cart->getCart($userId, $sessionId);
        $cartTotal = $this->cart->getCartTotal($userId, $sessionId);
        $cartCount = $this->cart->getCartCount($userId, $sessionId);
        echo json_encode([
            'success' => true,
            'cart_items' => $cartItems,
            'cart_total' => $cartTotal,
            'cart_count' => $cartCount
        ]);
    }

    // Đặt hàng (checkout): chỉ đổi trạng thái đơn hàng, không xóa order_details
    public function checkout() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        $userId = $_SESSION['user_id'] ?? null;
        $sessionId = session_id();
        $input = json_decode(file_get_contents('php://input'), true);
        $data = [
            'payment_status' => $input['payment_status'] ?? 'pending',
            'total' => $this->cart->getCartTotal($userId, $sessionId),
            'subtotal' => $this->cart->getCartTotal($userId, $sessionId),
            'guest_name' => $input['guest_name'] ?? null,
            'guest_email' => $input['guest_email'] ?? null,
            'guest_phone' => $input['guest_phone'] ?? null,
            'delivery_address' => $input['delivery_address'] ?? null,
            'notes' => $input['notes'] ?? null
        ];
        $result = $this->cart->checkout($userId, $sessionId, 'confirmed', $data);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Đặt hàng thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi khi đặt hàng']);
        }
    }
} 