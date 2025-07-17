<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';

// Khởi tạo kết nối database
$db = new Database();
$conn = $db->getConnection();

// Khởi tạo model
$orderModel = new Order($conn);

// Lấy danh sách đơn hàng chờ thanh toán
$pendingOrders = $orderModel->getPendingPayments();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý thanh toán - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .payment-management {
            background: #f8f9fa;
            min-height: 100vh;
            padding: 20px 0;
        }
        
        .payment-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .payment-header {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            padding: 20px;
        }
        
        .payment-body {
            padding: 20px;
        }
        
        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .order-detail {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
        }
        
        .order-detail h6 {
            color: #666;
            font-size: 0.8rem;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .order-detail p {
            color: #222;
            font-weight: 600;
            margin: 0;
        }
        
        .payment-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-badge.paid {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .btn-sm {
            padding: 5px 12px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="payment-management">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-credit-card me-2"></i>Quản lý thanh toán</h2>
                        <a href="index.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                        </a>
                    </div>
                    
                    <?php if (empty($pendingOrders)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h4>Không có đơn hàng chờ thanh toán</h4>
                        <p class="text-muted">Tất cả đơn hàng đã được xử lý</p>
                    </div>
                    <?php else: ?>
                    
                    <div class="row">
                        <?php foreach ($pendingOrders as $order): ?>
                        <div class="col-lg-6 col-xl-4">
                            <div class="payment-card">
                                <div class="payment-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Đơn hàng #<?php echo $order['id']; ?></h6>
                                        <span class="status-badge <?php echo $order['payment_status']; ?>">
                                            <i class="fas fa-<?php echo $order['payment_status'] === 'paid' ? 'check-circle' : 'clock'; ?>"></i>
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="payment-body">
                                    <div class="order-info">
                                        <div class="order-detail">
                                            <h6>Khách hàng</h6>
                                            <p><?php echo htmlspecialchars($order['guest_name']); ?></p>
                                        </div>
                                        <div class="order-detail">
                                            <h6>Số điện thoại</h6>
                                            <p><?php echo htmlspecialchars($order['guest_phone']); ?></p>
                                        </div>
                                        <div class="order-detail">
                                            <h6>Tổng tiền</h6>
                                            <p><?php echo number_format($order['total']); ?>₫</p>
                                        </div>
                                        <div class="order-detail">
                                            <h6>Phương thức</h6>
                                            <p><?php echo ucfirst($order['payment_method']); ?></p>
                                        </div>
                                        <div class="order-detail">
                                            <h6>Ngày đặt</h6>
                                            <p><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="payment-actions">
                                        <button type="button" class="btn btn-success btn-sm" onclick="markAsPaid(<?php echo $order['id']; ?>)">
                                            <i class="fas fa-check me-1"></i>Đã thanh toán
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="markAsFailed(<?php echo $order['id']; ?>)">
                                            <i class="fas fa-times me-1"></i>Thanh toán thất bại
                                        </button>
                                        <button type="button" class="btn btn-info btn-sm" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                            <i class="fas fa-eye me-1"></i>Chi tiết
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Đánh dấu đã thanh toán
        function markAsPaid(orderId) {
            if (confirm('Xác nhận đơn hàng #' + orderId + ' đã thanh toán?')) {
                updatePaymentStatus(orderId, 'paid');
            }
        }
        
        // Đánh dấu thanh toán thất bại
        function markAsFailed(orderId) {
            if (confirm('Xác nhận đơn hàng #' + orderId + ' thanh toán thất bại?')) {
                updatePaymentStatus(orderId, 'failed');
            }
        }
        
        // Cập nhật trạng thái thanh toán
        function updatePaymentStatus(orderId, status) {
            fetch('../index.php?page=api/order/update-payment-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    order_id: orderId,
                    payment_status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Cập nhật trạng thái thành công!');
                    location.reload();
                } else {
                    alert('Lỗi: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra');
            });
        }
        
        // Xem chi tiết đơn hàng
        function viewOrderDetails(orderId) {
            window.open('../index.php?page=order&id=' + orderId, '_blank');
        }
    </script>
</body>
</html> 