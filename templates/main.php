<!DOCTYPE html>
<html lang="vi" class="<?php echo isset($_SESSION['theme']) ? $_SESSION['theme'] . '-theme' : 'light-theme'; ?>">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Go - Thanh Lý Gỗ Việt Nam</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="main-container">
        <?php echo isset($content) ? $content : ''; ?>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>