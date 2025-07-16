<?php
// Include helpers
require_once __DIR__ . '/helpers.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Thanh lý gỗ chất lượng cao</title>
    <meta name="description" content="<?php echo META_DESCRIPTION; ?>">
    <meta name="keywords" content="<?php echo META_KEYWORDS; ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
    <!-- Header -->
    <header class="header-main">
        <!-- Main Header -->
        <div class="main-header py-3">
            <div class="container">
                <div class="row align-items-center">
                    <!-- Logo -->
                    <div class="col-lg-3 col-md-4">
                        <a class="navbar-brand" href="index.php">
                            <span class="brand-text">EMCwood.</span>
                            <span class="brand-dot"></span>
                        </a>
                    </div>
                    
                    <!-- Search Bar -->
                    <div class="col-lg-6 col-md-5">
                        <div class="search-container">
                            <form class="search-form" action="index.php" method="GET">
                                <input type="hidden" name="page" value="products">
                                <div class="input-group">
                                    <input type="text" class="form-control search-input" 
                                           name="search" placeholder="Tìm kiếm sản phẩm..." 
                                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                    <button class="btn search-btn" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- User Actions - Redesigned -->
                    <div class="col-lg-3 col-md-3">
                        <div class="user-actions d-flex justify-content-end align-items-center gap-3">
                            <!-- User Account -->
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <div class="dropdown">
                                    <button class="btn btn-link text-dark p-0 text-decoration-none" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-user-circle fs-5"></i>
                                        <span class="ms-1 fw-semibold">
                                            <?php echo htmlspecialchars(str_replace('_', ' ', $_SESSION['user_name'] ?? 'Tài khoản')); ?>
                                        </span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="index.php?page=profile"><i class="fas fa-user me-2"></i>Hồ sơ</a></li>
                                        <li><a class="dropdown-item" href="index.php?page=orders"><i class="fas fa-shopping-bag me-2"></i>Đơn hàng</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="index.php?page=logout"><i class="fas fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <a href="#" class="btn btn-link text-dark p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#authModal">
                                    <i class="fas fa-user-circle fs-5"></i> <span class="ms-1">Đăng nhập</span>
                                </a>
                            <?php endif; ?>
                            
                            <!-- Cart -->
                            <a href="index.php?page=cart" class="btn btn-link text-dark p-0 text-decoration-none position-relative">
                                <i class="fas fa-shopping-cart fs-5"></i>
                                <span class="cart-count position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                                    <?php
                                    global $conn;
                                    if ($conn) {
                                        if (isset($_SESSION['user_id'])) {
                                            require_once __DIR__ . '/../models/Cart.php';
                                            $cart = new Cart($conn);
                                            echo $cart->getCartCount($_SESSION['user_id']);
                                        } else {
                                            require_once __DIR__ . '/../models/Cart.php';
                                            $cart = new Cart($conn);
                                            echo $cart->getCartCount(null, session_id());
                                        }
                                    } else {
                                        echo '0';
                                    }
                                    ?>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-top">
            <div class="container">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                Sản phẩm <i class="fas fa-chevron-down ms-1"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="index.php?page=products&category=1">Gỗ tự nhiên</a></li>
                                <li><a class="dropdown-item" href="index.php?page=products&category=2">Gỗ công nghiệp</a></li>
                                <li><a class="dropdown-item" href="index.php?page=products&category=3">Nội thất gỗ</a></li>
                                <li><a class="dropdown-item" href="index.php?page=products&category=4">Ván gỗ</a></li>
                                <li><a class="dropdown-item" href="index.php?page=products&category=5">Gỗ tấm</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="index.php?page=products&sale=1">Sản phẩm giảm giá</a></li>
                                <li><a class="dropdown-item" href="index.php?page=products&featured=1">Sản phẩm nổi bật</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=contact">Liên hệ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=about">Về EMCWood</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php?page=stores">Cửa hàng</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content --> 

