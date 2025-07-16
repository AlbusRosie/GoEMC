<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/User.php';

class ApiController extends BaseController {
    private $productModel;
    private $categoryModel;
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->productModel = new Product($this->conn);
        $this->categoryModel = new Category($this->conn);
        $this->userModel = new User($this->conn);
    }
    
    // API: Tìm kiếm sản phẩm
    public function searchProducts() {
        $search = isset($_GET['q']) ? $this->sanitize($_GET['q']) : '';
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        
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
    
    // API: Lấy sản phẩm theo danh mục
    public function getProductsByCategory() {
        $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $excludeId = isset($_GET['exclude_id']) ? (int)$_GET['exclude_id'] : null;
        
        if (!$categoryId) {
            $this->jsonResponse([]);
        }
        
        $products = $this->productModel->getByCategory($categoryId, $limit, $excludeId);
        
        // Add gallery images
        foreach ($products as &$product) {
            $product['gallery'] = $this->productModel->getProductImages($product['id'], 'main');
        }
        unset($product);
        
        $this->jsonResponse($products);
    }
    
    // API: Thêm vào giỏ hàng
    public function addToCart() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        $data = $this->getPostData();
        $productId = (int)$data['product_id'] ?? 0;
        $quantity = (int)$data['quantity'] ?? 1;
        $options = $data['options'] ?? [];
        
        if (!$productId) {
            $this->jsonResponse(['error' => 'Product ID is required'], 400);
        }
        
        // Validate product exists
        $product = $this->productModel->getById($productId);
        if (!$product) {
            $this->jsonResponse(['error' => 'Product not found'], 404);
        }
        
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Add to cart
        $cartItem = [
            'product_id' => $productId,
            'quantity' => $quantity,
            'options' => $options,
            'price' => $this->productModel->getCurrentPrice($product)
        ];
        
        $_SESSION['cart'][] = $cartItem;
        
        $this->jsonResponse([
            'success' => true,
            'message' => 'Product added to cart',
            'cart_count' => count($_SESSION['cart'])
        ]);
    }
    
    // API: Lấy tất cả danh mục
    public function getAllCategories() {
        $categories = $this->categoryModel->getAll();
        $this->jsonResponse($categories);
    }
    
    // API: Lấy danh mục theo ID
    public function getCategoryById() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$id) {
            $this->jsonResponse(['error' => 'Category ID is required'], 400);
        }
        
        $category = $this->categoryModel->getById($id);
        
        if (!$category) {
            $this->jsonResponse(['error' => 'Category not found'], 404);
        }
        
        $this->jsonResponse($category);
    }
    
    // API: Tìm kiếm nhanh
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
    
    // API: Lấy sản phẩm nổi bật
    public function getFeaturedProducts() {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
        $products = $this->productModel->getFeatured($limit);
        
        // Add gallery images
        foreach ($products as &$product) {
            $product['gallery'] = $this->productModel->getProductImages($product['id'], 'main');
        }
        unset($product);
        
        $this->jsonResponse($products);
    }
    
    // API: Lấy sản phẩm mới nhất
    public function getLatestProducts() {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
        $products = $this->productModel->getLatest($limit);
        
        // Add gallery images
        foreach ($products as &$product) {
            $product['gallery'] = $this->productModel->getProductImages($product['id'], 'main');
        }
        unset($product);
        
        $this->jsonResponse($products);
    }
    
    // API: Lấy sản phẩm bán chạy
    public function getBestSellers() {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
        $products = $this->productModel->getBestSellers($limit);
        
        // Add gallery images
        foreach ($products as &$product) {
            $product['gallery'] = $this->productModel->getProductImages($product['id'], 'main');
        }
        unset($product);
        
        $this->jsonResponse($products);
    }
    
    // API: Lấy thông tin sản phẩm
    public function getProductInfo() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$id) {
            $this->jsonResponse(['error' => 'Product ID is required'], 400);
        }
        
        $product = $this->productModel->getById($id);
        
        if (!$product) {
            $this->jsonResponse(['error' => 'Product not found'], 404);
        }
        
        // Add gallery images
        $product['gallery'] = $this->productModel->getProductImages($id, 'main');
        $product['description_gallery'] = $this->productModel->getProductImages($id, 'description');
        
        $this->jsonResponse($product);
    }
} 