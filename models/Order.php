<?php
require_once __DIR__ . '/../config/database.php';

class Order {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Tạo đơn hàng mới
     */
    public function createOrder($orderData) {
        try {
            $this->conn->beginTransaction();
            
            // Tạo đơn hàng
            $sql = "INSERT INTO orders (
                user_id, guest_name, guest_email, guest_phone, 
                total, subtotal, shipping_fee, discount_amount,
                status, payment_status, payment_method,
                delivery_address, delivery_city, delivery_district, delivery_ward,
                delivery_notes, coupon_code, coupon_discount, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
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
            
            $orderId = $this->conn->lastInsertId();
            
            // Thêm chi tiết đơn hàng
            foreach ($orderData['items'] as $item) {
                $this->addOrderDetail($orderId, $item);
            }
            
            // Thêm lịch sử trạng thái
            $this->addOrderStatusHistory($orderId, 'pending', 'Đơn hàng được tạo');
            
            $this->conn->commit();
            return $orderId;
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error creating order: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Thêm chi tiết đơn hàng
     */
    private function addOrderDetail($orderId, $item) {
        try {
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
        } catch (PDOException $e) {
            error_log("Error adding order detail: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Thêm lịch sử trạng thái đơn hàng
     */
    public function addOrderStatusHistory($orderId, $status, $notes = '') {
        try {
            $sql = "INSERT INTO order_status_history (order_id, status, notes) VALUES (?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$orderId, $status, $notes]);
        } catch (PDOException $e) {
            error_log("Error adding order status history: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateOrderStatus($orderId, $status, $notes = '') {
        try {
            $this->conn->beginTransaction();
            
            // Cập nhật trạng thái đơn hàng
            $sql = "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$status, $orderId]);
            
            // Thêm lịch sử trạng thái
            $this->addOrderStatusHistory($orderId, $status, $notes);
            
            $this->conn->commit();
            return true;
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error updating order status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lấy đơn hàng theo ID
     */
    public function getById($orderId) {
        try {
            $sql = "SELECT * FROM orders WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($order) {
                $order['details'] = $this->getOrderDetails($orderId);
                $order['status_history'] = $this->getOrderStatusHistory($orderId);
            }
            
            return $order;
        } catch (PDOException $e) {
            error_log("Error getting order: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lấy chi tiết đơn hàng
     */
    private function getOrderDetails($orderId) {
        try {
            $sql = "SELECT od.*, p.image_ 
                FROM order_details od 
                LEFT JOIN products p ON od.product_id = p.id 
                WHERE od.order_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$orderId]);
            
            $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($details as &$detail) {
                $detail['selected_options_array'] = json_decode($detail['selected_options'], true);
            }
            
            return $details;
        } catch (PDOException $e) {
            error_log("Error getting order details: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lấy lịch sử trạng thái đơn hàng
     */
    private function getOrderStatusHistory($orderId) {
        try {
            $sql = "SELECT * FROM order_status_history WHERE order_id = ? ORDER BY created_at ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$orderId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting order status history: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lấy đơn hàng của user
     */
    public function getByUserId($userId, $limit = null) {
        try {
            $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
            
            if ($limit) {
                $sql .= " LIMIT " . (int)$limit;
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user orders: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Lấy tất cả đơn hàng (cho admin)
     */
    public function getAll($page = 1, $limit = 20, $status = null) {
        try {
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
        } catch (PDOException $e) {
            error_log("Error getting all orders: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Đếm tổng số đơn hàng
     */
    public function getTotalCount($status = null) {
        try {
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
        } catch (PDOException $e) {
            error_log("Error getting order count: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Tính phí vận chuyển
     */
    public function calculateShippingFee($subtotal, $deliveryCity = null) {
        // Logic tính phí vận chuyển
        if ($subtotal >= 2000000) {
            return 0; // Miễn phí vận chuyển cho đơn hàng từ 2 triệu
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
        try {
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
            
            $discount = 0;
            
            if ($coupon['discount_type'] === 'percentage') {
                $discount = $subtotal * ($coupon['discount_value'] / 100);
                if ($coupon['max_discount']) {
                    $discount = min($discount, $coupon['max_discount']);
                }
            } else {
                $discount = $coupon['discount_value'];
            }
            
            return [
                'success' => true,
                'coupon' => $coupon,
                'discount' => $discount
            ];
            
        } catch (PDOException $e) {
            error_log("Error applying coupon: " . $e->getMessage());
            return ['success' => false, 'message' => 'Có lỗi xảy ra'];
        }
    }
    
    /**
     * Cập nhật số lượng sử dụng mã giảm giá
     */
    public function updateCouponUsage($couponCode) {
        try {
            $sql = "UPDATE coupons SET used_count = used_count + 1 WHERE code = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$couponCode]);
        } catch (PDOException $e) {
            error_log("Error updating coupon usage: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lấy thống kê đơn hàng
     */
    public function getOrderStats() {
        try {
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
        } catch (PDOException $e) {
            error_log("Error getting order stats: " . $e->getMessage());
            return [];
        }
    }
}
?> 