<?php
session_start();

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../controllers/ManagementCashController.php';
$p = new GQLcash();
$con = $p->getAllOrders();

// Gọi fetchAll() trước khi xử lý
$orders = $con ? $con->fetchAll() : [];

$totalOrders = count($orders);
$pendingOrders = 0;
$confirmedOrders = 0;
$preparingOrders = 0;
$shippingOrders = 0;
$deliveredOrders = 0;
$cancelledOrders = 0;
$completedOrders = 0;
$totalRevenue = 0;


foreach ($orders as $order) {
    switch ($order['status']) {
        case 'pending':
            $pendingOrders++;
            break;
        case 'confirmed':
            $confirmedOrders++;
            break;
        case 'preparing':
            $preparingOrders++;
            break;
        case 'shipping':
            $shippingOrders++;
            break;
        case 'delivered':
            $deliveredOrders++;
            break;
        case 'cancelled':
            $cancelledOrders++;
            break;
        case 'completed':
            $completedOrders++;
            $totalRevenue += (float)$order['total_price'];
            break;
    }
}

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 1.1em;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .icon {
            font-size: 2.5em;
            margin-bottom: 15px;
        }

        .stat-card .value {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-card .label {
            color: #666;
            font-size: 0.9em;
        }

        .stat-card.total { border-left: 4px solid #3498db; }
        .stat-card.total .icon { color: #3498db; }

        .stat-card.completed { border-left: 4px solid #27ae60; }
        .stat-card.completed .icon { color: #27ae60; }

        .stat-card.pending { border-left: 4px solid #f39c12; }
        .stat-card.pending .icon { color: #f39c12; }

        .stat-card.cancelled { border-left: 4px solid #e74c3c; }
        .stat-card.cancelled .icon { color: #e74c3c; }

        .stat-card.revenue { border-left: 4px solid #9b59b6; }
        .stat-card.revenue .icon { color: #9b59b6; }

        .charts-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .chart-title {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }

        .filters {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .filter-row {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            font-weight: 500;
            color: #555;
        }

        .filter-group select,
        .filter-group input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        .orders-table {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .table-title {
            font-size: 1.3em;
            font-weight: bold;
            color: #333;
        }

        .export-btn {
            background: #27ae60;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .export-btn:hover {
            background: #219a52;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #555;
            position: sticky;
            top: 0;
        }

        tbody tr:hover {
            background-color: #f8f9fa;
        }

        .status-select {
            padding: 4px 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn-detail {
            padding: 4px 8px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
        }

        .btn-detail:hover {
            background: #0056b3;
        }

        .btn-edit {
            padding: 4px 8px;
            background: #ffc107;
            color: #333;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
        }

        .btn-edit:hover {
            background: #e0a800;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            gap: 10px;
        }

        .pagination button {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 4px;
        }

        .pagination button:hover {
            background: #f8f9fa;
        }

        .pagination button.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        @media (max-width: 768px) {
            .charts-section {
                grid-template-columns: 1fr;
            }
            
            .filter-row {
                flex-direction: column;
                align-items: stretch;
            }
            
            .table-header {
                flex-direction: column;
                gap: 10px;
                align-items: stretch;
            }
        }
        #back1 {
            background: linear-gradient(135deg, #ffffffff, #ffffffff);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        #back1 a {
            color: black;
            text-decoration: none;
            font-weight: 500;
            font-size: 16px;
            display: inline-block;
        }

        #back1:hover {
            background: linear-gradient(135deg, #5a67d8, #6b46c1);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 255, 255, 0.15);
            
        }

    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="header">
        <button id="back1"> <a href="index.php">Quay lại</a> </button>
        <h1><i class="fas fa-shopping-cart"></i> Quản lý đơn hàng</h1>
        <p>Theo dõi và quản lý tất cả đơn hàng trong hệ thống</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="icon"><i class="fas fa-list"></i></div>
            <div class="value"><?= $totalOrders ?></div>
            <div class="label">Tổng đơn hàng</div>
        </div>
        <div class="stat-card completed">
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <div class="value"><?= $completedOrders ?></div>
            <div class="label">Đã hoàn thành</div>
        </div>
        <div class="stat-card pending">
            <div class="icon"><i class="fas fa-clock"></i></div>
            <div class="value"><?= $pendingOrders ?></div>
            <div class="label">Chờ xử lý</div>
        </div>
        <div class="stat-card cancelled">
            <div class="icon"><i class="fas fa-times-circle"></i></div>
            <div class="value"><?= $cancelledOrders ?></div>
            <div class="label">Đã hủy</div>
        </div>
        <div class="stat-card revenue">
            <div class="icon"><i class="fas fa-dollar-sign"></i></div>
            <div class="value"><?= number_format($totalRevenue, 0, ',', '.') ?>đ</div>
            <div class="label">Tổng doanh thu</div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section">
        <div class="chart-container">
            <div class="chart-title">Thống kê trạng thái đơn hàng</div>
            <canvas id="statusChart"></canvas>
        </div>
        <div class="chart-container">
            <div class="chart-title">Doanh thu theo thời gian</div>
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters">
        <div class="filter-row">
            <div class="filter-group">
                <label>Trạng thái</label>
                <select id="statusFilter">
                    <option value="">Tất cả</option>
                    <option value="pending">Chờ xử lý</option>
                    <option value="confirmed">Đã xác nhận</option>
                    <option value="preparing">Đang chuẩn bị</option>
                    <option value="shipping">Đang giao</option>
                    <option value="delivered">Đã giao</option>
                    <option value="completed">Hoàn tất</option>
                    <option value="cancelled">Đã hủy</option>
                </select>

            </div>
            <div class="filter-group">
                <label>Phương thức thanh toán</label>
                <select id="paymentFilter">
                    <option value="">Tất cả</option>
                    <option value="cash">Tiền mặt</option>
                    <option value="card">Thẻ</option>
                    <option value="bank_transfer">Chuyển khoản</option>
                    <option value="momo">Momo</option>
                    <option value="zalopay">ZaloPay</option>
                </select>

            </div>
            <div class="filter-group">
                <label>Từ ngày</label>
                <input type="date" id="fromDate">
            </div>
            <div class="filter-group">
                <label>Đến ngày</label>
                <input type="date" id="toDate">
            </div>
            <div class="filter-group">
                <label>&nbsp;</label>
                <button class="btn btn-primary" onclick="applyFilters()">
                    <i class="fas fa-filter"></i> Lọc
                </button>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="orders-table">
        <div class="table-header">
            <div class="table-title">
                <i class="fas fa-table"></i> Danh sách đơn hàng
            </div>
            <button class="export-btn" onclick="exportToExcel()">
                <i class="fas fa-download"></i> Xuất Excel
            </button>
        </div>
        
        <?php if ($orders): ?>
            <table id="ordersTable">
                <thead>
                    <tr>
                        <th>ID Đơn hàng</th>
                        <th>Ngày đặt</th>
                        <th>Trạng thái</th>
                        <th>Phương thức TT</th>
                        <th>Tổng số lượng</th>
                        <th>Tổng tiền</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody">
                    <?php foreach ($orders as $row): ?>
                        <tr data-order-id="<?= $row['order_id'] ?>">
                            <td>#<?= $row['order_id'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                            <td>
                                <select class="status-select" onchange="updateOrderStatus(<?= $row['order_id'] ?>, this.value)">
                                    <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                                    <option value="confirmed" <?= $row['status'] === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                                    <option value="preparing" <?= $row['status'] === 'preparing' ? 'selected' : '' ?>>Đang chuẩn bị</option>
                                    <option value="shipping" <?= $row['status'] === 'shipping' ? 'selected' : '' ?>>Đang giao</option>
                                    <option value="delivered" <?= $row['status'] === 'delivered' ? 'selected' : '' ?>>Đã giao</option>
                                    <option value="completed" <?= $row['status'] === 'completed' ? 'selected' : '' ?>>Hoàn tất</option>
                                    <option value="cancelled" <?= $row['status'] === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                                </select>
                            </td>
                            <td>
                                <?php
                                $paymentMap = [
                                    'cash' => 'Tiền mặt',
                                    'card' => 'Thẻ',
                                    'bank_transfer' => 'Chuyển khoản',
                                    'momo' => 'Momo',
                                    'zalopay' => 'ZaloPay'
                                ];
                                echo $paymentMap[$row['payment_method']] ?? ucfirst($row['payment_method']);
                                ?>
                            </td>

                            <td><?= $row['total_quantity'] ?></td>
                            <td><?= number_format($row['total_price'], 0, ',', '.') ?>đ</td>
                            <td>
                                <div class="action-buttons">
                                    <a class="btn-detail" href="detail_order.php?id=<?= $row['order_id'] ?>">Chi tiết</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Không có đơn hàng nào trong hệ thống.
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Dữ liệu đơn hàng từ PHP
const orders = <?= json_encode($orders ?: []) ?>;
let filteredOrders = orders;

// Hàm format tiền tệ cho số lớn
function formatCurrency(amount) {
    const value = parseFloat(amount);
    
    if (value === 0) return '0đ';
    
    if (value >= 1000000000) {
        // Tỷ VNĐ
        return (value / 1000000000).toFixed(1).replace(/\.0$/, '') + ' tỷ';
    } else if (value >= 1000000) {
        // Triệu VNĐ
        return (value / 1000000).toFixed(1).replace(/\.0$/, '') + ' triệu';
    } else if (value >= 1000) {
        // Nghìn VNĐ
        return (value / 1000).toFixed(1).replace(/\.0$/, '') + 'k';
    } else {
        // Dưới 1000
        return value.toLocaleString('vi-VN') + 'đ';
    }
}

// Hàm format tiền tệ đầy đủ cho tooltip
function formatFullCurrency(amount) {
    const value = parseFloat(amount);
    return value.toLocaleString('vi-VN') + 'đ';
}

// Tạo biểu đồ trạng thái - Thu nhỏ lại
function createStatusChart() {
    const ctx = document.getElementById('statusChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Chờ xử lý', 'Đã xác nhận', 'Đang chuẩn bị', 'Đang giao hàng', 'Đã giao', 'Đã hủy', 'Đã hoàn thành'],
            datasets: [{
                data: [<?= $pendingOrders ?>, <?= $confirmedOrders ?>, <?= $preparingOrders ?>, <?= $shippingOrders ?>, <?= $deliveredOrders ?>, <?= $cancelledOrders ?>, <?= $completedOrders ?>],
                backgroundColor: ['#f39c12', '#2980b9', '#8e44ad', '#16a085', '#2ecc71', '#e74c3c', '#27ae60'],
                borderWidth: 2,
                borderColor: '#fff',
                hoverBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 1.6, // Tăng tỷ lệ để thu nhỏ chiều cao
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 12,
                        usePointStyle: true,
                        font: {
                            size: 10
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#fff',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            cutout: '70%', // Tăng cutout để làm viền mỏng hơn
            layout: {
                padding: {
                    top: 10,
                    bottom: 5,
                    left: 5,
                    right: 5
                }
            }
        }
    });
}

// Tạo biểu đồ doanh thu theo ngày - Cân đối với biểu đồ tròn
function createRevenueChart() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    // Tính doanh thu theo ngày trong 7 ngày gần nhất
    const last7Days = [];
    const revenueData = [];
    
    for (let i = 6; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        const dateStr = date.toISOString().split('T')[0];
        last7Days.push(date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit' }));
        
        const dayRevenue = orders.filter(order => {
            const orderDate = new Date(order.created_at);
            return orderDate.toISOString().split('T')[0] === dateStr && order.status === 'completed';
        }).reduce((sum, order) => sum + parseFloat(order.total_price), 0);
        
        revenueData.push(dayRevenue);
    }

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: last7Days,
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: revenueData,
                backgroundColor: 'rgba(155, 89, 182, 0.7)',
                borderColor: '#9b59b6',
                borderWidth: 1,
                borderRadius: 4,
                borderSkipped: false,
                hoverBackgroundColor: 'rgba(155, 89, 182, 0.9)',
                hoverBorderColor: '#8e44ad',
                hoverBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 1.6, // Cùng tỷ lệ với biểu đồ tròn
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#9b59b6',
                    borderWidth: 1,
                    cornerRadius: 6,
                    caretPadding: 10,
                    callbacks: {
                        label: function(context) {
                            return 'Doanh thu: ' + formatFullCurrency(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Ngày',
                        font: {
                            size: 11,
                            weight: 'bold'
                        }
                    },
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 9
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Doanh thu',
                        font: {
                            size: 11,
                            weight: 'bold'
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)',
                        borderDash: [3, 3]
                    },
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        },
                        maxTicksLimit: 5,
                        font: {
                            size: 9
                        }
                    }
                }
            },
            layout: {
                padding: {
                    top: 10,
                    bottom: 10,
                    left: 5,
                    right: 5
                }
            },
            animation: {
                duration: 800,
                easing: 'easeOutQuart'
            }
        }
    });
}

// Tạo biểu đồ doanh thu theo tháng (tùy chọn)
function createMonthlyRevenueChart() {
    const monthlyCtx = document.getElementById('monthlyRevenueChart');
    if (!monthlyCtx) return;
    
    const ctx = monthlyCtx.getContext('2d');
    
    // Tính doanh thu theo tháng trong 6 tháng gần nhất
    const monthlyData = [];
    const monthLabels = [];
    
    for (let i = 5; i >= 0; i--) {
        const date = new Date();
        date.setMonth(date.getMonth() - i);
        const year = date.getFullYear();
        const month = date.getMonth() + 1;
        
        monthLabels.push(`T${month}/${year}`);
        
        const monthRevenue = orders.filter(order => {
            const orderDate = new Date(order.created_at);
            return orderDate.getFullYear() === year && 
                   orderDate.getMonth() + 1 === month && 
                   order.status === 'completed';
        }).reduce((sum, order) => sum + parseFloat(order.total_price), 0);
        
        monthlyData.push(monthRevenue);
    }

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Doanh thu theo tháng',
                data: monthlyData,
                backgroundColor: 'rgba(155, 89, 182, 0.6)',
                borderColor: '#9b59b6',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 1.5,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: '#9b59b6',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            return 'Doanh thu: ' + formatFullCurrency(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Tháng/Năm',
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    },
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Doanh thu (VNĐ)',
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    },
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        },
                        maxTicksLimit: 5
                    }
                }
            }
        }
    });
}

