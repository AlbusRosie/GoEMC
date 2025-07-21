<?php
session_start();

// Chặn truy cập nếu không phải admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../controllers/ManagementCashController.php';

$orderId = $_GET['id'] ?? 0;
$ctrl = new GQLcash();

// Lấy thông tin đơn hàng
$order = $ctrl->getOrderDetail($orderId);
if (!$order) {
    echo "<div class='alert alert-danger'>Đơn hàng không tồn tại!</div>";
    exit;
}

// Lấy danh sách sản phẩm
$orderItems = $ctrl->getOrderItems($orderId);

// Utility functions
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.') . 'đ';
}

function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

function getStatusInfo($status) {
    $statuses = [
        'pending' => ['text' => 'Chờ xác nhận', 'class' => 'bg-warning'],
        'confirmed' => ['text' => 'Đã xác nhận', 'class' => 'bg-info'],
        'processing' => ['text' => 'Đang xử lý', 'class' => 'bg-primary'],
        'shipped' => ['text' => 'Đang giao hàng', 'class' => 'bg-warning'],
        'delivered' => ['text' => 'Đã giao hàng', 'class' => 'bg-success'],
        'cancelled' => ['text' => 'Đã hủy', 'class' => 'bg-danger'],
        'completed' => ['text' => 'Hoàn thành', 'class' => 'bg-success'],
    ];
    
    return $statuses[$status] ?? ['text' => ucfirst($status), 'class' => 'bg-secondary'];
}

function getPaymentStatusInfo($status) {
    $statuses = [
        'pending' => ['text' => 'Chờ thanh toán', 'class' => 'text-warning'],
        'paid' => ['text' => 'Đã thanh toán', 'class' => 'text-success'],
        'failed' => ['text' => 'Thất bại', 'class' => 'text-danger'],
        'refunded' => ['text' => 'Đã hoàn tiền', 'class' => 'text-info'],
        'unpaid' => ['text' => 'Chưa thanh toán', 'class' => 'text-warning'],
    ];
    
    return $statuses[$status] ?? ['text' => ucfirst($status), 'class' => 'text-secondary'];
}

function getPaymentMethodText($method) {
    $methods = [
        'cash' => 'Tiền mặt',
        'cod' => 'Thanh toán khi nhận hàng',
        'bank_transfer' => 'Chuyển khoản',
        'credit_card' => 'Thẻ tín dụng',
        'e_wallet' => 'Ví điện tử',
        'momo' => 'Ví MoMo',
        'zalopay' => 'ZaloPay',
        'vnpay' => 'VNPay',
    ];
    
    return $methods[$method] ?? ucfirst($method);
}

$statusInfo = getStatusInfo($order['status']);
$paymentStatusInfo = getPaymentStatusInfo($order['payment_status']);
$paymentMethodText = getPaymentMethodText($order['payment_method']);

// Tính tổng tiền
$tongTien = 0;
foreach ($orderItems as $item) {
    $tongTien += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng #<?= $orderId ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
        }
        
        .card {
            border: none;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            transition: transform 0.2s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
        }
        
        .card-header {
            border-radius: 15px 15px 0 0 !important;
            font-weight: 600;
            padding: 1rem 1.5rem;
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }
        
        .status-badge {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
        }
        
        .info-row {
            padding: 0.75rem 0;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .table-custom {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        
        .table-custom th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            padding: 1rem;
            border: none;
        }
        
        .table-custom td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #f8f9fa;
        }
        
        .total-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-top: 2rem;
        }
        
        .btn-back {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 0.75rem 2rem;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
        }
        
        .no-products {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        
        .no-products i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .product-image {
                width: 60px;
                height: 60px;
            }
            
            .table-custom th,
            .table-custom td {
                padding: 0.5rem;
                font-size: 0.875rem;
            }
            
            .page-header {
                padding: 1rem 0;
            }
        }
    </style>
