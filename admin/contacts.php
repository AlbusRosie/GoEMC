<?php
session_start();

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

include_once(__DIR__ . '/../controllers/ContactsController.php');

$p = new Gcontacts();
$con = $p->getallcontact();

// Lưu dữ liệu vào mảng
$contacts_data = [];
if ($con && $con instanceof PDOStatement) {
    while ($r = $con->fetch()) {
        $contacts_data[] = $r;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý liên hệ - Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header Styles */
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            padding: 12px 20px;
            border-radius: 50px;
            background: rgba(102, 126, 234, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .btn-back:hover {
            background: rgba(102, 126, 234, 0.2);
            transform: translateX(-5px);
        }

        .header h1 {
            font-size: 2.5rem;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 1.1rem;
        }

        /* Statistics Cards */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .stat-card.new::before {
            background: linear-gradient(90deg, #ff6b6b, #feca57);
        }

        .stat-card.resolved::before {
            background: linear-gradient(90deg, #48cab2, #2dd4bf);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }

        .stat-card.new .stat-number {
            color: #ff6b6b;
        }

        .stat-card.resolved .stat-number {
            color: #48cab2;
        }

        .stat-label {
            font-size: 1.1rem;
            color: #666;
            font-weight: 500;
        }

        /* Controls */
        .controls {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
            align-items: center;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: none;
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .search-box input:focus {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .search-box::before {
            content: '\f002';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
        }

        .filter-select {
            padding: 15px 20px;
            border: none;
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            font-size: 1rem;
            outline: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .filter-select:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        /* Table Container */
        .table-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 20px 15px;
            text-align: left;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        td {
            padding: 20px 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            vertical-align: middle;
        }

        tr {
            transition: all 0.3s ease;
        }

        tr:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        .email-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .email-link:hover {
            color: #764ba2;
        }

        .content-preview {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            cursor: help;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-0 {
            background: linear-gradient(45deg, #ff6b6b, #feca57);
            color: white;
        }

        .status-1 {
            background: linear-gradient(45deg, #48cab2, #2dd4bf);
            color: white;
        }

        .actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-resolve {
            background: linear-gradient(45deg, #48cab2, #2dd4bf);
            color: white;
        }

        .btn-resolve:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(72, 202, 178, 0.3);
        }

        .btn-delete {
            background: linear-gradient(45deg, #ff6b6b, #ff5252);
            color: white;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 107, 107, 0.3);
        }

        .btn-approve, .btn-delete {
            color: inherit;
            text-decoration: none;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            padding: 30px;
            background: rgba(255, 255, 255, 0.5);
        }

        .pagination button {
            padding: 12px 24px;
            border: none;
            border-radius: 50px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pagination button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        #pageInfo {
            font-weight: 500;
            color: #666;
            padding: 0 20px;
        }

        /* No Data State */
        .no-data {
            text-align: center;
            padding: 80px 20px;
            color: #666;
        }

        .no-data i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #667eea;
        }

        .no-data h3 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: #333;
        }

        .no-data p {
            font-size: 1.1rem;
            opacity: 0.8;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .controls {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                min-width: auto;
            }

            .actions {
                flex-direction: column;
                gap: 5px;
            }

            .pagination {
                flex-direction: column;
                gap: 15px;
            }
        }

        /* Floating Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <a href="index.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <h1><i class="fas fa-envelope"></i> Quản lý liên hệ</h1>
            <p>Quản lý tin nhắn liên hệ từ khách hàng một cách hiệu quả</p>
        </div>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card float-animation">
                <div class="stat-number"><?php echo count($contacts_data); ?></div>
                <div class="stat-label">Tổng liên hệ</div>
            </div>
            <div class="stat-card new float-animation" style="animation-delay: 0.2s;">
                <div class="stat-number">
                    <?php echo count(array_filter($contacts_data, function($c) { return $c['status'] == 0; })); ?>
                </div>
                <div class="stat-label">Chưa xử lý</div>
            </div>
            <div class="stat-card resolved float-animation" style="animation-delay: 0.4s;">
                <div class="stat-number">
                    <?php echo count(array_filter($contacts_data, function($c) { return $c['status'] == 1; })); ?>
                </div>
                <div class="stat-label">Đã xử lý</div>
            </div>
        </div>

        <!-- Controls -->
        <div class="controls">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Tìm kiếm theo email...">
            </div>
            <select class="filter-select" id="statusFilter">
                <option value="">Tất cả trạng thái</option>
                <option value="0">Chưa xử lý</option>
                <option value="1">Đã xử lý</option>
            </select>
            <select class="filter-select" id="entriesPerPage">
                <option value="10">10 mục/trang</option>
                <option value="25">25 mục/trang</option>
                <option value="50">50 mục/trang</option>
                <option value="100">100 mục/trang</option>
            </select>
        </div>

        <!-- Table -->
        <div class="table-container">
            <div class="table-wrapper">
                <?php if (empty($contacts_data)): ?>
                    <div class="no-data">
                        <i class="fas fa-inbox"></i>
                        <h3>Chưa có liên hệ nào</h3>
                        <p>Hiện tại chưa có tin nhắn liên hệ nào từ khách hàng.</p>
                    </div>
                <?php else: ?>
                    <table id="contactsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                <th>Tiêu đề</th>
                                <th>Nội dung</th>
                                <th>Ngày gửi</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="contactsTableBody">
                            <?php
                                $stt = 0;
                            ?>
                            <?php foreach ($contacts_data as $contact): ?>
                                <tr data-status="<?php echo htmlspecialchars($contact['status']); ?>" 
                                    data-search="<?php echo htmlspecialchars(strtolower($contact['name'] . ' ' . $contact['email'] . ' ' . $contact['title'])); ?>">
                                    <td><?php echo ++$stt ?></td>
                                    <td><?php echo htmlspecialchars($contact['name']); ?></td>
                                    <td>
                                        <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>" class="email-link">
                                            <?php echo htmlspecialchars($contact['email']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($contact['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($contact['title']); ?></td>
                                    <td>
                                        <div class="content-preview" title="<?php echo htmlspecialchars($contact['content']); ?>">
                                            <?php echo htmlspecialchars($contact['content']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($contact['date'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $contact['status']; ?>">
                                            <?php 
                                                if ($contact['status'] == 0) {
                                                    echo 'Chưa xử lý';
                                                } elseif ($contact['status'] == 1) {
                                                    echo 'Đã xử lý';
                                                } else {
                                                    echo 'Không xác định';
                                                }
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <button class="btn btn-resolve">
                                                <i class="fas fa-check"></i> 
                                                <a href="updatecontact.php?id=<?= $contact['id'] ?>" class="btn-approve">Duyệt</a>
                                            </button>
                                            <button class="btn btn-delete">
                                                <i class="fas fa-trash"></i>
                                                <a href="delete-contact.php?id=<?= $contact['id'] ?>" class="btn-delete" onclick="return confirm('Bạn có chắc muốn xóa liên hệ này?')">Xóa</a>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <div class="pagination" id="pagination">
                <button id="prevBtn" onclick="changePage(-1)">
                    <i class="fas fa-chevron-left"></i> Trước
                </button>
                <span id="pageInfo"></span>
                <button id="nextBtn" onclick="changePage(1)">
                    Sau <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let rowsPerPage = 10;
        let filteredRows = [];
        let allRows = [];

        document.addEventListener('DOMContentLoaded', function() {
            allRows = Array.from(document.querySelectorAll('#contactsTableBody tr'));
            filteredRows = [...allRows];
            updateTable();
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filteredRows = allRows.filter(row => {
                const searchData = row.getAttribute('data-search');
                return searchData.includes(searchTerm);
            });
            currentPage = 1;
            updateTable();
        });

        // Filter functionality
        document.getElementById('statusFilter').addEventListener('change', function() {
            const selectedStatus = this.value;
            filteredRows = allRows.filter(row => {
                const searchTerm = document.getElementById('searchInput').value.toLowerCase();
                const searchData = row.getAttribute('data-search');
                const statusMatch = selectedStatus === '' || row.getAttribute('data-status') === selectedStatus;
                const searchMatch = searchData.includes(searchTerm);
                return statusMatch && searchMatch;
            });
            currentPage = 1;
            updateTable();
        });

        // Entries per page
        document.getElementById('entriesPerPage').addEventListener('change', function() {
            rowsPerPage = parseInt(this.value);
            currentPage = 1;
            updateTable();
        });

        function updateTable() {
            const tbody = document.getElementById('contactsTableBody');
            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = startIndex + rowsPerPage;
            const totalPages = Math.ceil(filteredRows.length / rowsPerPage);

            // Hide all rows
            allRows.forEach(row => row.style.display = 'none');

            // Show filtered rows for current page
            filteredRows.slice(startIndex, endIndex).forEach(row => {
                row.style.display = '';
            });

            // Update pagination
            updatePagination(totalPages);
        }

        function updatePagination(totalPages) {
            const pageInfo = document.getElementById('pageInfo');
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');

            pageInfo.textContent = `Trang ${currentPage} / ${totalPages} (${filteredRows.length} mục)`;
            prevBtn.disabled = currentPage === 1;
            nextBtn.disabled = currentPage === totalPages || totalPages === 0;
        }

        function changePage(direction) {
            const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
            const newPage = currentPage + direction;
            
            if (newPage >= 1 && newPage <= totalPages) {
                currentPage = newPage;
                updateTable();
            }
        }

        // Add smooth scrolling effect
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Add loading effect for action buttons
        document.querySelectorAll('.btn').forEach(button => {
            const link = button.querySelector('a');
            if (link) {
                link.addEventListener('click', function() {
                    const icon = button.querySelector('i');
                    if (icon) {
                        icon.className = 'fas fa-spinner fa-spin';
                    }
                });
            }
        });
    </script>
</body>
</html>