// Cập nhật hiển thị doanh thu trong stat card
function updateRevenueDisplay() {
    const revenueElement = document.querySelector('.stat-card.revenue .value');
    if (revenueElement) {
        const totalRevenue = <?= $totalRevenue ?>;
        revenueElement.textContent = formatCurrency(totalRevenue);
    }
}

// Cập nhật trạng thái đơn hàng
function updateOrderStatus(orderId, newStatus) {
    if (!confirm(`Bạn có chắc muốn cập nhật trạng thái đơn hàng #${orderId} thành "${getStatusText(newStatus)}"?`)) {
        // Hoàn trả giá trị cũ nếu user hủy
        location.reload();
        return;
    }

    // Hiển thị loading
    showAlert('info', 'Đang cập nhật trạng thái...');

    // Gửi AJAX request để cập nhật
    fetch('update_order_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `order_id=${orderId}&status=${newStatus}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hiển thị thông báo thành công
            showAlert('success', `Đã cập nhật trạng thái đơn hàng #${orderId} thành công!`);
            
            // Reload trang để cập nhật thống kê
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert('error', data.message || 'Có lỗi xảy ra khi cập nhật trạng thái');
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Có lỗi xảy ra khi cập nhật trạng thái');
        location.reload();
    });
}

// Lấy text trạng thái tiếng Việt
function getStatusText(status) {
    const statusMap = {
        'pending': 'Chờ xử lý',
        'confirmed': 'Đã xác nhận',
        'preparing': 'Đang chuẩn bị',
        'shipping': 'Đang giao hàng',
        'delivered': 'Đã giao',
        'cancelled': 'Đã hủy',
        'completed': 'Đã hoàn thành'
    };
    return statusMap[status] || status;
}


