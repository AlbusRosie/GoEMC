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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize theme from session
        const currentTheme = '<?php echo isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light'; ?>';
        document.documentElement.classList.toggle('dark-theme', currentTheme === 'dark');
        
        // Theme toggle handler
        window.toggleTheme = function() {
            fetch('<?php echo BASE_URL; ?>/includes/header.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'toggle_theme=1'
            })
            .then(response => response.json())
            .then(data => {
                document.documentElement.classList.toggle('dark-theme', data.theme === 'dark');
            })
            .catch(error => console.error('Error:', error));
        };
    });
    </script>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="main-container">
        <?php echo isset($content) ? $content : ''; ?>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>