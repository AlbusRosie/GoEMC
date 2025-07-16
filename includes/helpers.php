<?php
// Global helper functions

/**
 * Generate URL using Router
 */
function url($page, $action = null, $params = []) {
    global $router;
    if (!$router) {
        $router = new Router();
    }
    return $router->url($page, $action, $params);
}

/**
 * Get current route information
 */
function currentRoute() {
    global $router;
    if (!$router) {
        $router = new Router();
    }
    return $router->getCurrentRoute();
}

/**
 * Check if current route is admin route
 */
function isAdminRoute() {
    $route = currentRoute();
    return $route['is_admin'];
}

/**
 * Display flash messages
 */
function displayFlashMessages() {
    $html = '';
    
    // Success messages
    if (isset($_SESSION['success'])) {
        $html .= '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        $html .= htmlspecialchars($_SESSION['success']);
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        $html .= '</div>';
        unset($_SESSION['success']);
    }
    
    // Error messages
    if (isset($_SESSION['error'])) {
        $html .= '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        $html .= htmlspecialchars($_SESSION['error']);
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        $html .= '</div>';
        unset($_SESSION['error']);
    }
    
    // Multiple errors
    if (isset($_SESSION['errors']) && is_array($_SESSION['errors'])) {
        $html .= '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        $html .= '<ul class="mb-0">';
        foreach ($_SESSION['errors'] as $error) {
            $html .= '<li>' . htmlspecialchars($error) . '</li>';
        }
        $html .= '</ul>';
        $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        $html .= '</div>';
        unset($_SESSION['errors']);
    }
    
    return $html;
}

/**
 * Get old form data
 */
function old($key, $default = '') {
    return $_SESSION['old_data'][$key] ?? $default;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Get current user data
 */
function currentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'name' => $_SESSION['user_name'],
        'role' => $_SESSION['user_role']
    ];
}

/**
 * Format price
 */
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . '₫';
}

/**
 * Format date
 */
function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

/**
 * Truncate text
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . $suffix;
}

/**
 * Generate pagination links
 */
function pagination($currentPage, $totalPages, $baseUrl = '') {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    if ($currentPage > 1) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage - 1) . '">Trước</a>';
        $html .= '</li>';
    }
    
    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        $active = ($i == $currentPage) ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '">';
        $html .= '<a class="page-link" href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a>';
        $html .= '</li>';
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . $baseUrl . '?page=' . ($currentPage + 1) . '">Sau</a>';
        $html .= '</li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}

/**
 * Generate breadcrumbs
 */
function breadcrumbs($items) {
    $html = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
    
    foreach ($items as $index => $item) {
        $isLast = ($index === count($items) - 1);
        
        if ($isLast) {
            $html .= '<li class="breadcrumb-item active" aria-current="page">';
            $html .= htmlspecialchars($item['text']);
            $html .= '</li>';
        } else {
            $html .= '<li class="breadcrumb-item">';
            $html .= '<a href="' . $item['url'] . '">' . htmlspecialchars($item['text']) . '</a>';
            $html .= '</li>';
        }
    }
    
    $html .= '</ol></nav>';
    
    return $html;
}

/**
 * Generate star rating HTML
 */
function starRating($rating, $maxRating = 5) {
    $html = '<div class="stars">';
    
    for ($i = 1; $i <= $maxRating; $i++) {
        if ($i <= $rating) {
            $html .= '<i class="fas fa-star"></i>';
        } elseif ($i == ceil($rating) && $rating - floor($rating) >= 0.5) {
            $html .= '<i class="fas fa-star-half-alt"></i>';
        } else {
            $html .= '<i class="far fa-star"></i>';
        }
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Generate discount badge
 */
function discountBadge($discountPercent) {
    if ($discountPercent <= 0) {
        return '';
    }
    
    return '<span class="badge bg-danger">-' . $discountPercent . '%</span>';
}

/**
 * Generate color swatch HTML
 */
function colorSwatch($colorName, $size = 'normal') {
    $colorMap = [
        'Nâu tự nhiên' => '#8B4513',
        'Nâu đậm' => '#654321',
        'Nâu sáng' => '#D2691E',
        'Nâu' => '#8B4513',
        'Đen' => '#000000',
        'Trắng' => '#FFFFFF',
        'Xám' => '#808080',
        'Kem' => '#F5F5DC',
        'Vàng' => '#FFD700',
        'Đỏ' => '#FF0000',
        'Hồng' => '#FFC0CB',
        'Xanh lá' => '#228B22',
        'Xanh dương' => '#0000FF',
        'Tím' => '#800080',
        'Cam' => '#FFA500',
        'Khác' => 'linear-gradient(45deg, #ff0000, #00ff00, #0000ff)'
    ];
    
    $hexColor = $colorMap[$colorName] ?? '#808080';
    $borderStyle = ($colorName === 'Trắng' || $colorName === 'Kem') ? 'border: 1px solid #ddd;' : '';
    
    $sizeClass = $size === 'small' ? 'related-color-option' : 'color-option';
    
    return '<div class="' . $sizeClass . '" style="background-color: ' . $hexColor . '; ' . $borderStyle . '"></div>';
}

/**
 * CSRF token
 */
function csrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * CSRF token input field
 */
function csrfField() {
    return '<input type="hidden" name="_token" value="' . csrfToken() . '">';
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
} 