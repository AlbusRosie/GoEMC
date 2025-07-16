<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Order.php';

class AdminController extends BaseController {
    private $productModel;
    private $categoryModel;
    private $userModel;
    private $orderModel;
    
    public function __construct($conn = null) {
        parent::__construct();
        if ($conn) {
            $this->conn = $conn;
        }
        $this->productModel = new Product($this->conn);
        $this->categoryModel = new Category($this->conn);
        $this->userModel = new User($this->conn);
        $this->orderModel = new Order($this->conn);
    }
    
    // Dashboard chính
    public function dashboard() {
        $this->requireAdmin();
        
        // Thống kê tổng quan
        $totalProducts = $this->productModel->getTotal();
        $totalCategories = $this->categoryModel->getTotal();
        $totalUsers = $this->userModel->getTotal();
        $totalOrders = $this->orderModel->getTotal();
        
        // Sản phẩm mới nhất
        $recentProducts = $this->productModel->getLatest(5);
        
        // Đơn hàng gần đây
        $recentOrders = $this->orderModel->getAll(5, 0);
        
        // Danh mục phổ biến
        $popularCategories = $this->categoryModel->getPopular(5);
        
        $data = [
            'totalProducts' => $totalProducts,
            'totalCategories' => $totalCategories,
            'totalUsers' => $totalUsers,
            'totalOrders' => $totalOrders,
            'recentProducts' => $recentProducts,
            'recentOrders' => $recentOrders,
            'popularCategories' => $popularCategories
        ];
        
        $this->render('admin/dashboard', $data);
    }
    
    // Upload ảnh
    public function uploadImage() {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        $uploadedImages = $this->handleImageUpload();
        
        if (!empty($uploadedImages)) {
            $this->jsonResponse([
                'success' => true,
                'images' => $uploadedImages
            ]);
        } else {
            $this->jsonResponse(['error' => 'No images uploaded'], 400);
        }
    }
    
    // Handle image upload
    private function handleImageUpload() {
        $uploadedImages = [];
        
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $uploadDir = __DIR__ . '/../assets/uploads/';
            
            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $fileName = time() . '_' . uniqid() . '_' . $_FILES['images']['name'][$key];
                    $filePath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($tmpName, $filePath)) {
                        $uploadedImages[] = 'assets/uploads/' . $fileName;
                    }
                }
            }
        }
        
        return $uploadedImages;
    }
    
    // Xử lý logout
    public function logout() {
        session_destroy();
        $this->redirect('admin/login.php');
    }
} 