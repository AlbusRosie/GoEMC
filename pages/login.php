<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width:400px;margin:40px auto;">
        <h2 class="mb-3">Đăng nhập</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">Đăng ký thành công! Vui lòng đăng nhập.</div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="mb-3">
                <label for="email_or_phone" class="form-label">Email hoặc Số điện thoại</label>
                <input type="text" class="form-control" id="email_or_phone" name="email_or_phone" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mật khẩu</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
        </form>
        <div class="mt-3 text-center">
            <a href="index.php?page=register">Chưa có tài khoản? Đăng ký</a>
        </div>
    </div>
</body>
</html> 