</head>
<body>
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">
                        <i class="fas fa-receipt me-3"></i>
                        Chi tiết đơn hàng #<?= $orderId ?>
                    </h1>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge status-badge <?= $statusInfo['class'] ?>">
                        <?= $statusInfo['text'] ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Order Statistics -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h5 class="mb-1"><?= count($orderItems) ?></h5>
                    <small class="text-muted">Sản phẩm</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h6 class="mb-1"><?= formatDate($order['created_at']) ?></h6>
                    <small class="text-muted">Ngày đặt</small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h6 class="mb-1"><?= $paymentMethodText ?></h6>
                    <small class="<?= $paymentStatusInfo['class'] ?>"><?= $paymentStatusInfo['text'] ?></small>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <div class="stats-icon" style="background: linear-gradient(135deg, #fbc2eb 0%, #a6c1ee 100%);">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h5 class="mb-1 text-success"><?= formatCurrency($tongTien) ?></h5>
                    <small class="text-muted">Tổng tiền</small>
                </div>
            </div>
        </div>

        <!-- Order Information -->
        <div class="row mb-4">
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2"></i>
                            Thông tin khách hàng
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <div class="row">
                                <div class="col-sm-4"><strong>Khách hàng:</strong></div>
                                <div class="col-sm-8"><?= htmlspecialchars($order['guest_name'] ?? 'Khách vãng lai') ?></div>
                            </div>
                        </div>
                        <?php if (!empty($order['guest_email'])): ?>
                        <div class="info-row">
                            <div class="row">
                                <div class="col-sm-4"><strong>Email:</strong></div>
                                <div class="col-sm-8"><?= htmlspecialchars($order['guest_email']) ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($order['guest_phone'])): ?>
                        <div class="info-row">
                            <div class="row">
                                <div class="col-sm-4"><strong>Điện thoại:</strong></div>
                                <div class="col-sm-8"><?= htmlspecialchars($order['guest_phone']) ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($order['delivery_address'])): ?>
                        <div class="info-row">
                            <div class="row">
                                <div class="col-sm-4"><strong>Địa chỉ giao hàng:</strong></div>
                                <div class="col-sm-8"><?= htmlspecialchars($order['delivery_address']) ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($order['notes'])): ?>
                        <div class="info-row">
                            <div class="row">
                                <div class="col-sm-4"><strong>Ghi chú:</strong></div>
                                <div class="col-sm-8"><?= htmlspecialchars($order['notes']) ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Thông tin đơn hàng
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <div class="row">
                                <div class="col-sm-5"><strong>Mã đơn hàng:</strong></div>
                                <div class="col-sm-7">#<?= $orderId ?></div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="row">
                                <div class="col-sm-5"><strong>Ngày đặt hàng:</strong></div>
                                <div class="col-sm-7"><?= formatDate($order['created_at']) ?></div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="row">
                                <div class="col-sm-5"><strong>Trạng thái:</strong></div>
                                <div class="col-sm-7">
                                    <span class="badge <?= $statusInfo['class'] ?>">
                                        <?= $statusInfo['text'] ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="row">
                                <div class="col-sm-5"><strong>Thanh toán:</strong></div>
                                <div class="col-sm-7">
                                    <?= $paymentMethodText ?>
                                    <br>
                                    <small class="<?= $paymentStatusInfo['class'] ?>">
                                        <?= $paymentStatusInfo['text'] ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($order['updated_at'])): ?>
                        <div class="info-row">
                            <div class="row">
                                <div class="col-sm-5"><strong>Cập nhật:</strong></div>
                                <div class="col-sm-7"><?= formatDate($order['updated_at']) ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products List -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="fas fa-shopping-bag me-2"></i>
                    Danh sách sản phẩm (<?= count($orderItems) ?> sản phẩm)
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($orderItems)): ?>
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th width="8%">#</th>
                                <th width="15%">Ảnh</th>
                                <th width="30%">Tên sản phẩm</th>
                                <th width="15%">Giá</th>
                                <th width="12%">Số lượng</th>
                                <th width="20%">Tổng</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            foreach ($orderItems as $item):
                            ?>
                            <tr>
                                <td class="text-center">
                                    <span class="badge bg-secondary"><?= $i++ ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($item['image_'])): ?>
                                        <img src="../<?= htmlspecialchars($item['image_']) ?>" 
                                             class="product-image" 
                                             alt="<?= htmlspecialchars($item['product_name']) ?>">
                                    <?php else: ?>
                                        <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                                    <?php if (!empty($item['product_code'])): ?>
                                        <br><small class="text-muted">Mã SP: <?= htmlspecialchars($item['product_code']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong class="text-primary"><?= formatCurrency($item['price']) ?></strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark"><?= $item['quantity'] ?></span>
                                </td>
                                <td>
                                    <strong class="text-success fs-6">
                                        <?= formatCurrency($item['price'] * $item['quantity']) ?>
                                    </strong>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="no-products">
                    <i class="fas fa-box-open"></i>
                    <h5>Không có sản phẩm</h5>
                    <p class="text-muted">Đơn hàng này chưa có sản phẩm nào.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Total Section -->
        <?php if (!empty($orderItems)): ?>
        <div class="total-section">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>
                        Tổng cộng đơn hàng
                    </h4>
                    <p class="mb-0 opacity-75">
                        <?= count($orderItems) ?> sản phẩm • 
                        Thanh toán: <?= $paymentMethodText ?> • 
                        <?= $paymentStatusInfo['text'] ?>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <h2 class="mb-0 fw-bold">
                        <?= formatCurrency($tongTien) ?>
                    </h2>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Back Button -->
        <div class="text-center mt-5 mb-4">
            <a href="orders.php" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Quay lại danh sách đơn hàng
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a2e0e6ad65.js" crossorigin="anonymous"></script>
</body>
</html>