<?php
// Cấu hình website thanh lý gỗ
define('SITE_NAME', 'Thanh Lý Gỗ Việt Nam');
define('SITE_URL', 'http://localhost/go');
define('ADMIN_EMAIL', 'admin@thanhlygo.com');

// Cấu hình SEO
define('META_DESCRIPTION', 'Chuyên thanh lý gỗ chất lượng cao, giá tốt nhất thị trường. Gỗ tự nhiên, gỗ công nghiệp, nội thất gỗ.');
define('META_KEYWORDS', 'thanh lý gỗ, gỗ tự nhiên, gỗ công nghiệp, nội thất gỗ, gỗ giá rẻ');

// Cấu hình upload
define('UPLOAD_PATH', 'assets/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Cấu hình phân trang
define('ITEMS_PER_PAGE', 12);
define('MAX_PAGE_LINKS', 5);

// Cấu hình thời gian
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Định nghĩa BASE_URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
define('BASE_URL', $protocol . '://' . $_SERVER['HTTP_HOST'] . '/go');

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return $text;
}
?> 