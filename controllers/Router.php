<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/HomeController.php';
require_once __DIR__ . '/ProductController.php';
require_once __DIR__ . '/CategoryController.php';
require_once __DIR__ . '/UserController.php';
require_once __DIR__ . '/AdminController.php';
require_once __DIR__ . '/CartController.php';
require_once __DIR__ . '/OrderController.php';

class Router {
    private $routes = [];
    private $defaultController = 'HomeController';
    private $defaultAction = 'index';
    
    public function __construct() {
        $this->initializeRoutes();
    }
    
    // Khởi tạo các routes
    private function initializeRoutes() {
        // Frontend routes
        $this->routes = [
            // Home routes
            'home' => ['controller' => 'HomeController', 'action' => 'index'],
            'store' => ['controller' => 'HomeController', 'action' => 'store'],
            'about' => ['controller' => 'HomeController', 'action' => 'about'],
            'contact' => ['controller' => 'HomeController', 'action' => 'contact'],
            'send-contact' => ['controller' => 'HomeController', 'action' => 'sendContact'],
            
            // Product routes
            'products' => ['controller' => 'ProductController', 'action' => 'index'],
            'product' => ['controller' => 'ProductController', 'action' => 'show'],
            'category' => ['controller' => 'CategoryController', 'action' => 'show'],
            

            
            // API routes
            'api/products/search' => ['controller' => 'ApiController', 'action' => 'searchProducts'],
            'api/products/by-category' => ['controller' => 'ApiController', 'action' => 'getProductsByCategory'],
            // 'api/products/add-to-cart' => ['controller' => 'ApiController', 'action' => 'addToCart'], // Disabled - use api/cart/add instead
            'api/categories/all' => ['controller' => 'ApiController', 'action' => 'getAllCategories'],
            'api/categories/get' => ['controller' => 'ApiController', 'action' => 'getCategoryById'],
            'api/home/search' => ['controller' => 'ApiController', 'action' => 'quickSearch'],
            'api/home/products-by-category' => ['controller' => 'ApiController', 'action' => 'getProductsByCategory'],
            'api/products/featured' => ['controller' => 'ApiController', 'action' => 'getFeaturedProducts'],
            'api/products/latest' => ['controller' => 'ApiController', 'action' => 'getLatestProducts'],
            'api/products/best-sellers' => ['controller' => 'ApiController', 'action' => 'getBestSellers'],
            'api/products/info' => ['controller' => 'ApiController', 'action' => 'getProductInfo'],
            
            // Admin routes
            'admin' => ['controller' => 'AdminController', 'action' => 'dashboard'],
            'admin/dashboard' => ['controller' => 'AdminController', 'action' => 'dashboard'],
            
            // Admin Product routes
            'admin/products' => ['controller' => 'ProductController', 'action' => 'adminIndex'],
            'admin/products/create' => ['controller' => 'ProductController', 'action' => 'adminCreate'],
            'admin/products/store' => ['controller' => 'ProductController', 'action' => 'adminStore'],
            'admin/products/edit' => ['controller' => 'ProductController', 'action' => 'adminEdit'],
            'admin/products/update' => ['controller' => 'ProductController', 'action' => 'adminUpdate'],
            'admin/products/delete' => ['controller' => 'ProductController', 'action' => 'adminDelete'],
            
            // Admin Category routes
            'admin/categories' => ['controller' => 'CategoryController', 'action' => 'adminIndex'],
            'admin/categories/create' => ['controller' => 'CategoryController', 'action' => 'adminCreate'],
            'admin/categories/store' => ['controller' => 'CategoryController', 'action' => 'adminStore'],
            'admin/categories/edit' => ['controller' => 'CategoryController', 'action' => 'adminEdit'],
            'admin/categories/update' => ['controller' => 'CategoryController', 'action' => 'adminUpdate'],
            'admin/categories/delete' => ['controller' => 'CategoryController', 'action' => 'adminDelete'],
            'admin/categories/detail' => ['controller' => 'CategoryController', 'action' => 'adminDetailCategories'],
            'admin/categories/detail/create' => ['controller' => 'CategoryController', 'action' => 'adminCreateDetailCategory'],
            'admin/categories/detail/update' => ['controller' => 'CategoryController', 'action' => 'adminUpdateDetailCategory'],
            'admin/categories/detail/delete' => ['controller' => 'CategoryController', 'action' => 'adminDeleteDetailCategory'],
            
            // Admin User routes
            'admin/users' => ['controller' => 'UserController', 'action' => 'adminIndex'],
            'admin/users/create' => ['controller' => 'UserController', 'action' => 'adminCreate'],
            'admin/users/store' => ['controller' => 'UserController', 'action' => 'adminStore'],
            'admin/users/edit' => ['controller' => 'UserController', 'action' => 'adminEdit'],
            'admin/users/update' => ['controller' => 'UserController', 'action' => 'adminUpdate'],
            'admin/users/delete' => ['controller' => 'UserController', 'action' => 'adminDelete'],
            
            // Admin authentication routes
            'admin/login' => ['controller' => 'UserController', 'action' => 'login'],
            'admin/authenticate' => ['controller' => 'UserController', 'action' => 'authenticate'],
            'admin/logout' => ['controller' => 'AdminController', 'action' => 'logout'],
            
            // Cart routes
            'cart' => ['controller' => 'CartController', 'action' => 'showCart'],
            
            // Order routes
            'checkout' => ['controller' => 'OrderController', 'action' => 'showCheckout'],
            'order' => ['controller' => 'OrderController', 'action' => 'showOrder'],
            'orders' => ['controller' => 'OrderController', 'action' => 'showUserOrders'],
            
            // Admin upload routes
            'admin/upload-image' => ['controller' => 'AdminController', 'action' => 'uploadImage'],
        ];
    }
    
