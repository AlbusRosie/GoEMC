<?php
session_start();
require_once '../config/database.php';
require_once '../config/config.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Lấy thống kê
$stats = [];

// Tổng số sản phẩm
$stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'");
$stats['total_products'] = $stmt->fetchColumn();

// Tổng số đơn hàng
$stmt = $pdo->query("SELECT COUNT(*) FROM orders");
$stats['total_orders'] = $stmt->fetchColumn();

// Tổng doanh thu
$stmt = $pdo->query("SELECT SUM(total) FROM orders WHERE status = 'completed'");
$stats['total_revenue'] = $stmt->fetchColumn() ?: 0;

// Số liên hệ mới (tạm thời set 0 vì chưa có bảng contacts)
$stats['new_contacts'] = 0;

// Đơn hàng gần đây
$stmt = $pdo->query("SELECT o.*, u.name as user_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
$recent_orders = $stmt->fetchAll();

// Liên hệ gần đây (tạm thời để trống vì chưa có bảng contacts)
$recent_contacts = [];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.25rem;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        .header-container {
            background-color: white;
            border-radius: 12px;
            padding: 20px 30px;
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .left-section {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .greeting {
            font-size: 28px;
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .wave-emoji {
            font-size: 32px;
            animation: wave 2s infinite;
        }

        @keyframes wave {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(20deg); }
            75% { transform: rotate(-20deg); }
        }

        .subtitle {
            font-size: 16px;
            color: #7f8c8d;
            font-weight: 400;
        }

        .right-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .search-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-input {
            width: 300px;
            padding: 12px 16px 12px 50px;
            border: 2px solid #e1e8ed;
            border-radius: 25px;
            font-size: 15px;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
            outline: none;
        }

        .search-input:focus {
            border-color: #3498db;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }

        .search-input::placeholder {
            color: #95a5a6;
            font-style: italic;
        }

        .search-icon {
            position: absolute;
            left: 16px;
            width: 20px;
            height: 20px;
            color: #95a5a6;
            pointer-events: none;
        }

        .notification-icon {
            width: 24px;
            height: 24px;
            color: #7f8c8d;
            cursor: pointer;
            transition: color 0.3s ease;
            position: relative;
        }

        .notification-icon:hover {
            color: #3498db;
        }

        .notification-dot {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 8px;
            height: 8px;
            background-color: #e74c3c;
            border-radius: 50%;
            border: 2px solid white;
        }



        /* .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .stats-card.products {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .stats-card.orders {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .stats-card.revenue {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        .stats-card.contacts {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        } */

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .stats-row {
            display: flex;
            gap: 20px;
            margin-bottom: 2rem;
        }

        .stats-card-wrapper {
            flex: 1;
            min-width: 0; /* Allows flex items to shrink below their content size */
        }

        .mb-4 {
            margin-bottom: 1.5rem;
        }

        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            height: 120px; /* Fixed height for consistency */
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
        }

        .card-body {
            padding: 20px;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 16px 24px;
            border-radius: 16px 16px 0 0;
        }

        .card-header h5 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
        }

        .card-header .fas {
            margin-right: 8px;
        }

        /* Stats Cards Styling */
        .stats-card {
            position: relative;
            overflow: hidden;
        }

        .stats-card.products {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stats-card.orders {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .stats-card.revenue {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .stats-card.contacts {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: -20px;
            right: -20px;
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            opacity: 0.5;
        }

        .stats-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            opacity: 0.3;
        }

        .stats-card .card-body {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 20px;
        }

        .stats-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .stats-text {
            flex: 1;
        }

        .stats-icon {
            flex-shrink: 0;
            margin-left: 15px;
        }

        .text-xs {
            font-size: 0.7rem;
            letter-spacing: 0.5px;
        }

        .font-weight-bold {
            font-weight: 700;
        }

        .text-white-50 {
            color: rgba(255, 255, 255, 0.7);
        }

        .text-white {
            color: white;
        }

        .text-uppercase {
            text-transform: uppercase;
        }

        .mb-1 {
            margin-bottom: 0.25rem;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .h5 {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .fa-2x {
            font-size: 2em;
        }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
            border-top: 1px solid #e9ecef;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .bg-success { background-color: #28a745; color: white; }
        .bg-warning { background-color: #ffc107; color: black; }
        .bg-danger { background-color: #dc3545; color: white; }
        .bg-info { background-color: #17a2b8; color: white; }
        .bg-primary { background-color: #007bff; color: white; }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: #6c757d;
        }

        /* Contact Items */
        .d-flex {
            display: flex;
        }

        .align-items-start {
            align-items: flex-start;
        }

        .flex-shrink-0 {
            flex-shrink: 0;
        }

        .flex-grow-1 {
            flex-grow: 1;
        }

        .ms-3 {
            margin-left: 1rem;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        .rounded-circle {
            border-radius: 50%;
        }

        .d-flex.align-items-center.justify-content-center {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .contact-item {
            padding-bottom: 16px;
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 16px;
        }

        .contact-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .contact-item h6 {
            margin: 0 0 4px 0;
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
        }

        .contact-item p {
            margin: 0 0 4px 0;
            font-size: 12px;
            color: #6c757d;
        }

        .contact-item small {
            font-size: 11px;
            color: #adb5bd;
        }

        .small {
            font-size: 0.875rem;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -15px;
        }

        .col-lg-8 {
            flex: 0 0 66.666667%;
            max-width: 66.666667%;
            padding: 0 15px;
        }

        .col-lg-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
            padding: 0 15px;
        }
         /* Custom scrollbar tooltip for bar chart */
        .bar-group {
            position: relative;
            cursor: pointer;
        }
        .tooltip {
            position: absolute;
            top: -2rem;
            left: 50%;
            transform: translateX(-50%);
            background: #1e293b; /* Tailwind slate-800 */
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            white-space: nowrap;
            pointer-events: none;
            user-select: none;
            opacity: 0;
            transition: opacity 0.15s ease-in-out;
            z-index: 10;
        }
        .bar-group:hover .tooltip {
            opacity: 1;
        }
        .bar {
            transition: opacity 0.3s ease;
        }





        
    </style>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">
                            <i class="fas fa-tree me-2"></i>Admin Panel
                        </h4>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="products.php">
                                <i class="fas fa-box me-2"></i>Sản phẩm
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="categories.php">
                                <i class="fas fa-tags me-2"></i>Danh mục
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="detail-categories.php">
                                <i class="fas fa-list me-2"></i>Chi tiết danh mục
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="orders.php">
                                <i class="fas fa-shopping-cart me-2"></i>Đơn hàng
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users me-2"></i>Người dùng
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="contacts.php">
                                <i class="fas fa-envelope me-2"></i>Liên hệ
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog me-2"></i>Cài đặt
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link" href="../index.php">
                                <i class="fas fa-home me-2"></i>Về trang chủ
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                
                <!-- <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Xuất báo cáo</button>
                        </div>
                    </div>
                </div> -->

                <div class="header-container">
                    <div class="left-section">
                        <div class="greeting">
                            Hello Iniesta
                            <span class="wave-emoji">👋</span>
                        </div>
                        <div class="subtitle">
                            Let's learn something new today!
                        </div>
                    </div>

                    <div class="right-section">
                        <div class="search-container">
                            <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <input 
                                type="text" 
                                class="search-input" 
                                placeholder="Search anything here..."
                            >
                        </div>

                        <div style="position: relative;">
                            <svg class="notification-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <div class="notification-dot"></div>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="dashboard-container">
                    <!-- Stats Cards Row -->
                    <div class="stats-row">
                        <div class="stats-card-wrapper">
                            <div class="card stats-card products border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="stats-content">
                                        <div class="stats-text">
                                            <div class="text-xs font-weight-bold text-white-50 text-uppercase mb-1">
                                                Sản phẩm
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-white">
                                                <?php echo number_format($stats['total_products']); ?>
                                            </div>
                                        </div>
                                        <div class="stats-icon">
                                            <i class="fas fa-box fa-2x text-white-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="stats-card-wrapper">
                            <div class="card stats-card orders border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="stats-content">
                                        <div class="stats-text">
                                            <div class="text-xs font-weight-bold text-white-50 text-uppercase mb-1">
                                                Đơn hàng
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-white">
                                                <?php echo number_format($stats['total_orders']); ?>
                                            </div>
                                        </div>
                                        <div class="stats-icon">
                                            <i class="fas fa-shopping-cart fa-2x text-white-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="stats-card-wrapper">
                            <div class="card stats-card revenue border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="stats-content">
                                        <div class="stats-text">
                                            <div class="text-xs font-weight-bold text-white-50 text-uppercase mb-1">
                                                Doanh thu
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-white">
                                                <?php echo formatPrice($stats['total_revenue']); ?>
                                            </div>
                                        </div>
                                        <div class="stats-icon">
                                            <i class="fas fa-dollar-sign fa-2x text-white-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="stats-card-wrapper">
                            <div class="card stats-card contacts border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="stats-content">
                                        <div class="stats-text">
                                            <div class="text-xs font-weight-bold text-white-50 text-uppercase mb-1">
                                                Liên hệ mới
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-white">
                                                <?php echo number_format($stats['new_contacts']); ?>
                                            </div>
                                        </div>
                                        <div class="stats-icon">
                                            <i class="fas fa-envelope fa-2x text-white-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders & Contacts -->
                    <div class="row">
                        <div class="col-lg-8 mb-4">
                            <div class="card shadow-sm">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-shopping-cart me-2"></i>Đơn hàng gần đây
                                    </h5>
                                </div>
                                <div class="card-body">
                                <p class="text-muted text-center">Chưa có đơn hàng nào</p>

                                    <?php if(empty($recent_orders)): ?>
                                    <p class="text-muted text-center">Chưa có đơn hàng nào</p>
                                    <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Mã đơn</th>
                                                    <th>Khách hàng</th>
                                                    <th>Tổng tiền</th>
                                                    <th>Trạng thái</th>
                                                    <th>Ngày tạo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($recent_orders as $order): ?>
                                                <tr>
                                                    <td>#<?php echo $order['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($order['guest_name'] ?: $order['user_name'] ?: 'Khách'); ?></td>
                                                    <td><?php echo formatPrice($order['total']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo getStatusColor($order['status']); ?>">
                                                            <?php echo getStatusText($order['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-center">
                                        <a href="orders.php" class="btn btn-primary btn-sm">Xem tất cả</a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 mb-4">
                            <div class="card shadow-sm">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-envelope me-2"></i>Liên hệ gần đây
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-start mb-3 contact-item">
                                        <div class="flex-shrink-0">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">Phạm Thị D</h6>
                                            <p class="text-muted small mb-1">Hỏi về sản phẩm</p>
                                            <small class="text-muted">17/07/2025 15:20</small>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex align-items-start mb-3 contact-item">
                                        <div class="flex-shrink-0">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">Hoàng Văn E</h6>
                                            <p class="text-muted small mb-1">Góp ý dịch vụ</p>
                                            <small class="text-muted">17/07/2025 12:30</small>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center">
                                        <a href="#" class="btn btn-primary btn-sm">Xem tất cả</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="max-w-5xl w-full bg-white rounded-xl shadow-md p-6 grid grid-cols-1 md:grid-cols-2 gap-8 font-sans text-gray-900">
    
                    <!-- Hours Spent -->
                    <section aria-label="Hours spent chart" class="flex flex-col gap-4">
                    <h2 class="text-lg font-semibold">Hours Spent</h2>
                    <div class="flex items-center justify-between text-xs text-gray-500 select-none mb-3">
                        <div class="flex items-center space-x-6">
                        <div class="flex items-center">
                            <span class="legend-dot" style="background:#fdecbf;"></span>
                            <span>Study</span>
                        </div>
                        <div class="flex items-center">
                            <span class="legend-dot" style="background:#f97316;"></span>
                            <span>Exams</span>
                        </div>
                        </div>
                        <select aria-label="Select duration" class="text-xs border border-gray-300 rounded-md py-1 px-2 cursor-pointer focus:outline-none focus:ring-1 focus:ring-orange-400">
                        <option>Monthly</option>
                        <option>Weekly</option>
                        <option>Yearly</option>
                        </select>
                    </div>

                    <!-- Vertical Y axis labels -->
                    <div class="relative pt-3">
                        <div class="absolute left-0 top-0 h-full flex flex-col justify-between text-gray-400 text-xs font-mono select-none" style="height:150px;">
                        <span>80 Hr</span>
                        <span>60 Hr</span>
                        <span>40 Hr</span>
                        <span>20 Hr</span>
                        <span>0 Hr</span>
                        </div>
                        <!-- Bar Chart -->
                        <div class="ml-14 flex justify-between items-end h-[150px] gap-6">
                        <!-- Each bar group -->
                        <div class="bar-group relative flex flex-col items-center gap-1">
                            <div class="relative w-8 flex flex-col justify-end rounded-md overflow-visible">
                            <div class="bar bg-orange-500 rounded-b-md" style="height:32px;" aria-label="Jan Exams 32 Hours"></div>
                            <div class="bar bg-yellow-200 rounded-t-md" style="height:55px;" aria-label="Jan Study 55 Hours"></div>
                            </div>
                            <div class="tooltip">55 Hr<br>32 Hr</div>
                            <div class="text-xs text-gray-700 select-none">Jan</div>
                        </div>
                        <div class="bar-group relative flex flex-col items-center gap-1">
                            <div class="relative w-8 flex flex-col justify-end rounded-md overflow-visible">
                            <div class="bar bg-orange-500 rounded-b-md" style="height:12px;" aria-label="Feb Exams 12 Hours"></div>
                            <div class="bar bg-yellow-200 rounded-t-md" style="height:35px;" aria-label="Feb Study 35 Hours"></div>
                            </div>
                            <div class="tooltip">35 Hr<br>12 Hr</div>
                            <div class="text-xs text-gray-700 select-none">Feb</div>
                        </div>
                        <div class="bar-group relative flex flex-col items-center gap-1">
                            <div class="relative w-8 flex flex-col justify-end rounded-md overflow-visible">
                            <div class="bar bg-orange-500 rounded-b-md" style="height:64px;" aria-label="Mar Exams 64 Hours"></div>
                            <div class="bar bg-yellow-200 rounded-t-md" style="height:22px;" aria-label="Mar Study 22 Hours"></div>
                            </div>
                            <div class="tooltip">22 Hr<br>64 Hr</div>
                            <div class="text-xs text-gray-700 select-none">Mar</div>
                        </div>
                        <div class="bar-group relative flex flex-col items-center gap-1">
                            <div class="relative w-8 flex flex-col justify-end rounded-md overflow-visible">
                            <div class="bar bg-orange-500 rounded-b-md" style="height:28px;" aria-label="Apr Exams 28 Hours"></div>
                            <div class="bar bg-yellow-200 rounded-t-md" style="height:55px;" aria-label="Apr Study 55 Hours"></div>
                            </div>
                            <div class="tooltip">55 Hr<br>28 Hr</div>
                            <div class="text-xs text-gray-700 select-none">Apr</div>
                        </div>
                        <div class="bar-group relative flex flex-col items-center gap-1">
                            <div class="relative w-8 flex flex-col justify-end rounded-md overflow-visible">
                            <div class="bar bg-orange-500 rounded-b-md" style="height:14px;" aria-label="May Exams 14 Hours"></div>
                            <div class="bar bg-yellow-200 rounded-t-md" style="height:11px;" aria-label="May Study 11 Hours"></div>
                            </div>
                            <div class="tooltip">11 Hr<br>14 Hr</div>
                            <div class="text-xs text-gray-700 select-none">May</div>
                        </div>
                        </div>
                    </div>
                    </section>

                    <!-- Performance -->
                    <section aria-label="Performance widget" class="flex flex-col gap-4">
                    <h2 class="text-lg font-semibold">Performance</h2>
                    <div class="flex items-center justify-between text-xs text-gray-500 select-none mb-3">
                        <div class="flex items-center">
                        <span class="legend-dot" style="background:#2dd4bf;"></span>
                        <span>Point Progress</span>
                        </div>
                        <select aria-label="Select duration" class="text-xs border border-gray-300 rounded-md py-1 px-2 cursor-pointer focus:outline-none focus:ring-1 focus:ring-teal-400">
                        <option>Monthly</option>
                        <option>Weekly</option>
                        <option>Yearly</option>
                        </select>
                    </div>
                    <div class="relative w-full max-w-sm mx-auto p-6 bg-white rounded-lg shadow-inner shadow-gray-200">
                        <canvas id="gaugeCanvas" width="280" height="160" aria-label="Performance gauge showing point progress"></canvas>
                        <div class="text-center mt-6 select-none">
                        <p class="text-sm text-gray-500">Your Point:</p>
                        <p class="text-2xl font-semibold text-gray-900">8.966</p>
                        <p class="text-xs text-teal-500 underline cursor-pointer mt-1">5th in Leaderboard</p>
                        </div>
                    </div>
                    </section>
                </div>


                
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../assets/js/main.js"></script>
    <script>
    // Draw gauge on canvas
    const canvas = document.getElementById('gaugeCanvas');
    const ctx = canvas.getContext('2d');
    const width = canvas.width;
    const height = canvas.height;
    const centerX = width / 2;
    const centerY = height * 1.1;
    const radius = 110;

    // Clear canvas
    ctx.clearRect(0, 0, width, height);

    // Color arcs: teal for progress, light gray for remainder
    const startAngle = Math.PI; // 180 deg (left)
    const endAngle = 0;         // 0 deg (right)
    const progressPercent = 0.68; // 68% progress of arc
    
    // Draw background arc (light)
    ctx.beginPath();
    ctx.lineWidth = 12;
    ctx.strokeStyle = '#fbe8db'; // light beige
    ctx.lineCap = 'round';
    ctx.arc(centerX, centerY, radius, startAngle, endAngle, false);
    ctx.stroke();

    // Draw progress arc (teal)
    ctx.beginPath();
    ctx.lineWidth = 12;
    ctx.strokeStyle = '#2dd4bf'; // teal
    ctx.lineCap = 'round';
    ctx.arc(centerX, centerY, radius, startAngle, startAngle + (endAngle - startAngle)*progressPercent, false);
    ctx.stroke();

    // Draw needle pivot circle
    ctx.beginPath();
    ctx.fillStyle = '#f97316'; // orange
    ctx.shadowColor = 'rgba(0,0,0,0.15)';
    ctx.shadowBlur = 6;
    ctx.arc(centerX, centerY, 10, 0, 2 * Math.PI, false);
    ctx.fill();

    // Draw needle pointing at ~68% of arc
    const needleValue = progressPercent;
    const needleAngle = startAngle + (endAngle - startAngle)*needleValue;
    const needleLength = radius - 20;
    const needleX = centerX + needleLength * Math.cos(needleAngle);
    const needleY = centerY + needleLength * Math.sin(needleAngle);
    ctx.beginPath();
    ctx.lineWidth = 3;
    ctx.strokeStyle = '#f97316'; // orange
    ctx.moveTo(centerX, centerY);
    ctx.lineTo(needleX, needleY);
    ctx.stroke();

    // Draw tick marks
    const ticks = 10;
    ctx.lineWidth = 2;
    ctx.strokeStyle = '#e5e7eb'; // gray-200
    for (let i = 0; i <= ticks; i++) {
      const tickAngle = startAngle + (endAngle - startAngle) * (i / ticks);
      const innerRadius = radius - 8;
      const outerRadius = radius + 4;
      const x1 = centerX + innerRadius * Math.cos(tickAngle);
      const y1 = centerY + innerRadius * Math.sin(tickAngle);
      const x2 = centerX + outerRadius * Math.cos(tickAngle);
      const y2 = centerY + outerRadius * Math.sin(tickAngle);
      ctx.beginPath();
      ctx.moveTo(x1, y1);
      ctx.lineTo(x2, y2);
      ctx.stroke();
    }
  </script>

</body>
</html>

<?php
function getStatusColor($status) {
    return match($status) {
        'pending' => 'warning',
        'preparing' => 'info',
        'ready' => 'primary',
        'served' => 'success',
        'completed' => 'success',
        'cancelled' => 'danger',
        default => 'secondary'
    };
}

function getStatusText($status) {
    return match($status) {
        'pending' => 'Chờ xác nhận',
        'preparing' => 'Đang chuẩn bị',
        'ready' => 'Sẵn sàng',
        'served' => 'Đã phục vụ',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
        default => 'Không xác định'
    };
}


?> 