<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/ProductOption.php';

class ProductController extends BaseController {
    private $productModel;
    private $categoryModel;
    private $productOptionModel;
    
    public function __construct($conn = null) {
        parent::__construct();
        if ($conn) {
            $this->conn = $conn;
        }
        $this->productModel = new Product($this->conn);
        $this->categoryModel = new Category($this->conn);
        $this->productOptionModel = new ProductOption($this->conn);
    }
    
    // Hiển thị trang danh sách sản phẩm
    public function index() {
        // Truyền biến $pdo vào view để sử dụng trong pages/products.php
        global $pdo;
        
        // Include trực tiếp file products.php
        $viewPath = __DIR__ . '/../pages/products.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            $this->renderError('Không tìm thấy trang sản phẩm');
        }
    }
    
    // Hiển thị chi tiết sản phẩm
    public function show($id = null) {
        if (!$id) {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        }
        
        if (!$id) {
            $this->redirect('index.php?page=products');
        }
        
        // Lấy thông tin sản phẩm
        $product = $this->productModel->getById($id);
        
        if (!$product) {
            $this->redirect('index.php?page=products');
        }
        
        // Lấy gallery ảnh
        $product['gallery'] = $this->productModel->getProductImages($id, 'main');
        $product['description_gallery'] = $this->productModel->getProductImages($id, 'description');
        
        // Lấy chi tiết sản phẩm
        $productDetails = $this->productModel->getDetails($id);
        
        // Lấy product options
        $productOptions = $this->productOptionModel->getByProductId($id);
        
        // Lấy sản phẩm liên quan
        $relatedProducts = $this->productModel->getByCategory($product['category_id'], 4, $id);
        
        // Lấy thông tin bổ sung cho sản phẩm liên quan
        foreach ($relatedProducts as &$relatedProduct) {
            $relatedProduct['gallery'] = $this->productModel->getProductImages($relatedProduct['id'], 'main');
            $relatedProduct['rating'] = $relatedProduct['rating'] ?? null;
            $relatedProduct['review_count'] = $relatedProduct['review_count'] ?? 0;
            $relatedProduct['sold_count'] = $relatedProduct['sold_count'] ?? 0;
        }
        unset($relatedProduct);
        
        $data = [
            'product' => $product,
            'productDetails' => $productDetails,
            'productOptions' => $productOptions,
            'relatedProducts' => $relatedProducts
        ];
        
        $this->render('product', $data);
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
    
    // API: Tìm kiếm sản phẩm
    public function search() {
        $search = isset($_GET['q']) ? $this->sanitize($_GET['q']) : '';
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        
        if (empty($search)) {
            $this->jsonResponse([]);
        }
        
        $products = $this->productModel->search($search, $limit);
        
        $this->jsonResponse($products);
    }
    
    // API: Lấy sản phẩm theo category
    public function getByCategory() {
        $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $excludeId = isset($_GET['exclude_id']) ? (int)$_GET['exclude_id'] : null;
        
        if (!$categoryId) {
            $this->jsonResponse([]);
        }
        
        $products = $this->productModel->getByCategory($categoryId, $limit, $excludeId);
        
        $this->jsonResponse($products);
    }
    
    // Admin: Hiển thị danh sách sản phẩm (admin)
    public function adminIndex() {
        $this->requireAdmin();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $products = $this->productModel->getAll($limit, $offset);
        $totalProducts = $this->productModel->getTotal();
        $totalPages = ceil($totalProducts / $limit);
        
        $data = [
            'products' => $products,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalProducts' => $totalProducts
        ];
        
        $this->render('admin/products', $data);
    }
    
    // Admin: Hiển thị form thêm sản phẩm
    public function adminCreate() {
        $this->requireAdmin();
        
        $categories = $this->categoryModel->getAll();
        
        $data = [
            'categories' => $categories,
            'product' => null
        ];
        
        $this->render('admin/product-add', $data);
    }
    
    // Admin: Lưu sản phẩm mới
    public function adminStore() {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/products');
        }
        
        $data = $this->getPostData();
        $data = $this->sanitize($data);
        
        // Validate required fields
        $requiredFields = ['name', 'category_id', 'price'];
        $errors = $this->validateRequired($data, $requiredFields);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_data'] = $data;
            $this->redirect('admin/products/create');
        }
        
        // Handle file upload
        $uploadedImages = $this->handleImageUpload();
        
        // Create product
        $productId = $this->productModel->create($data);
        
        if ($productId) {
            // Save product images
            if (!empty($uploadedImages)) {
                $this->productModel->saveProductImages($productId, $uploadedImages);
            }
            
            $_SESSION['success'] = 'Product created successfully';
            $this->redirect('admin/products');
        } else {
            $_SESSION['errors'] = ['Failed to create product'];
            $_SESSION['old_data'] = $data;
            $this->redirect('admin/products/create');
        }
    }
    
    // Admin: Hiển thị form chỉnh sửa sản phẩm
    public function adminEdit($id = null) {
        $this->requireAdmin();
        
        if (!$id) {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        }
        
        if (!$id) {
            $this->redirect('admin/products');
        }
        
        $product = $this->productModel->getById($id);
        if (!$product) {
            $this->redirect('admin/products');
        }
        
        $categories = $this->categoryModel->getAll();
        $product['gallery'] = $this->productModel->getProductImages($id, 'main');
        
        $data = [
            'product' => $product,
            'categories' => $categories
        ];
        
        $this->render('admin/product-edit', $data);
    }
    
    // Admin: Cập nhật sản phẩm
    public function adminUpdate($id = null) {
        $this->requireAdmin();
        
        if (!$id) {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        }
        
        if (!$id) {
            $this->redirect('admin/products');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/products');
        }
        
        $data = $this->getPostData();
        $data = $this->sanitize($data);
        
        // Validate required fields
        $requiredFields = ['name', 'category_id', 'price'];
        $errors = $this->validateRequired($data, $requiredFields);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_data'] = $data;
            $this->redirect("admin/products/edit?id={$id}");
        }
        
        // Handle file upload
        $uploadedImages = $this->handleImageUpload();
        
        // Update product
        $success = $this->productModel->update($id, $data);
        
        if ($success) {
            // Save new product images if any
            if (!empty($uploadedImages)) {
                $this->productModel->saveProductImages($id, $uploadedImages);
            }
            
            $_SESSION['success'] = 'Product updated successfully';
            $this->redirect('admin/products');
        } else {
            $_SESSION['errors'] = ['Failed to update product'];
            $_SESSION['old_data'] = $data;
            $this->redirect("admin/products/edit?id={$id}");
        }
    }
    
    // Admin: Xóa sản phẩm
    public function adminDelete($id = null) {
        $this->requireAdmin();
        
        if (!$id) {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        }
        
        if (!$id) {
            $this->redirect('admin/products');
        }
        
        $success = $this->productModel->delete($id);
        
        if ($success) {
            $_SESSION['success'] = 'Product deleted successfully';
        } else {
            $_SESSION['errors'] = ['Failed to delete product'];
        }
        
        $this->redirect('admin/products');
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
} 