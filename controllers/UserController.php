<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';

class UserController extends BaseController {
    private $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User($this->conn);
    }
    
    // Hiển thị trang đăng nhập
    public function login() {
        if ($this->isLoggedIn()) {
            $this->redirect('admin/index.php');
        }
        
        $data = [
            'error' => $_SESSION['error'] ?? null,
            'old_data' => $_SESSION['old_data'] ?? []
        ];
        
        // Clear session messages
        unset($_SESSION['error'], $_SESSION['old_data']);
        
        $this->render('admin/login', $data);
    }
    
    // Xử lý đăng nhập
    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/login.php');
        }
        
        $data = $this->getPostData();
        $data = $this->sanitize($data);
        
        // Validate required fields
        $requiredFields = ['username', 'password'];
        $errors = $this->validateRequired($data, $requiredFields);
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode(', ', $errors);
            $_SESSION['old_data'] = $data;
            $this->redirect('admin/login.php');
        }
        
        // Authenticate user
        $user = $this->userModel->authenticate($data['username'], $data['password']);
        
        if ($user) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];
            
            $this->redirect('admin/index.php');
        } else {
            $_SESSION['error'] = 'Invalid username or password';
            $_SESSION['old_data'] = $data;
            $this->redirect('admin/login.php');
        }
    }
    
    // Đăng xuất
    public function logout() {
        session_destroy();
        $this->redirect('admin/login.php');
    }
    
    // Hiển thị trang đăng ký
    public function register() {
        if ($this->isLoggedIn()) {
            $this->redirect('index.php?page=home');
        }
        
        $data = [
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null,
            'old_data' => $_SESSION['old_data'] ?? []
        ];
        
        // Clear session messages
        unset($_SESSION['error'], $_SESSION['success'], $_SESSION['old_data']);
        
        $this->render('register', $data);
    }
    
    // Xử lý đăng ký
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?page=register');
        }
        
        $data = $this->getPostData();
        $data = $this->sanitize($data);
        
        // Validate required fields
        $requiredFields = ['username', 'password', 'confirm_password', 'email', 'name'];
        $errors = $this->validateRequired($data, $requiredFields);
        
        // Validate password confirmation
        if ($data['password'] !== $data['confirm_password']) {
            $errors[] = 'Password confirmation does not match';
        }
        
        // Validate password strength
        if (strlen($data['password']) < 6) {
            $errors[] = 'Password must be at least 6 characters long';
        }
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        // Check if username already exists
        if ($this->userModel->getByUsername($data['username'])) {
            $errors[] = 'Username already exists';
        }
        
        // Check if email already exists
        if ($this->userModel->getByEmail($data['email'])) {
            $errors[] = 'Email already exists';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode(', ', $errors);
            $_SESSION['old_data'] = $data;
            $this->redirect('index.php?page=register');
        }
        
        // Create user
        $userData = [
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'email' => $data['email'],
            'name' => $data['name'],
            'role' => 'user' // Default role
        ];
        
        $userId = $this->userModel->create($userData);
        
        if ($userId) {
            $_SESSION['success'] = 'Registration successful. Please login.';
            $this->redirect('index.php?page=login');
        } else {
            $_SESSION['error'] = 'Registration failed. Please try again.';
            $_SESSION['old_data'] = $data;
            $this->redirect('index.php?page=register');
        }
    }
    
    // Hiển thị profile người dùng
    public function profile() {
        $this->requireAuth();
        
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getById($userId);
        
        if (!$user) {
            $this->redirect('index.php?page=home');
        }
        
        $data = [
            'user' => $user,
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];
        
        // Clear session messages
        unset($_SESSION['error'], $_SESSION['success']);
        
        $this->render('profile', $data);
    }
    
    // Cập nhật profile
    public function updateProfile() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?page=profile');
        }
        
        $userId = $_SESSION['user_id'];
        $data = $this->getPostData();
        $data = $this->sanitize($data);
        
        // Validate required fields
        $requiredFields = ['name', 'email'];
        $errors = $this->validateRequired($data, $requiredFields);
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        // Check if email already exists (excluding current user)
        $existingUser = $this->userModel->getByEmail($data['email']);
        if ($existingUser && $existingUser['id'] != $userId) {
            $errors[] = 'Email already exists';
        }
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode(', ', $errors);
            $this->redirect('index.php?page=profile');
        }
        
        // Update user
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email']
        ];
        
        // Update password if provided
        if (!empty($data['new_password'])) {
            if (strlen($data['new_password']) < 6) {
                $_SESSION['error'] = 'Password must be at least 6 characters long';
                $this->redirect('index.php?page=profile');
            }
            
            if ($data['new_password'] !== $data['confirm_password']) {
                $_SESSION['error'] = 'Password confirmation does not match';
                $this->redirect('index.php?page=profile');
            }
            
            $updateData['password'] = password_hash($data['new_password'], PASSWORD_DEFAULT);
        }
        
        $success = $this->userModel->update($userId, $updateData);
        
        if ($success) {
            $_SESSION['success'] = 'Profile updated successfully';
            // Update session data
            $_SESSION['user_name'] = $data['name'];
        } else {
            $_SESSION['error'] = 'Failed to update profile';
        }
        
        $this->redirect('index.php?page=profile');
    }
    
    // Admin: Hiển thị danh sách người dùng
    public function adminIndex() {
        $this->requireAdmin();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $users = $this->userModel->getAll($limit, $offset);
        $totalUsers = $this->userModel->getTotal();
        $totalPages = ceil($totalUsers / $limit);
        
        $data = [
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalUsers' => $totalUsers
        ];
        
        $this->render('admin/users', $data);
    }
    
    // Admin: Hiển thị form thêm người dùng
    public function adminCreate() {
        $this->requireAdmin();
        
        $data = [
            'user' => null
        ];
        
        $this->render('admin/user-add', $data);
    }
    
    // Admin: Lưu người dùng mới
    public function adminStore() {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/users');
        }
        
        $data = $this->getPostData();
        $data = $this->sanitize($data);
        
        // Validate required fields
        $requiredFields = ['username', 'password', 'email', 'name', 'role'];
        $errors = $this->validateRequired($data, $requiredFields);
        
        // Validate password strength
        if (strlen($data['password']) < 6) {
            $errors[] = 'Password must be at least 6 characters long';
        }
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        // Check if username already exists
        if ($this->userModel->getByUsername($data['username'])) {
            $errors[] = 'Username already exists';
        }
        
        // Check if email already exists
        if ($this->userModel->getByEmail($data['email'])) {
            $errors[] = 'Email already exists';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_data'] = $data;
            $this->redirect('admin/users/create');
        }
        
        // Create user
        $userData = [
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'email' => $data['email'],
            'name' => $data['name'],
            'role' => $data['role']
        ];
        
        $userId = $this->userModel->create($userData);
        
        if ($userId) {
            $_SESSION['success'] = 'User created successfully';
            $this->redirect('admin/users');
        } else {
            $_SESSION['errors'] = ['Failed to create user'];
            $_SESSION['old_data'] = $data;
            $this->redirect('admin/users/create');
        }
    }
    
    // Admin: Hiển thị form chỉnh sửa người dùng
    public function adminEdit($id = null) {
        $this->requireAdmin();
        
        if (!$id) {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        }
        
        if (!$id) {
            $this->redirect('admin/users');
        }
        
        $user = $this->userModel->getById($id);
        if (!$user) {
            $this->redirect('admin/users');
        }
        
        $data = [
            'user' => $user
        ];
        
        $this->render('admin/user-edit', $data);
    }
    
    // Admin: Cập nhật người dùng
    public function adminUpdate($id = null) {
        $this->requireAdmin();
        
        if (!$id) {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        }
        
        if (!$id) {
            $this->redirect('admin/users');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/users');
        }
        
        $data = $this->getPostData();
        $data = $this->sanitize($data);
        
        // Validate required fields
        $requiredFields = ['username', 'email', 'name', 'role'];
        $errors = $this->validateRequired($data, $requiredFields);
        
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        // Check if username already exists (excluding current user)
        $existingUser = $this->userModel->getByUsername($data['username']);
        if ($existingUser && $existingUser['id'] != $id) {
            $errors[] = 'Username already exists';
        }
        
        // Check if email already exists (excluding current user)
        $existingUser = $this->userModel->getByEmail($data['email']);
        if ($existingUser && $existingUser['id'] != $id) {
            $errors[] = 'Email already exists';
        }
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_data'] = $data;
            $this->redirect("admin/users/edit?id={$id}");
        }
        
        // Update user
        $updateData = [
            'username' => $data['username'],
            'email' => $data['email'],
            'name' => $data['name'],
            'role' => $data['role']
        ];
        
        // Update password if provided
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                $_SESSION['errors'] = ['Password must be at least 6 characters long'];
                $_SESSION['old_data'] = $data;
                $this->redirect("admin/users/edit?id={$id}");
            }
            
            $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $success = $this->userModel->update($id, $updateData);
        
        if ($success) {
            $_SESSION['success'] = 'User updated successfully';
            $this->redirect('admin/users');
        } else {
            $_SESSION['errors'] = ['Failed to update user'];
            $_SESSION['old_data'] = $data;
            $this->redirect("admin/users/edit?id={$id}");
        }
    }
    
    // Admin: Xóa người dùng
    public function adminDelete($id = null) {
        $this->requireAdmin();
        
        if (!$id) {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        }
        
        if (!$id) {
            $this->redirect('admin/users');
        }
        
        // Prevent deleting self
        if ($id == $_SESSION['user_id']) {
            $_SESSION['errors'] = ['Cannot delete your own account'];
            $this->redirect('admin/users');
        }
        
        $success = $this->userModel->delete($id);
        
        if ($success) {
            $_SESSION['success'] = 'User deleted successfully';
        } else {
            $_SESSION['errors'] = ['Failed to delete user'];
        }
        
        $this->redirect('admin/users');
    }
} 