// Hiển thị thông báo
function showAlert(type, message) {
    // Xóa alert cũ nếu có
    const existingAlert = document.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }

    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    
    const iconMap = {
        'success': 'check-circle',
        'error': 'exclamation-triangle',
        'info': 'info-circle'
    };
    
    alertDiv.innerHTML = `<i class="fas fa-${iconMap[type] || 'info-circle'}"></i> ${message}`;
    
    document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.header'));
    
    // Tự động ẩn sau 5 giây
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Áp dụng bộ lọc
function applyFilters() {
    const statusFilter = document.getElementById('statusFilter').value;
    const paymentFilter = document.getElementById('paymentFilter').value;
    const fromDate = document.getElementById('fromDate').value;
    const toDate = document.getElementById('toDate').value;

    const tbody = document.getElementById('ordersTableBody');
    const rows = tbody.querySelectorAll('tr');
    let visibleCount = 0;

    rows.forEach(row => {
        const orderId = row.dataset.orderId;
        const order = orders.find(o => o.order_id == orderId);
        
        if (!order) return;

        let show = true;

        // Lọc theo trạng thái
        if (statusFilter && order.status !== statusFilter) {
            show = false;
        }

        // Lọc theo phương thức thanh toán
        if (paymentFilter && order.payment_method !== paymentFilter) {
            show = false;
        }

        // Lọc theo ngày
        if (fromDate) {
            const orderDate = new Date(order.created_at);
            const filterDate = new Date(fromDate);
            if (orderDate < filterDate) {
                show = false;
            }
        }

        if (toDate) {
            const orderDate = new Date(order.created_at);
            const filterDate = new Date(toDate);
            filterDate.setHours(23, 59, 59, 999); // Cuối ngày
            if (orderDate > filterDate) {
                show = false;
            }
        }

        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });

    // Hiển thị số lượng kết quả
    updateFilterResults(visibleCount);
}

