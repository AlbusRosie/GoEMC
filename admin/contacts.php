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
    <link rel="stylesheet" href="../assets/css/contactsadmin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <a href="index.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <h1><i class="fas fa-envelope"></i> Quản lý liên hệ</h1>
            <p>Quản lý tin nhắn liên hệ từ khách hàng</p>
        </div>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($contacts_data); ?></div>
                <div class="stat-label">Tổng liên hệ</div>
            </div>
            <div class="stat-card new">
                <div class="stat-number">
                    <?php echo count(array_filter($contacts_data, function($c) { return $c['status'] == 0; })); ?>
                </div>
                <div class="stat-label">Chưa xử lý</div>
            </div>
            <div class="stat-card resolved">
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
                                            
                                            <button class="btn btn-resolve" ">
                                                <i class="fas fa-check"></i> 
                                                <a href="updatecontact.php?id=<?= $contact['id'] ?>" class="btn-approve">Duyệt</a>
                                            </button>
                                         
                                            <button class="btn btn-delete" >
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
    </script>
</body>
</html>