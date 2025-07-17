<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Class Order - Quản lý đơn hàng
 */
class Order {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Tạo đơn hàng mới
     * @param array $orderData Dữ liệu đơn hàng
     * @return int|false ID đơn hàng hoặc false nếu lỗi
     */
    public function createOrder($orderData) {
        try {
            
            $this->conn->beginTransaction();
            
            // Tạo đơn hàng chính
            $orderId = $this->insertOrder($orderData);
            if (!$orderId) {
                throw new Exception('Không thể tạo đơn hàng');
            }
            
            // Thêm chi tiết đơn hàng
            foreach ($orderData['items'] as $item) {
                $result = $this->addOrderDetail($orderId, $item);
                if (!$result) {
                    throw new Exception('Không thể thêm chi tiết đơn hàng');
                }
            }
            
            // Không cần thêm lịch sử trạng thái
            
            $this->conn->commit();
            return $orderId;
            
        } catch (Exception $e) {
            $this->conn->rollBack();

            return false;
        }
    }
    
    /**
     * Thêm đơn hàng vào database
     */
    private function insertOrder($orderData) {
        $sql = "INSERT INTO orders (
            user_id, guest_name, guest_email, guest_phone, 
            total, subtotal, shipping_fee, discount_amount,
            status, payment_status, payment_method,
            delivery_address, delivery_city, delivery_district, delivery_ward,
            delivery_notes, coupon_code, coupon_discount, notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            $orderData['user_id'],
            $orderData['guest_name'],
            $orderData['guest_email'],
            $orderData['guest_phone'],
            $orderData['total'],
            $orderData['subtotal'],
            $orderData['shipping_fee'],
            $orderData['discount_amount'],
            $orderData['status'],
            $orderData['payment_status'],
            $orderData['payment_method'],
            $orderData['delivery_address'],
            $orderData['delivery_city'],
            $orderData['delivery_district'],
            $orderData['delivery_ward'],
            $orderData['delivery_notes'],
            $orderData['coupon_code'],
            $orderData['coupon_discount'],
            $orderData['notes']
        ]);
        

        
        return $result ? $this->conn->lastInsertId() : false;
    }
    
    /**
     * Thêm chi tiết đơn hàng
     */
    private function addOrderDetail($orderId, $item) {
        $sql = "INSERT INTO order_details (
            order_id, product_id, product_name, quantity, price, selected_options
        ) VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $orderId,
            $item['product_id'],
            $item['product_name'],
            $item['quantity'],
            $item['price'],
            json_encode($item['selected_options'])
        ]);
    }
    

    
    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateOrderStatus($orderId, $status, $notes = '') {
        try {
            $this->conn->beginTransaction();
            
            $sql = "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$status, $orderId]);
            
            // Không cần thêm lịch sử trạng thái
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollBack();

            return false;
        }
    }
    
    /**
     * Cập nhật trạng thái thanh toán
     */
    public function updatePaymentStatus($orderId, $paymentStatus) {
        $sql = "UPDATE orders SET payment_status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$paymentStatus, $orderId]);
    }
    
    /**
     * Lấy đơn hàng theo ID
     */
    public function getById($orderId) {
        $sql = "SELECT * FROM orders WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$orderId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy chi tiết đơn hàng
     */
    public function getOrderDetails($orderId) {
        $sql = "SELECT od.*, p.image_, 
                       (SELECT pi.image_path 
                        FROM product_images pi 
                        WHERE pi.product_id = p.id AND pi.image_type = 'main' 
                        ORDER BY pi.sort_order ASC 
                        LIMIT 1) as product_image
                FROM order_details od 
                LEFT JOIN products p ON od.product_id = p.id 
                WHERE od.order_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$orderId]);
        
        $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($details as &$detail) {
            $detail['selected_options_array'] = json_decode($detail['selected_options'], true);
            
            // Nếu không có ảnh từ product_images, sử dụng ảnh mặc định
            if (empty($detail['product_image'])) {
                $detail['product_image'] = 'assets/uploads/product-default.jpg';
            }
        }
        
        return $details;
    }
    

    
    /**
     * Lấy đơn hàng của user
     */
    public function getByUserId($userId, $limit = null) {
        $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy tất cả đơn hàng (cho admin)
     */
    public function getAll($page = 1, $limit = 20, $status = null) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT o.*, u.name as user_name, u.email as user_email 
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id";
        
        $params = [];
        
        if ($status) {
            $sql .= " WHERE o.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Đếm tổng số đơn hàng
     */
    public function getTotalCount($status = null) {
        $sql = "SELECT COUNT(*) as count FROM orders";
        $params = [];
        
        if ($status) {
            $sql .= " WHERE status = ?";
            $params[] = $status;
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'];
    }
    
    /**
     * Tính phí vận chuyển
     */
    public function calculateShippingFee($subtotal, $deliveryCity = null) {
        // Miễn phí vận chuyển cho đơn hàng từ 2 triệu
        if ($subtotal >= 2000000) {
            return 0;
        }
        
        // Phí vận chuyển cơ bản
        $baseFee = 50000;
        
        // Giảm phí cho TP.HCM và Hà Nội
        if (in_array($deliveryCity, ['TP.HCM', 'Hà Nội'])) {
            $baseFee = 30000;
        }
        
        return $baseFee;
    }
    
    /**
     * Áp dụng mã giảm giá
     */
    public function applyCoupon($couponCode, $subtotal) {
        $sql = "SELECT * FROM coupons 
                WHERE code = ? AND is_active = 1 
                AND valid_from <= NOW() AND valid_to >= NOW()
                AND (usage_limit IS NULL OR used_count < usage_limit)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$couponCode]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$coupon) {
            return ['success' => false, 'message' => 'Mã giảm giá không hợp lệ'];
        }
        
        if ($subtotal < $coupon['min_order_amount']) {
            return ['success' => false, 'message' => 'Đơn hàng tối thiểu ' . number_format($coupon['min_order_amount']) . 'đ'];
        }
        
        $discount = $this->calculateDiscount($coupon, $subtotal);
        
        return [
            'success' => true,
            'coupon' => $coupon,
            'discount' => $discount
        ];
    }
    
    /**
     * Tính toán giảm giá
     */
    private function calculateDiscount($coupon, $subtotal) {
        if ($coupon['discount_type'] === 'percentage') {
            $discount = $subtotal * ($coupon['discount_value'] / 100);
            if ($coupon['max_discount']) {
                $discount = min($discount, $coupon['max_discount']);
            }
        } else {
            $discount = $coupon['discount_value'];
        }
        
        return $discount;
    }
    
    /**
     * Cập nhật số lượng sử dụng mã giảm giá
     */
    public function updateCouponUsage($couponCode) {
        $sql = "UPDATE coupons SET used_count = used_count + 1 WHERE code = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$couponCode]);
    }
    
    /**
     * Lấy thống kê đơn hàng
     */
    public function getOrderStats() {
        $sql = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_orders,
                    SUM(CASE WHEN status = 'shipping' THEN 1 ELSE 0 END) as shipping_orders,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                    SUM(total) as total_revenue
                FROM orders 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy danh sách đơn hàng chờ thanh toán
     */
    public function getPendingPayments() {
        $sql = "SELECT * FROM orders WHERE payment_status IN ('pending', 'failed') ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 