// Cập nhật hiển thị kết quả lọc
function updateFilterResults(count) {
    let resultDiv = document.getElementById('filterResults');
    if (!resultDiv) {
        resultDiv = document.createElement('div');
        resultDiv.id = 'filterResults';
        resultDiv.style.cssText = 'margin-top: 10px; padding: 10px; background-color: #f8f9fa; border-radius: 5px; font-size: 14px;';
        document.querySelector('.filters').appendChild(resultDiv);
    }
    
    resultDiv.innerHTML = `<i class="fas fa-filter"></i> Hiển thị <strong>${count}</strong> trong tổng số <strong>${orders.length}</strong> đơn hàng`;
}

// Reset bộ lọc
function resetFilters() {
    document.getElementById('statusFilter').value = '';
    document.getElementById('paymentFilter').value = '';
    document.getElementById('fromDate').value = '';
    document.getElementById('toDate').value = '';
    
    // Hiển thị tất cả rows
    const tbody = document.getElementById('ordersTableBody');
    const rows = tbody.querySelectorAll('tr');
    rows.forEach(row => {
        row.style.display = '';
    });
    
    // Xóa kết quả lọc
    const resultDiv = document.getElementById('filterResults');
    if (resultDiv) {
        resultDiv.remove();
    }
}

// Xuất Excel
function exportToExcel() {
    // Lấy dữ liệu hiển thị hiện tại
    const visibleOrders = [];
    const tbody = document.getElementById('ordersTableBody');
    const rows = tbody.querySelectorAll('tr');
    
    rows.forEach(row => {
        if (row.style.display !== 'none') {
            const orderId = row.dataset.orderId;
            const order = orders.find(o => o.order_id == orderId);
            if (order) {
                visibleOrders.push(order);
            }
        }
    });

    // Tạo CSV content
    let csvContent = "ID,Ngày đặt,Trạng thái,Phương thức thanh toán,Số lượng,Tổng tiền\n";
    
    visibleOrders.forEach(order => {
        const formattedDate = new Date(order.created_at).toLocaleDateString('vi-VN');
        const statusText = getStatusText(order.status);
        const paymentMethod = order.payment_method.replace('_', ' ');
        const totalPrice = parseFloat(order.total_price).toLocaleString('vi-VN');
        
        csvContent += `${order.order_id},"${formattedDate}","${statusText}","${paymentMethod}",${order.total_quantity},"${totalPrice}"\n`;
    });
    
    // Tạo và download file
    const blob = new Blob(['\ufeff' + csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'orders_' + new Date().toISOString().split('T')[0] + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showAlert('success', `Đã xuất ${visibleOrders.length} đơn hàng ra file Excel!`);
}

// Khởi tạo trang
document.addEventListener('DOMContentLoaded', function() {
    if (orders.length > 0) {
        createStatusChart();
        createRevenueChart();
        createMonthlyRevenueChart(); // Nếu có element monthlyRevenueChart
        updateRevenueDisplay();
    }
    
    // Thêm event listener cho Enter key trong các input filter
    const filterInputs = document.querySelectorAll('.filter-group input, .filter-group select');
    filterInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
        });
    });
});

// Thêm nút reset filter
function addResetButton() {
    const filterRow = document.querySelector('.filter-row');
    if (filterRow && !document.getElementById('resetBtn')) {
        const resetGroup = document.createElement('div');
        resetGroup.className = 'filter-group';
        resetGroup.innerHTML = `
            <label>&nbsp;</label>
            <button class="btn btn-secondary" id="resetBtn" onclick="resetFilters()">
                <i class="fas fa-undo"></i> Reset
            </button>
        `;
        filterRow.appendChild(resetGroup);
    }
}

// Gọi thêm nút reset khi trang load
document.addEventListener('DOMContentLoaded', function() {
    addResetButton();
});
</script>

</body>
</html>