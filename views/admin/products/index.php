<?php
// Include admin header
include __DIR__ . '/../../../admin/includes/header.php';
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý sản phẩm</h1>
        <a href="index.php?page=admin/products/create" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Thêm sản phẩm mới
        </a>
    </div>

    <!-- Content Row -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Danh sách sản phẩm</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                             aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Tùy chọn:</div>
                            <a class="dropdown-item" href="index.php?page=admin/products/export">Xuất Excel</a>
                            <a class="dropdown-item" href="index.php?page=admin/products/import">Nhập Excel</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="index.php?page=admin/products/bulk-delete">Xóa hàng loạt</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($products)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll">
                                        </th>
                                        <th>ID</th>
                                        <th>Hình ảnh</th>
                                        <th>Tên sản phẩm</th>
                                        <th>Danh mục</th>
                                        <th>Giá</th>
                                        <th>Giảm giá</th>
                                        <th>Tồn kho</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="product-checkbox" value="<?php echo $product['id']; ?>">
                                            </td>
                                            <td><?php echo $product['id']; ?></td>
                                            <td>
                                                <?php if (!empty($product['image_'])): ?>
                                                    <img src="<?php echo $product['image_']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                         class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light text-center" style="width: 50px; height: 50px; line-height: 50px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                                <br>
                                                <small class="text-muted">SKU: <?php echo $product['id']; ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                            <td>
                                                <?php if ($product['sale'] && $product['sale'] > 0): ?>
                                                    <span class="text-decoration-line-through text-muted">
                                                        <?php echo number_format($product['price'], 0, ',', '.'); ?>₫
                                                    </span>
                                                    <br>
                                                    <span class="text-danger font-weight-bold">
                                                        <?php echo number_format($product['price'] - $product['sale'], 0, ',', '.'); ?>₫
                                                    </span>
                                                <?php else: ?>
                                                    <span class="font-weight-bold">
                                                        <?php echo number_format($product['price'], 0, ',', '.'); ?>₫
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($product['sale'] && $product['sale'] > 0): ?>
                                                    <span class="badge badge-danger">
                                                        -<?php echo round(($product['sale'] / $product['price']) * 100); ?>%
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($product['stock'] > 0): ?>
                                                    <span class="badge badge-success"><?php echo $product['stock']; ?></span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Hết hàng</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($product['is_available']): ?>
                                                    <span class="badge badge-success">Có sẵn</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Không có sẵn</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="index.php?page=product&id=<?php echo $product['id']; ?>" 
                                                       class="btn btn-sm btn-outline-info" target="_blank" title="Xem">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="index.php?page=admin/products/edit&id=<?php echo $product['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="index.php?page=admin/products/delete&id=<?php echo $product['id']; ?>" 
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
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center">
                                    <?php if ($currentPage > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=admin/products&p=<?php echo $currentPage - 1; ?>">Trước</a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                        <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=admin/products&p=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($currentPage < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=admin/products&p=<?php echo $currentPage + 1; ?>">Sau</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-box fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có sản phẩm nào</h5>
                            <p class="text-muted">Bắt đầu bằng cách thêm sản phẩm đầu tiên.</p>
                            <a href="index.php?page=admin/products/create" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Thêm sản phẩm mới
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
    const checkboxes = document.querySelectorAll('.product-checkbox');
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