<?php if (!isset($_SESSION['user_id'])): ?>
<!-- Modal Đăng nhập/Đăng ký -->
<div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content auth-form-container">
      <div class="modal-header border-0 pb-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>
      <div class="modal-body pt-0">
        <ul class="nav nav-tabs mb-3 justify-content-center" id="authTabModal" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="login-tab-modal" data-bs-toggle="tab" data-bs-target="#login-form-modal" type="button" role="tab">Đăng nhập</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="register-tab-modal" data-bs-toggle="tab" data-bs-target="#register-form-modal" type="button" role="tab">Đăng ký</button>
          </li>
        </ul>
        <div class="tab-content" id="authTabContentModal">
          <!-- Đăng nhập -->
          <div class="tab-pane fade show active" id="login-form-modal" role="tabpanel">
            <form method="post" action="index.php" class="auth-form" id="loginForm" autocomplete="off">
              <input type="hidden" name="action" value="login">
              <h3 class="auth-title">Chào mừng trở lại!</h3>
              <div class="form-group mb-3">
                <div class="input-group input-group-lg">
                  <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-user-circle text-secondary"></i></span>
                  <input type="text" class="form-control border-0 ps-2" name="email_or_phone" placeholder="Email hoặc Số điện thoại" value="<?php echo isset($_SESSION['login_prefill']['email_or_phone']) ? htmlspecialchars($_SESSION['login_prefill']['email_or_phone']) : ''; ?>">
                </div>
              </div>
              <div class="form-group mb-4">
                <div class="input-group input-group-lg">
                  <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-lock text-secondary"></i></span>
                  <input type="password" class="form-control border-0 ps-2" name="password" placeholder="Mật khẩu" required>
                </div>
              </div>
              <button type="submit" class="btn btn-primary btn-block py-3 fw-bold" style="border-radius: 16px; font-size: 1.15rem;">Đăng nhập</button>
              <div class="switch-link mt-3">
                Chưa có tài khoản? <a href="#" onclick="switchToRegisterModal(event)">Đăng ký</a>
              </div>
            </form>
          </div>
          <!-- Đăng ký -->
          <div class="tab-pane fade" id="register-form-modal" role="tabpanel">
            <form method="post" action="index.php" class="auth-form" id="registerForm" autocomplete="off">
              <input type="hidden" name="action" value="register">
              <h3 class="auth-title">Tạo tài khoản mới</h3>
              <div class="form-group mb-3">
                <div class="input-group input-group-lg">
                  <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-envelope text-secondary"></i></span>
                  <input type="email" class="form-control border-0 ps-2" name="email" placeholder="Email" required>
                </div>
              </div>
              <div class="form-group mb-3">
                <div class="input-group input-group-lg">
                  <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-phone text-secondary"></i></span>
                  <input type="text" class="form-control border-0 ps-2" name="phone" placeholder="Số điện thoại" required>
                </div>
              </div>
              <div class="form-group mb-3">
                <div class="input-group input-group-lg">
                  <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-user text-secondary"></i></span>
                  <input type="text" class="form-control border-0 ps-2" name="name" placeholder="Họ tên" required>
                </div>
              </div>
              <div class="form-group mb-3">
                <div class="input-group input-group-lg">
                  <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-map-marker-alt text-secondary"></i></span>
                  <input type="text" class="form-control border-0 ps-2" name="address" placeholder="Địa chỉ">
                </div>
              </div>
              <div class="form-group mb-4">
                <div class="input-group input-group-lg">
                  <span class="input-group-text bg-white border-0 ps-3"><i class="fas fa-lock text-secondary"></i></span>
                  <input type="password" class="form-control border-0 ps-2" name="password" placeholder="Mật khẩu" required>
                </div>
              </div>
              <button type="submit" class="btn btn-primary btn-block py-3 fw-bold" style="border-radius: 16px; font-size: 1.15rem;">Đăng ký</button>
              <div class="switch-link mt-3">
                Đã có tài khoản? <a href="#" onclick="switchToLoginModal(event)">Đăng nhập</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  var formData = new FormData(this);
  
  fetch('index.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.text())
  .then(data => {
    // Nếu đăng ký thành công, chuyển sang tab đăng nhập
    if (data.includes('success')) {
      // Chuyển sang tab đăng nhậpimage.png
      document.querySelector('#login-tab-modal').click();
      
      // Điền sẵn email/phone vào form đăng nhập
      var emailOrPhone = formData.get('email') || formData.get('phone');
      document.querySelector('input[name="email_or_phone"]').value = emailOrPhone;
      
    } else {
      // Hiển thị lỗi
      alert('Có lỗi xảy ra: ' + data);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Có lỗi xảy ra khi đăng ký');
  });
});
function switchToRegisterModal(e) {
  e.preventDefault();
  var tab = document.querySelector('#register-tab-modal');
  if(tab) tab.click();
}
function switchToLoginModal(e) {
  e.preventDefault();
  var tab = document.querySelector('#login-tab-modal');
  if(tab) tab.click();
}
document.addEventListener('DOMContentLoaded', function() {
  var loginBtn = document.querySelector('[data-bs-target="#authModal"]');
  if(loginBtn) {
    loginBtn.addEventListener('click', function(e) {
      var modal = new bootstrap.Modal(document.getElementById('authModal'));
      modal.show();
    });
  }
  <?php if (!empty($_SESSION['show_login_modal'])): unset($_SESSION['show_login_modal']); ?>
    var modal = new bootstrap.Modal(document.getElementById('authModal'));
    modal.show();
    document.querySelector('#login-tab-modal').click();
  <?php endif; ?>
  <?php if (!empty($_SESSION['show_register_modal'])): unset($_SESSION['show_register_modal']); ?>
    var modal = new bootstrap.Modal(document.getElementById('authModal'));
    modal.show();
    document.querySelector('#register-tab-modal').click();
  <?php endif; ?>

  // Khi modal đóng, xóa backdrop nếu còn sót
  var authModalEl = document.getElementById('authModal');
  if (authModalEl) {
    authModalEl.addEventListener('hidden.bs.modal', function () {
      // Xóa backdrop nếu còn
      var backdrops = document.querySelectorAll('.modal-backdrop');
      backdrops.forEach(function(bd) { bd.parentNode.removeChild(bd); });
      // Xóa class 'modal-open' trên body nếu còn
      document.body.classList.remove('modal-open');
      document.body.style = '';
    });
  }

  // Xử lý form đăng ký bằng AJAX
  document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var formData = new FormData(this);
    
    fetch('index.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(result => {
      if (result.success) {
        // Chuyển sang tab đăng nhập
        document.querySelector('#login-tab-modal').click();
        
        // Điền sẵn email/phone vào form đăng nhập
        var emailOrPhone = formData.get('email') || formData.get('phone');
        document.querySelector('input[name="email_or_phone"]').value = emailOrPhone;
        
        // Hiển thị thông báo thành công
        alert('Đăng ký thành công! Vui lòng đăng nhập.');
      } else {
        // Hiển thị lỗi
        alert(result.message || 'Đăng ký thất bại');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Có lỗi xảy ra khi đăng ký');
    });
  });

  // Xử lý form đăng nhập bằng AJAX
  document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    var formData = new FormData(this);
    
    fetch('index.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(result => {
      if (result.success) {
        // Đăng nhập thành công - đóng modal và reload trang để cập nhật header
        var modal = bootstrap.Modal.getInstance(document.getElementById('authModal'));
        modal.hide();
        location.reload();
      } else {
        // Hiển thị lỗi
        alert(result.message || 'Đăng nhập thất bại');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Có lỗi xảy ra khi đăng nhập');
    });
  });
});
</script>

<!-- Bootstrap JS + Popper (nên dùng CDN) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php if (!empty($_SESSION['login_error'])): ?>
  <div class="alert alert-danger"><?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['register_error'])): ?>
  <div class="alert alert-danger"><?php echo $_SESSION['register_error']; unset($_SESSION['register_error']); ?></div>
<?php endif; ?>
<?php unset($_SESSION['login_prefill']); ?>