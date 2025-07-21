<!DOCTYPE html>
<html lang="vi" class="<?php echo isset($_SESSION['theme']) ? $_SESSION['theme'] . '-theme' : 'light-theme'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME . ' - Thanh lý gỗ chất lượng cao'; ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : META_DESCRIPTION; ?>">
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
    
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="main-container">
        <?php echo isset($content) ? $content : ''; ?>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Bootstrap JS + Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Xóa thuộc tính style cứng trên tất cả dropdown menu (nếu có)
        document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
            menu.removeAttribute('style');
        });

        // Xử lý responsive menu
        var navbarToggler = document.querySelector('.navbar-toggler');
        var navbarCollapse = document.querySelector('.navbar-collapse');
        if (navbarToggler && navbarCollapse) {
            navbarToggler.addEventListener('click', function() {
                navbarCollapse.classList.toggle('show');
            });
        }
        
        // Đóng menu khi click bên ngoài
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.navbar')) {
                if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                    navbarCollapse.classList.remove('show');
                }
            }
        });
        // Đã xóa các dòng console.log test
    });
    </script>
</body>
</html>