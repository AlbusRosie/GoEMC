<?php
class Cart {
    private $conn;
    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Lấy đơn hàng pending hiện tại (theo user hoặc session)
    public function getPendingOrder($userId = null, $sessionId = null) {
        $sql = "SELECT * FROM orders WHERE status = 'pending' AND ";
        if ($userId) {
            $sql .= "user_id = ?";
            $params = [$userId];
        } else {
            $sql .= "guest_phone = ?";
            $params = [$sessionId];
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tạo đơn hàng pending mới
    public function createPendingOrder($userId = null, $sessionId = null) {
        $sql = "INSERT INTO orders (user_id, guest_phone, total, subtotal, status, payment_status, created_at) VALUES (?, ?, 0, 0, 'pending', 'pending', NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId, $sessionId]);
        $orderId = $this->conn->lastInsertId();
        return $this->getOrderById($orderId);
    }

    // Lấy đơn hàng theo id
    public function getOrderById($orderId) {
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Thêm sản phẩm vào giỏ (order_details)
    public function addToCart($productId, $quantity, $selectedOptions = null, $userId = null, $sessionId = null) {
        // Lấy hoặc tạo đơn pending
        $order = $this->getPendingOrder($userId, $sessionId);
        if (!$order) {
            $order = $this->createPendingOrder($userId, $sessionId);
        }
        $orderId = $order['id'];
        // Kiểm tra sản phẩm đã có trong order_details chưa
        $optionsJson = $selectedOptions ? json_encode($selectedOptions, JSON_UNESCAPED_UNICODE) : null;
        $sql = "SELECT * FROM order_details WHERE order_id = ? AND product_id = ? AND ";
        if ($selectedOptions && !empty($selectedOptions)) {
            $sql .= "selected_options = ?";
            $params = [$orderId, $productId, $optionsJson];
        } else {
            $sql .= "(selected_options IS NULL OR selected_options = 'null' OR selected_options = '{}')";
            $params = [$orderId, $productId];
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            // Nếu đã có thì cộng dồn số lượng
            $newQty = $existing['quantity'] + $quantity;
            $stmt2 = $this->conn->prepare("UPDATE order_details SET quantity = ? WHERE id = ?");
            return $stmt2->execute([$newQty, $existing['id']]);
        } else {
            // Lấy thông tin sản phẩm
            $stmtP = $this->conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmtP->execute([$productId]);
            $product = $stmtP->fetch(PDO::FETCH_ASSOC);
            if (!$product) return false;
            $price = $product['sale'] && $product['sale'] > 0 ? $product['sale'] : $product['price'];
            $stmt3 = $this->conn->prepare("INSERT INTO order_details (order_id, product_id, product_name, quantity, price, selected_options) VALUES (?, ?, ?, ?, ?, ?)");
            return $stmt3->execute([
                $orderId,
                $productId,
                $product['name'],
                $quantity,
                $price,
                $optionsJson
            ]);
        }
    }

    // Lấy toàn bộ sản phẩm trong giỏ
    public function getCart($userId = null, $sessionId = null) {
        $order = $this->getPendingOrder($userId, $sessionId);
        if (!$order) return [];
        $orderId = $order['id'];
        $sql = "SELECT * FROM order_details WHERE order_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$orderId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($items as &$item) {
            $item['selected_options_array'] = $item['selected_options'] ? json_decode($item['selected_options'], true) : [];
            $item['total_price'] = $item['price'] * $item['quantity'];
        }
        return $items;
    }

    // Cập nhật số lượng sản phẩm trong giỏ
    public function updateCartItem($orderDetailId, $quantity) {
        $stmt = $this->conn->prepare("UPDATE order_details SET quantity = ? WHERE id = ?");
        return $stmt->execute([$quantity, $orderDetailId]);
    }

    // Xóa sản phẩm khỏi giỏ
    public function removeFromCart($orderDetailId) {
        $stmt = $this->conn->prepare("DELETE FROM order_details WHERE id = ?");
        return $stmt->execute([$orderDetailId]);
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

    // Tính tổng tiền giỏ hàng
    public function getCartTotal($userId = null, $sessionId = null) {
        $items = $this->getCart($userId, $sessionId);
        $total = 0;
        foreach ($items as $item) {
            $total += $item['total_price'];
        }
        return $total;
    }

    // Kiểm tra stock
    public function checkStock($productId, $quantity) {
        $sql = "SELECT stock FROM products WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) return false;
        return $product['stock'] >= $quantity;
    }

    // Đổi trạng thái đơn hàng (checkout)
    public function checkout($userId = null, $sessionId = null, $status = 'confirmed', $data = []) {
        $order = $this->getPendingOrder($userId, $sessionId);
        if (!$order) return false;
        $orderId = $order['id'];
        $sql = "UPDATE orders SET status = ?, payment_status = ?, total = ?, subtotal = ?, guest_name = ?, guest_email = ?, guest_phone = ?, delivery_address = ?, notes = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $status,
            $data['payment_status'] ?? 'pending',
            $data['total'] ?? $order['total'],
            $data['subtotal'] ?? $order['subtotal'],
            $data['guest_name'] ?? null,
            $data['guest_email'] ?? null,
            $data['guest_phone'] ?? null,
            $data['delivery_address'] ?? null,
            $data['notes'] ?? null,
            $orderId
        ]);
    }
}
?> 