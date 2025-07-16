<?php
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/ProductOption.php';

class CartController extends BaseController {
    private $cart;
    private $product;
    private $productOptionModel;
    public function __construct($conn) {
        parent::__construct();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->cart = new Cart($conn);
        $this->product = new Product($conn);
        $this->productOptionModel = new ProductOption($conn);
    }
    // Thêm sản phẩm vào giỏ hàng
    public function addToCart() {
        header('Content-Type: application/json');
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
        // Kiểm tra tồn kho option nếu có
        if ($selectedOptions && is_array($selectedOptions)) {
            $productOptions = $this->productOptionModel->getByProductId($productId);
            foreach ($selectedOptions as $optionName => $optionValue) {
                foreach ($productOptions as $opt) {
                    if ($opt['name'] == $optionName) {
                        $stock = $this->productOptionModel->getOptionValueStock($opt['id'], $optionValue);
                        if ($stock < $quantity) {
                            echo json_encode(['success' => false, 'message' => "Không đủ số lượng cho lựa chọn $optionValue"]);
                            return;
                        }
                    }
                }
            }
        } else {
            if (!$this->cart->checkStock($productId, $quantity)) {
                echo json_encode(['success' => false, 'message' => 'Sản phẩm không đủ số lượng']);
                return;
            }
        }
        $userId = $_SESSION['user_id'] ?? null;
        $sessionId = session_id();
        $result = $this->cart->addToCart($productId, $quantity, $selectedOptions, $userId, $sessionId);
        if ($result) {
            $cartCount = $this->cart->getCartCount($userId, $sessionId);
            echo json_encode([
                'success' => true,
                'message' => 'Đã thêm sản phẩm vào giỏ hàng!',
                'cart_count' => $cartCount
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi thêm vào giỏ hàng']);
        }
    }
    // Lấy giỏ hàng
    public function getCart() {
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'] ?? null;
        $sessionId = session_id();
        $cartItems = $this->cart->getCart($userId, $sessionId);
        $cartCount = $this->cart->getCartCount($userId, $sessionId);
        echo json_encode([
            'success' => true,
            'cart_items' => $cartItems,
            'cart_count' => $cartCount
        ]);
    }
    // Xóa sản phẩm khỏi giỏ
    public function removeFromCart() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $orderDetailId = (int)($input['order_detail_id'] ?? 0);
        if (!$orderDetailId) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }
        $result = $this->cart->removeFromCart($orderDetailId);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Đã xóa sản phẩm khỏi giỏ hàng']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }
    // Cập nhật số lượng
    public function updateCart() {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $orderDetailId = (int)($input['order_detail_id'] ?? 0);
        $quantity = (int)($input['quantity'] ?? 1);
        if (!$orderDetailId || $quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }
        $result = $this->cart->updateCartItem($orderDetailId, $quantity);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Đã cập nhật giỏ hàng']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    public function showCart() {
        $userId = $_SESSION['user_id'] ?? null;
        $sessionId = session_id();
        $cartItems = $this->cart->getCart($userId, $sessionId); // Phải trả về đầy đủ thông tin sản phẩm
        $cartTotal = 0;
        $cartCount = 0;
        foreach ($cartItems as $item) {
            $cartTotal += $item['price'] * $item['quantity'];
            $cartCount += $item['quantity'];
        }
        $this->render('cart', [
            'cartItems' => $cartItems,
            'cartTotal' => $cartTotal,
            'cartCount' => $cartCount
        ]);
    }
} 