    // Dispatch request
    public function dispatch($page = null, $action = null) {
        // Get page from URL if not provided
        if (!$page) {
            $page = isset($_GET['page']) ? $_GET['page'] : 'home';
        }
        
        // Get action from URL if not provided
        if (!$action) {
            $action = isset($_GET['action']) ? $_GET['action'] : null;
        }
        
        // Build route key
        $routeKey = $page;
        if ($action) {
            $routeKey .= '/' . $action;
        }
        
        // Check if this is an API route
        if (strpos($routeKey, 'api/') === 0) {
            $this->handleApiRoute($routeKey);
            return;
        }
        
        // Check if route exists
        if (!isset($this->routes[$routeKey])) {
            // Try to find a matching route
            $routeKey = $this->findMatchingRoute($page, $action);
        }
        
        // Get route configuration
        $route = $this->routes[$routeKey] ?? [
            'controller' => $this->defaultController,
            'action' => $this->defaultAction
        ];
        
        // Get controller and action
        $controllerName = $route['controller'];
        $actionName = $route['action'];
        
        // Check if controller exists
        if (!class_exists($controllerName)) {
            $this->handleError("Controller {$controllerName} not found");
            return;
        }
        
        // Create controller instance with database connection
        global $conn;
        $controller = new $controllerName($conn);
        
        // Check if action exists
        if (!method_exists($controller, $actionName)) {
            $this->handleError("Action {$actionName} not found in {$controllerName}");
            return;
        }
        
        // Execute action with parameters
        try {
            // Check if this is a parameterized route (like order with ID)
            if ($routeKey === 'order' && isset($_GET['id'])) {
                $controller->$actionName($_GET['id']);
            } else {
                $controller->$actionName();
            }
        } catch (Exception $e) {
            $this->handleError($e->getMessage());
        }
    }
    
    // Handle API routes
    private function handleApiRoute($routeKey) {
        global $conn;
        
        // Ensure session is started for API routes
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Map API routes to controllers
        $apiRoutes = [
            'api/cart/add' => ['CartController', 'addToCart'],
            'api/cart/update' => ['CartController', 'updateCart'],
            'api/cart/remove' => ['CartController', 'removeFromCart'],
            'api/cart/get' => ['CartController', 'getCart'],
            'api/cart/clear' => ['CartController', 'clearCart'],
            'api/order/create' => ['OrderController', 'createOrder'],
            'api/order/apply-coupon' => ['OrderController', 'applyCoupon'],
            'api/order/calculate-shipping' => ['OrderController', 'calculateShipping'],
            'api/order/check-payment' => ['OrderController', 'checkPayment'],
            'api/order/update-payment-status' => ['OrderController', 'updatePaymentStatus'],
            'api/order/cancel' => ['OrderController', 'cancelOrder']
        ];
        
        if (isset($apiRoutes[$routeKey])) {
            $controllerName = $apiRoutes[$routeKey][0];
            $actionName = $apiRoutes[$routeKey][1];
            
            if (class_exists($controllerName)) {
                $controller = new $controllerName($conn);
                if (method_exists($controller, $actionName)) {
                    try {
                        $controller->$actionName();
                        return;
                    } catch (Exception $e) {
                        http_response_code(500);
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                        return;
                    }
                }
            }
        }
        
        // API route not found
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'API endpoint not found']);
    }
    
    // Find matching route
    private function findMatchingRoute($page, $action) {
        $searchKey = $page;
        if ($action) {
            $searchKey .= '/' . $action;
        }
        
        // Direct match
        if (isset($this->routes[$searchKey])) {
            return $searchKey;
        }
        
        // Try to find partial matches
        foreach ($this->routes as $route => $config) {
            if (strpos($route, $searchKey) === 0) {
                return $route;
            }
        }
        
        return 'home';
    }
    
    // Handle errors
    private function handleError($message) {
        // Log error

        
        // Show error page
        global $conn;
        $controller = new HomeController($conn);
        $controller->renderError($message);
    }
    
    // Generate URL
    public function url($page, $action = null, $params = []) {
        $url = "index.php?page={$page}";
        
        if ($action) {
            $url .= "&action={$action}";
        }
        
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $url .= "&{$key}=" . urlencode($value);
            }
        }
        
        return $url;
    }
    
    // Check if current route is admin route
    public function isAdminRoute($page, $action = null) {
        $routeKey = $page;
        if ($action) {
            $routeKey .= '/' . $action;
        }
        
        return strpos($routeKey, 'admin/') === 0;
    }
    
    // Get current route
    public function getCurrentRoute() {
        $page = isset($_GET['page']) ? $_GET['page'] : 'home';
        $action = isset($_GET['action']) ? $_GET['action'] : null;
        
        return [
            'page' => $page,
            'action' => $action,
            'is_admin' => $this->isAdminRoute($page, $action),
            'is_api' => strpos($page, 'api/') === 0
        ];
    }
} 