<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';

class UserController extends BaseController {
    private $userModel;
    
    public function __construct($conn = null) {
        parent::__construct();
        if ($conn) {
            $this->conn = $conn;
        }
        $this->userModel = new User($this->conn);
    }
    
    // Xử lý đăng ký
    public function register() {
        global $conn;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
            $userModel = new User($conn);
            $data = [
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'name' => trim($_POST['name'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'role_id' => 2
            ];
            
            // Validation
            if (empty($data['email']) && empty($data['phone'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Vui lòng nhập email hoặc số điện thoại']);
                return;
            }
            if (empty($data['password']) || empty($data['name'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin']);
                return;
            }
            if (strlen($data['password']) < 6) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự']);
                return;
            }
            
            $result = $userModel->register($data);
            header('Content-Type: application/json');
            echo json_encode($result);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }

    // Xử lý đăng nhập
    public function login() {
        global $conn;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
            $userModel = new User($conn);
            $emailOrPhone = trim($_POST['email_or_phone'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($emailOrPhone) || empty($password)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin']);
                return;
            }
            
            $user = $userModel->login($emailOrPhone, $password);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Đăng nhập thành công']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Sai thông tin đăng nhập']);
            }
            return;
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }

    // Đăng xuất
    public function logout() {
        session_destroy();
        header('Location: index.php');
        exit;
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