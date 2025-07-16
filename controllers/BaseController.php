<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class BaseController {
    protected $db;
    protected $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    // Render view với data
    protected function render($view, $data = []) {
        // Extract data thành variables
        extract($data);
        
        // Include view file
        $viewPath = __DIR__ . '/../pages/' . $view . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new Exception("View {$view} not found");
        }
    }
    
    // Redirect
    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }
    
    // JSON response
    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    // Check if user is logged in
    protected function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    // Check if user is admin
    protected function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    
    // Require authentication
    protected function requireAuth() {
        if (!$this->isLoggedIn()) {
            $this->redirect('index.php?page=login');
        }
    }
    
    // Require admin
    protected function requireAdmin() {
        $this->requireAuth();
        if (!$this->isAdmin()) {
            $this->redirect('index.php?page=home');
        }
    }
    
    // Get POST data
    protected function getPostData() {
        return $_POST;
    }
    
    // Get GET data
    protected function getGetData() {
        return $_GET;
    }
    
    // Validate required fields
    protected function validateRequired($data, $fields) {
        $errors = [];
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                $errors[] = "Field {$field} is required";
            }
        }
        return $errors;
    }
    
    // Sanitize input
    protected function sanitize($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitize'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
} 