<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

class HomeController extends BaseController {
    private $productModel;
    private $categoryModel;
    
    public function __construct($conn = null) {
        parent::__construct();
        if ($conn) {
            $this->conn = $conn;
        }
        $this->productModel = new Product($this->conn);
        $this->categoryModel = new Category($this->conn);
    }
    
    // Hiển thị trang chủ
    public function index() {
        // Lấy sản phẩm nổi bật
        $featuredProducts = $this->productModel->getFeatured(8);
        
        // Lấy sản phẩm mới nhất
        $latestProducts = $this->productModel->getLatest(8);
        
        // Lấy sản phẩm bán chạy
        $bestSellers = $this->productModel->getBestSellers(8);
        
        // Lấy danh mục chính
        $categories = $this->categoryModel->getAll();
        
        // Lấy gallery ảnh cho sản phẩm
        foreach ($featuredProducts as &$product) {
            $product['gallery'] = $this->productModel->getProductImages($product['id'], 'main');
        }
        unset($product);
        
        foreach ($latestProducts as &$product) {
            $product['gallery'] = $this->productModel->getProductImages($product['id'], 'main');
        }
        unset($product);
        
        foreach ($bestSellers as &$product) {
            $product['gallery'] = $this->productModel->getProductImages($product['id'], 'main');
        }
        unset($product);
        
        $data = [
            'featuredProducts' => $featuredProducts,
            'latestProducts' => $latestProducts,
            'bestSellers' => $bestSellers,
            'categories' => $categories
        ];
        
        $this->render('home', $data);
    }
    
    // Hiển thị trang About
    public function about() {
        $data = [
            'pageTitle' => 'Về chúng tôi',
            'pageDescription' => 'Tìm hiểu về MOHO - Thương hiệu nội thất hàng đầu Việt Nam'
        ];
        
        $this->render('about', $data);
    }
    
    // Hiển thị trang Contact
    public function contact() {
        $data = [
            'pageTitle' => 'Liên hệ',
            'pageDescription' => 'Liên hệ với MOHO để được tư vấn và hỗ trợ'
        ];
        
        $this->render('contact', $data);
    }
    
    // Xử lý form liên hệ
    public function sendContact() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?page=contact');
        }
        
        $data = $this->getPostData();
        $data = $this->sanitize($data);
        
        // Validate required fields
        $requiredFields = ['name', 'email', 'subject', 'message'];
        $errors = $this->validateRequired($data, $requiredFields);
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode(', ', $errors);
            $_SESSION['old_data'] = $data;
            $this->redirect('index.php?page=contact');
        }
        
        // Here you would typically save to database or send email
        // For now, we'll just show success message
        
        $_SESSION['success'] = 'Cảm ơn bạn đã liên hệ. Chúng tôi sẽ phản hồi sớm nhất có thể!';
        $this->redirect('index.php?page=contact');
    }
    
    // Hiển thị trang 404
    public function notFound() {
        http_response_code(404);
        
        $data = [
            'pageTitle' => '404 - Không tìm thấy trang',
            'pageDescription' => 'Trang bạn đang tìm kiếm không tồn tại'
        ];
        
        $this->render('error', $data);
    }
    
    // API: Tìm kiếm sản phẩm nhanh
    public function quickSearch() {
        $search = isset($_GET['q']) ? $this->sanitize($_GET['q']) : '';
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
        
        if (empty($search)) {
            $this->jsonResponse([]);
        }
        
        $products = $this->productModel->search($search, $limit);
        
        // Add gallery images
        foreach ($products as &$product) {
            $product['gallery'] = $this->productModel->getProductImages($product['id'], 'main');
        }
        unset($product);
        
        $this->jsonResponse($products);
    }
    
    // API: Lấy sản phẩm theo danh mục cho trang chủ
    public function getProductsByCategory() {
        $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 4;
        
        if (!$categoryId) {
            $this->jsonResponse([]);
        }
        
        $products = $this->productModel->getByCategory($categoryId, $limit);
        
        // Add gallery images
        foreach ($products as &$product) {
            $product['gallery'] = $this->productModel->getProductImages($product['id'], 'main');
        }
        unset($product);
        
        $this->jsonResponse($products);
    }
    
    // Admin: Dashboard
    public function adminDashboard() {
        $this->requireAdmin();
        
        // Get statistics
        $totalProducts = $this->productModel->getTotal();
        $totalCategories = $this->categoryModel->getTotal();
        
        // Get recent products
        $recentProducts = $this->productModel->getLatest(5);
        
        // Get best selling products
        $bestSellers = $this->productModel->getBestSellers(5);
        
        $data = [
            'totalProducts' => $totalProducts,
            'totalCategories' => $totalCategories,
            'recentProducts' => $recentProducts,
            'bestSellers' => $bestSellers
        ];
        
        $this->render('admin/index', $data);
    }
} 