<?php
require_once __DIR__ . '/../config/database.php';

class Order {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Lấy tất cả đơn hàng
    public function getAll($limit = null, $offset = null, $status = null) {
        $sql = "SELECT o.*, u.name as user_name, u.email as user_email 
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id";
        $params = [];
        
        if ($status) {
            $sql .= " WHERE o.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY o.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
            if ($offset) {
                $sql .= " OFFSET $offset";
            }
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Lấy đơn hàng theo ID
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT o.*, u.name as user_name, u.email as user_email 
                                    FROM orders o 
                                    LEFT JOIN users u ON o.user_id = u.id 
                                    WHERE o.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Lấy đơn hàng theo user ID
    public function getByUserId($user_id, $limit = null, $offset = null) {
        $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
            if ($offset) {
                $sql .= " OFFSET $offset";
            }
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }
    
    // Tạo đơn hàng mới
    public function create($data) {
        $this->pdo->beginTransaction();
        
        try {
            $sql = "INSERT INTO orders (user_id, guest_name, guest_email, guest_phone, 
                    total, status, payment_status, payment_method, delivery_address, 
                    freeshipcode, notes) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['user_id'] ?? null,
                $data['guest_name'] ?? null,
                $data['guest_email'] ?? null,
                $data['guest_phone'] ?? null,
                $data['total'],
                $data['status'] ?? 'pending',
                $data['payment_status'] ?? 'pending',
                $data['payment_method'] ?? null,
                $data['delivery_address'] ?? null,
                $data['freeshipcode'] ?? null,
                $data['notes'] ?? null
            ]);
            
            $order_id = $this->pdo->lastInsertId();
            
            // Thêm chi tiết đơn hàng
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $this->addOrderDetail($order_id, $item);
                }
            }
            
            $this->pdo->commit();
            return $order_id;
            
        } catch (Exception $e) {
            $this->pdo->rollback();
            throw $e;
        }
    }
    
    // Cập nhật đơn hàng
    public function update($id, $data) {
        $sql = "UPDATE orders SET 
                status = ?, 
                payment_status = ?, 
                payment_method = ?, 
                delivery_address = ?, 
                notes = ? 
                WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['status'] ?? 'pending',
            $data['payment_status'] ?? 'pending',
            $data['payment_method'] ?? null,
            $data['delivery_address'] ?? null,
            $data['notes'] ?? null,
            $id
        ]);
    }
    
    // Xóa đơn hàng
    public function delete($id) {
        $this->pdo->beginTransaction();
        
        try {
            // Xóa chi tiết đơn hàng trước
            $stmt = $this->pdo->prepare("DELETE FROM order_details WHERE order_id = ?");
            $stmt->execute([$id]);
            
            // Xóa đơn hàng
            $stmt = $this->pdo->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$id]);
            
            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $this->pdo->rollback();
            throw $e;
        }
    }
    
    // Thêm chi tiết đơn hàng
    public function addOrderDetail($order_id, $item) {
        $sql = "INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $order_id,
            $item['product_id'],
            $item['quantity'],
            $item['price']
        ]);
    }
    
    // Lấy chi tiết đơn hàng
    public function getOrderDetails($order_id) {
        $sql = "SELECT od.*, p.name as product_name, p.image_ as product_image 
                FROM order_details od 
                LEFT JOIN products p ON od.product_id = p.id 
                WHERE od.order_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$order_id]);
        return $stmt->fetchAll();
    }
    
    // Cập nhật trạng thái đơn hàng
    public function updateStatus($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
    
    // Cập nhật trạng thái thanh toán
    public function updatePaymentStatus($id, $payment_status) {
        $stmt = $this->pdo->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
        return $stmt->execute([$payment_status, $id]);
    }
    
    // Đếm tổng số đơn hàng
    public function count($status = null) {
        $sql = "SELECT COUNT(*) FROM orders";
        $params = [];
        
        if ($status) {
            $sql .= " WHERE status = ?";
            $params[] = $status;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    // Lấy tổng số đơn hàng (alias cho count)
    public function getTotal($status = null) {
        return $this->count($status);
    }
    
    // Tính tổng doanh thu
    public function getTotalRevenue($status = 'completed') {
        $stmt = $this->pdo->prepare("SELECT SUM(total) FROM orders WHERE status = ? AND payment_status = 'paid'");
        $stmt->execute([$status]);
        return $stmt->fetchColumn() ?: 0;
    }
    
    // Lấy đơn hàng theo ngày
    public function getByDate($date) {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE DATE(created_at) = ? ORDER BY created_at DESC");
        $stmt->execute([$date]);
        return $stmt->fetchAll();
    }
    
    // Lấy đơn hàng theo khoảng thời gian
    public function getByDateRange($start_date, $end_date) {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE DATE(created_at) BETWEEN ? AND ? ORDER BY created_at DESC");
        $stmt->execute([$start_date, $end_date]);
        return $stmt->fetchAll();
    }
    
    // Lấy thống kê đơn hàng
    public function getStats() {
        $stats = [];
        
        // Tổng số đơn hàng
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM orders");
        $stats['total_orders'] = $stmt->fetchColumn();
        
        // Đơn hàng chờ xử lý
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
        $stats['pending_orders'] = $stmt->fetchColumn();
        
        // Đơn hàng đã hoàn thành
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'");
        $stats['completed_orders'] = $stmt->fetchColumn();
        
        // Tổng doanh thu
        $stmt = $this->pdo->query("SELECT SUM(total) FROM orders WHERE status = 'completed' AND payment_status = 'paid'");
        $stats['total_revenue'] = $stmt->fetchColumn() ?: 0;
        
        // Đơn hàng hôm nay
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()");
        $stmt->execute();
        $stats['today_orders'] = $stmt->fetchColumn();
        
        return $stats;
    }
    
    // Lấy trạng thái đơn hàng dạng text
    public function getStatusText($status) {
        return match($status) {
            'pending' => 'Chờ xử lý',
            'preparing' => 'Đang chuẩn bị',
            'ready' => 'Sẵn sàng',
            'served' => 'Đã phục vụ',
            'cancelled' => 'Đã hủy',
            'completed' => 'Hoàn thành',
            default => 'Không xác định'
        };
    }
    
    // Lấy trạng thái thanh toán dạng text
    public function getPaymentStatusText($status) {
        return match($status) {
            'pending' => 'Chờ thanh toán',
            'paid' => 'Đã thanh toán',
            'failed' => 'Thanh toán thất bại',
            default => 'Không xác định'
        };
    }
    
    // Lấy phương thức thanh toán dạng text
    public function getPaymentMethodText($method) {
        return match($method) {
            'cash' => 'Tiền mặt',
            'card' => 'Thẻ',
            'online' => 'Trực tuyến',
            default => 'Không xác định'
        };
    }
}
?> 