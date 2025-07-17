<?php


class Cart {
    private $conn;
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Lấy đơn hàng pending hiện tại (theo user hoặc session)
    public function getPendingOrder($userId = null, $sessionId = null) {
        $sql = "SELECT * FROM orders WHERE status = 'pending' AND ";
        $params = [];
        if ($userId) {
            $sql .= "user_id = ?";
            $params[] = $userId;
        } else {
            $sql .= "guest_phone = ?";
            $params[] = $sessionId;
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tạo đơn hàng pending mới
    public function createPendingOrder($userId = null, $sessionId = null) {
        $guestPhone = $userId ? null : $sessionId;
        $sql = "INSERT INTO orders (user_id, guest_phone, total, subtotal, status, payment_status, created_at) VALUES (?, ?, 0, 0, 'pending', 'pending', NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId, $guestPhone]);
        $orderId = $this->conn->lastInsertId();
        return $this->getOrderById($orderId);
    }

    public function getOrderById($orderId) {
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Thêm sản phẩm vào giỏ
    public function addToCart($productId, $quantity, $selectedOptions = null, $userId = null, $sessionId = null) {
        // Lấy hoặc tạo đơn hàng pending
        $order = $this->getPendingOrder($userId, $sessionId);
        if (!$order) {
            $order = $this->createPendingOrder($userId, $sessionId);
        }
        $orderId = $order['id'];
        
        // Chuẩn hóa selectedOptions: sort key để so sánh chính xác
        if ($selectedOptions && is_array($selectedOptions)) {
            ksort($selectedOptions);
        }
        $optionsJson = $selectedOptions ? json_encode($selectedOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
        
        // Kiểm tra đã có sản phẩm này với options này chưa
        if ($optionsJson) {
            $sql = "SELECT * FROM order_details WHERE order_id = ? AND product_id = ? AND JSON_CONTAINS(selected_options, ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$orderId, $productId, $optionsJson]);
        } else {
            $sql = "SELECT * FROM order_details WHERE order_id = ? AND product_id = ? AND selected_options IS NULL";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$orderId, $productId]);
        }
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Lấy thông tin sản phẩm
        $stmtP = $this->conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmtP->execute([$productId]);
        $product = $stmtP->fetch(PDO::FETCH_ASSOC);
        if (!$product) return false;
        $price = $product['price'] - ($product['sale'] ?? 0);
        if ($existing) {
            // Cộng dồn số lượng
            $newQty = $existing['quantity'] + $quantity;
            $stmt2 = $this->conn->prepare("UPDATE order_details SET quantity = ? WHERE id = ?");
            $result = $stmt2->execute([$newQty, $existing['id']]);
            return $result;
        } else {
            $stmt3 = $this->conn->prepare("INSERT INTO order_details (order_id, product_id, product_name, quantity, price, selected_options) VALUES (?, ?, ?, ?, ?, ?)");
            $result = $stmt3->execute([
                $orderId,
                $productId,
                $product['name'],
                $quantity,
                $price,
                $optionsJson
            ]);
            return $result;
        }
    }

    // Lấy toàn bộ sản phẩm trong giỏ
    public function getCart($userId = null, $sessionId = null) {
        $order = $this->getPendingOrder($userId, $sessionId);
        if (!$order) return [];
        $orderId = $order['id'];
        $sql = "SELECT od.*, 
    p.name as product_name, 
    p.price as product_price, 
    p.sale as product_sale, 
    p.stock as product_stock, 
    p.category_id as product_category_id, 
    p.description as product_description, 
    p.size as product_size, 
    p.color as product_color, 
    p.main_images as product_main_images,
    c.name as category_name,
    pi.image_path as product_image,
    (od.price * od.quantity) as total_price,
    od.price as current_price
 FROM order_details od
 JOIN products p ON od.product_id = p.id
 LEFT JOIN categories c ON p.category_id = c.id
 LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.image_type = 'main' AND pi.sort_order = 0
 WHERE od.order_id = ? ORDER BY od.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($items as &$item) {
            $item['selected_options_array'] = $item['selected_options'] ? json_decode($item['selected_options'], true) : [];
            // Đảm bảo có đủ các trường cần thiết
            $item['total_price'] = floatval($item['price']) * intval($item['quantity']);
            $item['current_price'] = floatval($item['price']);
        }
        return $items;
    }

    // Xóa sản phẩm khỏi giỏ
    public function removeFromCart($orderDetailId) {
        $stmt = $this->conn->prepare("DELETE FROM order_details WHERE id = ?");
        return $stmt->execute([$orderDetailId]);
    }

    // Cập nhật số lượng
    public function updateCartItem($orderDetailId, $quantity) {
        $stmt = $this->conn->prepare("UPDATE order_details SET quantity = ? WHERE id = ?");
        return $stmt->execute([$quantity, $orderDetailId]);
    }

    // Đếm số sản phẩm trong giỏ
    public function getCartCount($userId = null, $sessionId = null) {
        $order = $this->getPendingOrder($userId, $sessionId);
        if (!$order) return 0;
        $orderId = $order['id'];
        $stmt = $this->conn->prepare("SELECT SUM(quantity) as count FROM order_details WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    // Kiểm tra stock sản phẩm chính
    public function checkStock($productId, $quantity) {
        $sql = "SELECT stock FROM products WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) return false;
        return $product['stock'] >= $quantity;
    }

    // Tính tổng tiền giỏ hàng
    public function getCartTotal($userId = null, $sessionId = null) {
        $cartItems = $this->getCart($userId, $sessionId);
        $total = 0;
        
        foreach ($cartItems as $item) {
            $price = floatval($item['price']);
            $quantity = intval($item['quantity']);
            $total += $price * $quantity;
        }
        
        return $total;
    }

    // Xóa toàn bộ giỏ hàng
    public function clearCart($userId = null, $sessionId = null) {
        $order = $this->getPendingOrder($userId, $sessionId);
        if (!$order) return false;
        
        $orderId = $order['id'];
        
        // Xóa tất cả order_details
        $stmt = $this->conn->prepare("DELETE FROM order_details WHERE order_id = ?");
        $stmt->execute([$orderId]);
        
        // Xóa order
        $stmt2 = $this->conn->prepare("DELETE FROM orders WHERE id = ?");
        return $stmt2->execute([$orderId]);
    }
}
?> 
