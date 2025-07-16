<?php
// Include admin header
include __DIR__ . '/../../../admin/includes/header.php';
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý danh mục</h1>
        <a href="index.php?page=admin/categories/create" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Thêm danh mục mới
        </a>
    </div>

    <!-- Content Row -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Danh sách danh mục</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                             aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Tùy chọn:</div>
                            <a class="dropdown-item" href="index.php?page=admin/categories/export">Xuất Excel</a>
                            <a class="dropdown-item" href="index.php?page=admin/categories/import">Nhập Excel</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="index.php?page=admin/categories/bulk-delete">Xóa hàng loạt</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($categories)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll">
                                        </th>
                                        <th>ID</th>
                                        <th>Tên danh mục</th>
                                        <th>Mô tả</th>
                                        <th>Số sản phẩm</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="category-checkbox" value="<?php echo $category['id']; ?>">
                                            </td>
                                            <td><?php echo $category['id']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                                <?php if ($category['slug']): ?>
                                                    <br>
                                                    <small class="text-muted">Slug: <?php echo $category['slug']; ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($category['description']): ?>
                                                    <?php echo htmlspecialchars(substr($category['description'], 0, 100)); ?>
                                                    <?php if (strlen($category['description']) > 100): ?>
                                                        <span class="text-muted">...</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Không có mô tả</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary"><?php echo $category['product_count'] ?? 0; ?></span>
                                            </td>
                                            <td>
                                                <?php if ($category['is_active']): ?>
                                                    <span class="badge badge-success">Hoạt động</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Không hoạt động</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo date('d/m/Y H:i', strtotime($category['created_at'])); ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="index.php?page=products&category=<?php echo $category['id']; ?>" 
                                                       class="btn btn-sm btn-outline-info" target="_blank" title="Xem sản phẩm">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="index.php?page=admin/categories/edit&id=<?php echo $category['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="index.php?page=admin/categories/delete&id=<?php echo $category['id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger btn-delete" title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
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
                                            <a class="page-link" href="?page=admin/categories&p=<?php echo $currentPage - 1; ?>">Trước</a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                        <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=admin/categories&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($currentPage < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=admin/categories&p=<?php echo $currentPage + 1; ?>">Sau</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-list fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có danh mục nào</h5>
                            <p class="text-muted">Bắt đầu bằng cách thêm danh mục đầu tiên.</p>
                            <a href="index.php?page=admin/categories/create" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Thêm danh mục mới
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Select all checkbox
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.category-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// DataTable initialization
$(document).ready(function() {
    $('#dataTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Vietnamese.json"
        },
        "pageLength": 25,
        "order": [[1, "desc"]]
    });
});
</script>

<?php
// Include admin footer
include __DIR__ . '/../../../admin/includes/footer.php';
?> 