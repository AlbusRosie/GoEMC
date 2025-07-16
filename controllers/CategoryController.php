<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Product.php';

class CategoryController extends BaseController {
    private $categoryModel;
    private $productModel;
    
    public function __construct($conn = null) {
        parent::__construct();
        if ($conn) {
            $this->conn = $conn;
        }
        $this->categoryModel = new Category($this->conn);
        $this->productModel = new Product($this->conn);
    }
    
    // Hiển thị danh sách sản phẩm theo danh mục
    public function show($id = null) {
        if (!$id) {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        }
        
        if (!$id) {
            $this->redirect('index.php?page=products');
        }
        
        // Lấy thông tin danh mục
        $category = $this->categoryModel->getById($id);
        
        if (!$category) {
            $this->redirect('index.php?page=products');
        }
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;
        
        // Lấy sản phẩm theo danh mục
        $products = $this->productModel->getByCategory($id, $limit, null, $offset);
        $totalProducts = $this->productModel->getTotal($id);
        
        // Tính pagination
        $totalPages = ceil($totalProducts / $limit);
        
        // Lấy tất cả danh mục cho sidebar
        $categories = $this->categoryModel->getAll();
        
        $data = [
            'category' => $category,
            'products' => $products,
            'categories' => $categories,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalProducts' => $totalProducts
        ];
        
        $this->render('category', $data);
    }
    
    // API: Lấy tất cả danh mục
    public function getAll() {
        $categories = $this->categoryModel->getAll();
        $this->jsonResponse($categories);
    }
    
    // API: Lấy danh mục theo ID
    public function getById() {
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
    
    // Admin: Hiển thị danh sách danh mục (admin)
    public function adminIndex() {
        $this->requireAdmin();
        
        $categories = $this->categoryModel->getAll();
        
        $data = [
            'categories' => $categories
        ];
        
        $this->render('admin/categories', $data);
    }
    
    // Admin: Hiển thị form thêm danh mục
    public function adminCreate() {
        $this->requireAdmin();
        
        $data = [
            'category' => null
        ];
        
        $this->render('admin/category-add', $data);
    }
    
    // Admin: Lưu danh mục mới
    public function adminStore() {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/categories');
        }
        
        $data = $this->getPostData();
        $data = $this->sanitize($data);
        
        // Validate required fields
        $requiredFields = ['name'];
        $errors = $this->validateRequired($data, $requiredFields);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_data'] = $data;
            $this->redirect('admin/categories/create');
        }
        
        // Create category
        $categoryId = $this->categoryModel->create($data);
        
