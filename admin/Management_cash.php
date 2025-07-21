<?php
session_start();

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../controllers/ManagementCashController.php';
$p = new GQLcash();
$con = $p->getallcash();

// Dữ liệu đơn hàng
$orders_data = [];
if ($con && $con instanceof PDOStatement) {
    while ($r = $con->fetch()) {
        $orders_data[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý dòng tiền - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            --card-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 2rem 2rem;
            box-shadow: var(--card-shadow);
        }

        .main-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin: 0;
        }

        .stats-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--card-shadow);
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .stats-icon.total { background: var(--success-gradient); }
        .stats-icon.pending { background: var(--warning-gradient); }
        .stats-icon.completed { background: var(--info-gradient); }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }

        .stats-label {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin: 0;
        }

        .data-table {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: var(--card-shadow);
            border: none;
        }

        .table-modern {
            border: none;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .table-modern thead th {
            background: var(--dark-gradient);
            color: white;
            border: none;
            padding: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table-modern tbody td {
            padding: 1rem;
            border: none;
            border-bottom: 1px solid #f8f9fa;
            vertical-align: middle;
        }

        .table-modern tbody tr:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transform: scale(1.01);
            transition: all 0.3s ease;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-paid { background: var(--success-gradient); color: white; }
        .badge-pending { background: var(--warning-gradient); color: white; }
        .badge-failed { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%); color: white; }
        .badge-refunded { background: var(--dark-gradient); color: white; }

        .btn-modern {
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .btn-detail {
            background: var(--info-gradient);
            color: white;
        }

        .btn-back {
            background: var(--dark-gradient);
            color: white;
        }

        .total-summary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 0.5rem;
            text-align: center;
            margin-top: 1rem;
        }

        .total-summary h4 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #7f8c8d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #bdc3c7;
        }

        .search-filter {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--card-shadow);
        }

        .filter-row {
            border-top: 1px solid #e9ecef;
            padding-top: 1rem;
            margin-top: 1rem;
        }

        .date-filter-label {
            display: flex;
            align-items: center;
            font-weight: 600;
            color: #495057;
            margin-bottom: 0;
        }

        .btn-outline-primary {
            border-color: #667eea;
            color: #667eea;
        }

        .btn-outline-primary:hover {
            background-color: #667eea;
            border-color: #667eea;
        }

        .form-control, .form-select {
            border-radius: 0.5rem;
            border: 2px solid #e9ecef;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .order-id {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            color: #667eea;
        }

        .amount-highlight {
            font-size: 1.1rem;
            font-weight: 700;
            color: #27ae60;
        }

        @media (max-width: 768px) {
            .main-header h1 {
                font-size: 2rem;
            }
            
            .stats-card {
                margin-bottom: 1rem;
            }
            
            .table-responsive {
                border-radius: 0.5rem;
            }
        }

        .fade-in {
            animation: fadeIn 0.8s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="main-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-chart-line me-3"></i>Quản lý dòng tiền</h1>
                    <p class="mb-0 opacity-75">Theo dõi và quản lý tài chính doanh nghiệp</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="text-white">
                        <i class="fas fa-calendar-alt me-2"></i>
                        <?= date('d/m/Y') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container fade-in">
        <?php if (empty($orders_data)): ?>
            <!-- Empty State -->
            <div class="data-table">
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>Chưa có đơn hàng nào</h3>
                    <p>Hiện tại chưa có đơn hàng hoàn tất nào trong hệ thống.</p>
                    <a href="index.php" class="btn btn-modern btn-back">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại Dashboard
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <?php 
                $total_amount = 0;
                $total_orders = count($orders_data);
                $paid_orders = 0;
                $pending_orders = 0;
                
                foreach ($orders_data as $order) {
                    $total_amount += $order['total_amount'];
                    if ($order['payment_status'] === 'paid') $paid_orders++;
                    if ($order['payment_status'] === 'pending') $pending_orders++;
                }
                ?>
                
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="stats-icon total">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <h3 class="stats-number"><?= number_format($total_amount, 0, ',', '.') ?>đ</h3>
                        <p class="stats-label">Tổng doanh thu</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="stats-icon completed">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="stats-number"><?= $paid_orders ?></h3>
                        <p class="stats-label">Đơn đã thanh toán</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="stats-icon pending">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3 class="stats-number"><?= $pending_orders ?></h3>
                        <p class="stats-label">Đơn chờ thanh toán</p>
                    </div>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="search-filter">
                <div class="row align-items-center mb-3">
                    <div class="col-md-2">
                        <a href="index.php" class="btn btn-modern btn-back">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                        </a>
                    </div>
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" placeholder="Tìm kiếm mã đơn hàng..." id="searchInput">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <select class="form-select" id="paymentFilter">
                            <option value="">Tất cả phương thức thanh toán</option>
                            <option value="cash">💵 Tiền mặt</option>
                            <option value="card">💳 Thẻ ngân hàng</option>
                            <option value="bank_transfer">🏦 Chuyển khoản ngân hàng</option>
                            <option value="momo">📱 Ví MoMo</option>
                            <option value="zalopay">⚡ ZaloPay</option>
                        </select>
                    </div>
                </div>
                
                <!-- Date Filter Row -->
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <label class="form-label mb-0 text-muted">
                            <i class="fas fa-calendar-alt me-1"></i>Lọc theo ngày:
                        </label>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="dateFrom" placeholder="Từ ngày">
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control" id="dateTo" placeholder="Đến ngày">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-primary w-100" id="clearDateFilter">
                            <i class="fas fa-times me-1"></i>Xóa lọc
                        </button>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" id="quickDateFilter">
                            <option value="">Chọn nhanh</option>
                            <option value="today">Hôm nay</option>
                            <option value="yesterday">Hôm qua</option>
                            <option value="thisweek">Tuần này</option>
                            <option value="lastweek">Tuần trước</option>
                            <option value="thismonth">Tháng này</option>
                            <option value="lastmonth">Tháng trước</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="data-table">
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Mã đơn hàng</th>
                                <th>Ngày tạo</th>
                                <th>Phương thức</th>
                                <th>Trạng thái</th>
                                <th>Số tiền</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="ordersTableBody">
                            <?php 
                            $stt = 1;
                            foreach ($orders_data as $row): 
                            ?>
                            <tr data-order-id="<?= strtolower($row['order_id']) ?>" 
                                data-payment="<?= strtolower($row['payment_method']) ?>"
                                data-date="<?= date('Y-m-d', strtotime($row['created_at'])) ?>">
                                <td><?= $stt++ ?></td>
                                <td>
                                    <span class="order-id">#<?= htmlspecialchars($row['order_id']) ?></span>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= date('d/m/Y', strtotime($row['created_at'])) ?></strong><br>
                                        <small class="text-muted"><?= date('H:i', strtotime($row['created_at'])) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $payment_icons = [
                                        'cash' => 'fas fa-money-bill-wave',
                                        'card' => 'fas fa-credit-card',
                                        'bank_transfer' => 'fas fa-university',
                                        'momo' => 'fas fa-mobile-alt',
                                        'zalopay' => 'fas fa-bolt'
                                    ];
                                    
                                    $payment_names = [
                                        'cash' => 'Tiền mặt',
                                        'card' => 'Thẻ ngân hàng',
                                        'bank_transfer' => 'Chuyển khoản',
                                        'momo' => 'Ví MoMo',
                                        'zalopay' => 'ZaloPay'
                                    ];
                                    
                                    $method = $row['payment_method'];
                                    $icon = $payment_icons[$method] ?? 'fas fa-question-circle';
                                    $name = $payment_names[$method] ?? 'Không rõ';
                                    ?>
                                    <i class="<?= $icon ?> me-2"></i>
                                    <?= $name ?>
                                </td>
                                <td>
                                    <?php 
                                    switch ($row['payment_status']) {
                                        case 'paid':
                                            echo '<span class="status-badge badge-paid">Đã thanh toán</span>';
                                            break;
                                        case 'pending':
                                            echo '<span class="status-badge badge-pending">Chờ thanh toán</span>';
                                            break;
                                        case 'failed':
                                            echo '<span class="status-badge badge-failed">Thất bại</span>';
                                            break;
                                        case 'refunded':
                                            echo '<span class="status-badge badge-refunded">Đã hoàn tiền</span>';
                                            break;
                                        default:
                                            echo '<span class="status-badge badge-refunded">Không xác định</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="amount-highlight">
                                        <?= number_format($row['total_amount'], 0, ',', '.') ?>đ
                                    </span>
                                </td>
                                <td>
                                    <a href="detail_orderdone.php?id=<?= $row['order_id'] ?>" 
                                       class="btn btn-modern btn-detail btn-sm">
                                        <i class="fas fa-eye me-1"></i>Chi tiết
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Total Summary -->
                <div class="total-summary">
                    <h4>
                        <i class="fas fa-calculator me-2"></i>
                        Tổng doanh thu: <?= number_format($total_amount, 0, ',', '.') ?>đ
                    </h4>
                    <small>Từ <?= $total_orders ?> đơn hàng</small>
                </div>
            </div>
        <?php endif; ?>

        
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search and Filter Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const paymentFilter = document.getElementById('paymentFilter');
            const dateFrom = document.getElementById('dateFrom');
            const dateTo = document.getElementById('dateTo');
            const clearDateFilter = document.getElementById('clearDateFilter');
            const quickDateFilter = document.getElementById('quickDateFilter');
            const tableBody = document.getElementById('ordersTableBody');
            
            // Quick date filter functions
            function getDateRange(option) {
                const today = new Date();
                const yesterday = new Date(today);
                yesterday.setDate(yesterday.getDate() - 1);
                
                switch(option) {
                    case 'today':
                        return { from: today, to: today };
                    case 'yesterday':
                        return { from: yesterday, to: yesterday };
                    case 'thisweek':
                        const thisWeekStart = new Date(today);
                        thisWeekStart.setDate(today.getDate() - today.getDay() + 1);
                        return { from: thisWeekStart, to: today };
                    case 'lastweek':
                        const lastWeekEnd = new Date(today);
                        lastWeekEnd.setDate(today.getDate() - today.getDay());
                        const lastWeekStart = new Date(lastWeekEnd);
                        lastWeekStart.setDate(lastWeekEnd.getDate() - 6);
                        return { from: lastWeekStart, to: lastWeekEnd };
                    case 'thismonth':
                        const thisMonthStart = new Date(today.getFullYear(), today.getMonth(), 1);
                        return { from: thisMonthStart, to: today };
                    case 'lastmonth':
                        const lastMonthStart = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                        const lastMonthEnd = new Date(today.getFullYear(), today.getMonth(), 0);
                        return { from: lastMonthStart, to: lastMonthEnd };
                    default:
                        return null;
                }
            }
            
            function formatDateForInput(date) {
                return date.toISOString().split('T')[0];
            }
            
            function filterTable() {
                const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
                const paymentValue = paymentFilter ? paymentFilter.value : '';
                const fromDate = dateFrom ? dateFrom.value : '';
                const toDate = dateTo ? dateTo.value : '';
                
                if (tableBody) {
                    const rows = tableBody.querySelectorAll('tr');
                    let visibleCount = 0;
                    let visibleTotal = 0;
                    
                    rows.forEach(row => {
                        const orderId = row.getAttribute('data-order-id') || '';
                        const payment = row.getAttribute('data-payment') || '';
                        const dateStr = row.getAttribute('data-date') || '';
                        
                        const matchesSearch = orderId.includes(searchTerm);
                        const matchesPayment = !paymentValue || payment === paymentValue;
                        
                        // Date filtering
                        let matchesDate = true;
                        if (fromDate || toDate) {
                            const rowDate = new Date(dateStr);
                            if (fromDate && rowDate < new Date(fromDate)) {
                                matchesDate = false;
                            }
                            if (toDate && rowDate > new Date(toDate)) {
                                matchesDate = false;
                            }
                        }
                        
                        if (matchesSearch && matchesPayment && matchesDate) {
                            row.style.display = '';
                            visibleCount++;
                            
                            // Calculate total for visible rows
                            const amountCell = row.querySelector('.amount-highlight');
                            if (amountCell) {
                                const amount = parseInt(amountCell.textContent.replace(/[^\d]/g, ''));
                                visibleTotal += amount;
                            }
                        } else {
                            row.style.display = 'none';
                        }
                    });
                    
                    // Update total summary
                    updateTotalSummary(visibleTotal, visibleCount);
                }
            }
            
            function updateTotalSummary(total, count) {
                const totalSummary = document.querySelector('.total-summary h4');
                const totalSubtext = document.querySelector('.total-summary small');
                
                if (totalSummary) {
                    totalSummary.innerHTML = `
                        <i class="fas fa-calculator me-2"></i>
                        Tổng doanh thu: ${total.toLocaleString('vi-VN')}đ
                    `;
                }
                
                if (totalSubtext) {
                    totalSubtext.textContent = `Từ ${count} đơn hàng`;
                }
            }
            
            // Event listeners
            if (searchInput) searchInput.addEventListener('input', filterTable);
            if (paymentFilter) paymentFilter.addEventListener('change', filterTable);
            if (dateFrom) dateFrom.addEventListener('change', filterTable);
            if (dateTo) dateTo.addEventListener('change', filterTable);
            
            // Clear date filter
            if (clearDateFilter) {
                clearDateFilter.addEventListener('click', function() {
                    if (dateFrom) dateFrom.value = '';
                    if (dateTo) dateTo.value = '';
                    if (quickDateFilter) quickDateFilter.value = '';
                    filterTable();
                });
            }
            
            // Quick date filter
            if (quickDateFilter) {
                quickDateFilter.addEventListener('change', function() {
                    const range = getDateRange(this.value);
                    if (range) {
                        if (dateFrom) dateFrom.value = formatDateForInput(range.from);
                        if (dateTo) dateTo.value = formatDateForInput(range.to);
                        filterTable();
                    }
                });
            }
            
            // Animate table rows on load
            const rows = document.querySelectorAll('.table-modern tbody tr');
            rows.forEach((row, index) => {
                setTimeout(() => {
                    row.style.opacity = '0';
                    row.style.transform = 'translateY(20px)';
                    row.style.transition = 'all 0.3s ease';
                    
                    setTimeout(() => {
                        row.style.opacity = '1';
                        row.style.transform = 'translateY(0)';
                    }, 50);
                }, index * 50);
            });
        });

        // Add hover effect to stats cards
        document.querySelectorAll('.stats-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>