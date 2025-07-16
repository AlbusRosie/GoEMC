<?php
// Include admin header
include __DIR__ . '/../../../admin/includes/header.php';
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý đơn hàng</h1>
        <div class="btn-group">
            <a href="index.php?page=admin/orders/export" class="btn btn-sm btn-success">
                <i class="fas fa-file-excel fa-sm text-white-50"></i> Xuất Excel
            </a>
            <a href="index.php?page=admin/orders/print" class="btn btn-sm btn-info">
                <i class="fas fa-print fa-sm text-white-50"></i> In báo cáo
            </a>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Danh sách đơn hàng</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                             aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Lọc theo trạng thái:</div>
                            <a class="dropdown-item" href="?page=admin/orders&status=pending">Chờ xử lý</a>
                            <a class="dropdown-item" href="?page=admin/orders&status=processing">Đang xử lý</a>
                            <a class="dropdown-item" href="?page=admin/orders&status=completed">Hoàn thành</a>
                            <a class="dropdown-item" href="?page=admin/orders&status=cancelled">Đã hủy</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="?page=admin/orders">Tất cả</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($orders)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Mã đơn hàng</th>
                                        <th>Khách hàng</th>
                                        <th>Sản phẩm</th>
                                        <th>Tổng tiền</th>
                                        <th>Phương thức</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày đặt</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>
                                                <strong>#<?php echo $order['id']; ?></strong>
                                                <?php if ($order['is_guest']): ?>
                                                    <br><small class="text-muted">Khách</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($order['user_name'] ?? $order['guest_name']); ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($order['user_email'] ?? $order['guest_email']); ?>
                                                </small>
                                                <?php if ($order['user_phone'] ?? $order['guest_phone']): ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars($order['user_phone'] ?? $order['guest_phone']); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $orderItems = $order['items'] ?? [];
                                                $itemCount = count($orderItems);
                                                if ($itemCount > 0) {
                                                    echo '<strong>' . $itemCount . ' sản phẩm</strong><br>';
                                                    foreach (array_slice($orderItems, 0, 2) as $item) {
                                                        echo '<small>' . htmlspecialchars($item['product_name']) . ' (x' . $item['quantity'] . ')</small><br>';
                                                    }
                                                    if ($itemCount > 2) {
                                                        echo '<small class="text-muted">... và ' . ($itemCount - 2) . ' sản phẩm khác</small>';
                                                    }
                                                } else {
                                                    echo '<span class="text-muted">Không có sản phẩm</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <strong class="text-primary">
                                                    <?php echo number_format($order['total'], 0, ',', '.'); ?>₫
                                                </strong>
                                                <?php if ($order['discount'] && $order['discount'] > 0): ?>
                                                    <br>
                                                    <small class="text-success">
                                                        Giảm: <?php echo number_format($order['discount'], 0, ',', '.'); ?>₫
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $paymentMethod = $order['payment_method'] ?? 'cod';
                                                switch ($paymentMethod) {
                                                    case 'cod':
                                                        echo '<span class="badge badge-warning">Tiền mặt</span>';
                                                        break;
                                                    case 'bank':
                                                        echo '<span class="badge badge-info">Chuyển khoản</span>';
                                                        break;
                                                    case 'momo':
                                                        echo '<span class="badge badge-danger">MoMo</span>';
                                                        break;
                                                    default:
                                                        echo '<span class="badge badge-secondary">Khác</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = '';
                                                $statusText = '';
                                                switch ($order['status']) {
                                                    case 'pending':
                                                        $statusClass = 'badge-warning';
                                                        $statusText = 'Chờ xử lý';
                                                        break;
                                                    case 'processing':
                                                        $statusClass = 'badge-info';
                                                        $statusText = 'Đang xử lý';
                                                        break;
                                                    case 'shipped':
                                                        $statusClass = 'badge-primary';
                                                        $statusText = 'Đã gửi hàng';
                                                        break;
                                                    case 'completed':
                                                        $statusClass = 'badge-success';
                                                        $statusText = 'Hoàn thành';
                                                        break;
                                                    case 'cancelled':
                                                        $statusClass = 'badge-danger';
                                                        $statusText = 'Đã hủy';
                                                        break;
                                                    default:
                                                        $statusClass = 'badge-secondary';
                                                        $statusText = 'Không xác định';
                                                }
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                            </td>
                                            <td>
                                                <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="index.php?page=admin/orders/view&id=<?php echo $order['id']; ?>" 
                                                       class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="index.php?page=admin/orders/edit&id=<?php echo $order['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="index.php?page=admin/orders/print&id=<?php echo $order['id']; ?>" 
                                                       class="btn btn-sm btn-outline-secondary" target="_blank" title="In đơn hàng">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                    <?php if ($order['status'] !== 'completed' && $order['status'] !== 'cancelled'): ?>
                                                        <a href="index.php?page=admin/orders/delete&id=<?php echo $order['id']; ?>" 
                                                           class="btn btn-sm btn-outline-danger btn-delete" title="Xóa">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if (isset($totalPages) && $totalPages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <?php if ($currentPage > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=admin/orders&p=<?php echo $currentPage - 1; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?>">Trước</a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                        <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=admin/orders&p=<?php echo $i; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($currentPage < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=admin/orders&p=<?php echo $currentPage + 1; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?>">Sau</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có đơn hàng nào</h5>
                            <p class="text-muted">Khi có đơn hàng mới, chúng sẽ xuất hiện ở đây.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// DataTable initialization
$(document).ready(function() {
    $('#dataTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Vietnamese.json"
        },
        "pageLength": 25,
        "order": [[6, "desc"]]
    });
});
</script>

<?php
// Include admin footer
include __DIR__ . '/../../../admin/includes/footer.php';
?> 