        if ($categoryId) {
            $_SESSION['success'] = 'Category created successfully';
            $this->redirect('admin/categories');
        } else {
            $_SESSION['errors'] = ['Failed to create category'];
            $_SESSION['old_data'] = $data;
            $this->redirect('admin/categories/create');
        }
    }
    
    // Admin: Hiển thị form chỉnh sửa danh mục
    public function adminEdit($id = null) {
        $this->requireAdmin();
        
        if (!$id) {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        }
        
        if (!$id) {
            $this->redirect('admin/categories');
        }
        
        $category = $this->categoryModel->getById($id);
        if (!$category) {
            $this->redirect('admin/categories');
        }
        
        $data = [
            'category' => $category
        ];
        
        $this->render('admin/category-edit', $data);
    }
    
    // Admin: Cập nhật danh mục
    public function adminUpdate($id = null) {
        $this->requireAdmin();
        
        if (!$id) {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        }
        
        if (!$id) {
            $this->redirect('admin/categories');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/categories');
        }
        
        $data = $this->getPostData();
        $data = $this->sanitize($data);
        
        // Validate required fields
        $requiredFields = ['name'];
        $errors = $this->validateRequired($data, $requiredFields);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_data'] = $data;
            $this->redirect("admin/categories/edit?id={$id}");
        }
        
        // Update category
        $success = $this->categoryModel->update($id, $data);
        
        if ($success) {
            $_SESSION['success'] = 'Category updated successfully';
            $this->redirect('admin/categories');
        } else {
            $_SESSION['errors'] = ['Failed to update category'];
            $_SESSION['old_data'] = $data;
            $this->redirect("admin/categories/edit?id={$id}");
        }
    }
    
    // Admin: Xóa danh mục
    public function adminDelete($id = null) {
        $this->requireAdmin();
        
        if (!$id) {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        }
        
        if (!$id) {
            $this->redirect('admin/categories');
        }
        
        // Check if category has products
        $products = $this->productModel->getByCategory($id, 1);
        if (!empty($products)) {
            $_SESSION['errors'] = ['Cannot delete category with existing products'];
            $this->redirect('admin/categories');
        }
        
        $success = $this->categoryModel->delete($id);
        
        if ($success) {
            $_SESSION['success'] = 'Category deleted successfully';
        } else {
            $_SESSION['errors'] = ['Failed to delete category'];
        }
        
        $this->redirect('admin/categories');
    }
    
    // Admin: Quản lý detail categories
    public function adminDetailCategories($categoryId = null) {
        $this->requireAdmin();
        
        if (!$categoryId) {
            $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
        }
        
        if (!$categoryId) {
            $this->redirect('admin/categories');
        }
        
        $category = $this->categoryModel->getById($categoryId);
        if (!$category) {
            $this->redirect('admin/categories');
        }
        
        $detailCategories = $this->categoryModel->getDetailCategories($categoryId);
        
        $data = [
            'category' => $category,
            'detailCategories' => $detailCategories
        ];
        
        $this->render('admin/detail-categories', $data);
    }
    
    // Admin: Thêm detail category
    public function adminCreateDetailCategory() {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/categories');
        }
        
        $data = $this->getPostData();
        $data = $this->sanitize($data);
        
        // Validate required fields
        $requiredFields = ['name', 'category_id'];
        $errors = $this->validateRequired($data, $requiredFields);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_data'] = $data;
            $this->redirect("admin/categories/detail?category_id={$data['category_id']}");
        }
        
        // Create detail category
        $success = $this->categoryModel->createDetailCategory($data);
        
        if ($success) {
            $_SESSION['success'] = 'Detail category created successfully';
        } else {
            $_SESSION['errors'] = ['Failed to create detail category'];
        }
        
        $this->redirect("admin/categories/detail?category_id={$data['category_id']}");
    }
    
    // Admin: Cập nhật detail category
    public function adminUpdateDetailCategory() {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/categories');
        }
        
        $data = $this->getPostData();
        $data = $this->sanitize($data);
        
        // Validate required fields
        $requiredFields = ['id', 'name'];
        $errors = $this->validateRequired($data, $requiredFields);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_data'] = $data;
            $this->redirect("admin/categories/detail?category_id={$data['category_id']}");
        }
        
        // Update detail category
        $success = $this->categoryModel->updateDetailCategory($data['id'], $data);
        
        if ($success) {
            $_SESSION['success'] = 'Detail category updated successfully';
        } else {
            $_SESSION['errors'] = ['Failed to update detail category'];
        }
        
        $this->redirect("admin/categories/detail?category_id={$data['category_id']}");
    }
    
    // Admin: Xóa detail category
    public function adminDeleteDetailCategory() {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/categories');
        }
        
        $data = $this->getPostData();
        $id = (int)$data['id'] ?? 0;
        $categoryId = (int)$data['category_id'] ?? 0;
        
        if (!$id || !$categoryId) {
            $this->redirect('admin/categories');
        }
        
        $success = $this->categoryModel->deleteDetailCategory($id);
        
        if ($success) {
            $_SESSION['success'] = 'Detail category deleted successfully';
        } else {
            $_SESSION['errors'] = ['Failed to delete detail category'];
        }
        
        $this->redirect("admin/categories/detail?category_id={$categoryId}");
    }
} 