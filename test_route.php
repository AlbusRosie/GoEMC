<?php
session_start();
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'controllers/Router.php';

echo "<h2>Test Route Matching</h2>";

// Test different routes
$testRoutes = [
    'api/cart/add',
    'api/products/add-to-cart',
    'api/cart/update',
    'api/cart/remove'
];

foreach ($testRoutes as $route) {
    echo "<h3>Testing route: $route</h3>";
    
    // Simulate the route
    $_GET['page'] = $route;
    
    // Create router
    $router = new Router();
    
    // Get current route info
    $currentRoute = $router->getCurrentRoute();
    echo "Current route: " . print_r($currentRoute, true) . "<br>";
    
    // Check if it's an API route
    $isApiRoute = strpos($route, 'api/') === 0;
    echo "Is API route: " . ($isApiRoute ? 'Yes' : 'No') . "<br>";
    
    if ($isApiRoute) {
        // Test API route handling
        echo "Testing API route handling...<br>";
        
        // Capture output
        ob_start();
        try {
            $router->dispatch();
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage() . "<br>";
        }
        $output = ob_get_clean();
        
        echo "Output: " . htmlspecialchars($output) . "<br>";
    }
    
    echo "<hr>";
}

echo "<h3>All routes in Router:</h3>";
$router = new Router();
$reflection = new ReflectionClass($router);
$routesProperty = $reflection->getProperty('routes');
$routesProperty->setAccessible(true);
$routes = $routesProperty->getValue($router);

echo "<pre>";
foreach ($routes as $route => $config) {
    if (strpos($route, 'api/') === 0) {
        echo "$route => " . print_r($config, true) . "\n";
    }
}
echo "</pre>";

echo "<h3>API routes in handleApiRoute:</h3>";
echo "<pre>";
$apiRoutes = [
    'api/cart/add' => ['CartController', 'addToCart'],
    'api/cart/update' => ['CartController', 'updateCart'],
    'api/cart/remove' => ['CartController', 'removeFromCart'],
    'api/cart/get' => ['CartController', 'getCart'],
    'api/cart/clear' => ['CartController', 'clearCart'],
    'api/order/create' => ['OrderController', 'createOrder'],
    'api/order/apply-coupon' => ['OrderController', 'applyCoupon'],
    'api/order/calculate-shipping' => ['OrderController', 'calculateShipping'],
    'api/order/cancel' => ['OrderController', 'cancelOrder']
];

foreach ($apiRoutes as $route => $config) {
    echo "$route => " . print_r($config, true) . "\n";
}
echo "</pre>";
?> 