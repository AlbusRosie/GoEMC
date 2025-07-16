<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function login($emailOrPhone, $password) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT u.*, r.name as role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE (u.email = ? OR u.phone = ?) AND u.status = 'active'
            ");
            $stmt->execute([$emailOrPhone, $emailOrPhone]);
            $user = $stmt->fetch();
            
            // Debug log
            error_log("User lookup result: " . ($user ? "Found user ID: " . $user['id'] : "No user found"));
            
            if ($user && password_verify($password, $user['password'])) {
                error_log("Password verification successful");
                return $user;
            } else {
                error_log("Password verification failed");
            }
            return false;
        } catch(PDOException $e) {
            error_log("Database error in login: " . $e->getMessage());
            return false;
        }
    }
    
    public function register($data) {
        try {
            // Xử lý email và phone - ít nhất một trong hai phải có giá trị
            $email = !empty($data['email']) ? $data['email'] : null;
            $phone = !empty($data['phone']) ? $data['phone'] : null;
            
            if (empty($email) && empty($phone)) {
                return ['success' => false, 'message' => 'Vui lòng nhập email hoặc số điện thoại'];
            }
            
            // Kiểm tra email đã tồn tại
            if (!empty($email)) {
                $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    return ['success' => false, 'message' => 'Email đã tồn tại'];
                }
            }
            
            // Kiểm tra phone đã tồn tại
            if (!empty($phone)) {
                $stmt = $this->pdo->prepare("SELECT * FROM users WHERE phone = ?");
                $stmt->execute([$phone]);
                if ($stmt->fetch()) {
                    return ['success' => false, 'message' => 'Số điện thoại đã tồn tại'];
                }
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO users (role_id, password, email, phone, name, address, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'active')
            ");
            
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $success = $stmt->execute([
                2, // role_id = 2 (user)
                $hashedPassword,
                $email,
                $phone,
                $data['name'],
                $data['address']
            ]);
            
            if ($success) {
                return ['success' => true, 'message' => 'Đăng ký thành công'];
            } else {
                return ['success' => false, 'message' => 'Đăng ký thất bại, vui lòng thử lại'];
            }
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()];
        }
    }
    
    public function getById($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT u.*, r.name as role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function update($id, $data) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE users 
                SET name = ?, email = ?, phone = ?, address = ? 
                WHERE id = ?
            ");
            
            return $stmt->execute([
                $data['name'],
                $data['email'],
                $data['phone'],
                $data['address'],
                $id
            ]);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function changePassword($id, $newPassword) {
        try {
            $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            return $stmt->execute([$hashedPassword, $id]);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function getAll($limit = null, $offset = null) {
        try {
            $sql = "
                SELECT u.*, r.name as role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                ORDER BY u.created_at DESC
            ";
            
            if ($limit) {
                $sql .= " LIMIT " . (int)$limit;
                if ($offset) {
                    $sql .= " OFFSET " . (int)$offset;
                }
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            return [];
        }
    }
    
    public function count() {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM users");
            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            return 0;
        }
    }
    
    public function delete($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
            return $stmt->execute([$id]);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function checkEmailExists($email, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
            $params = [$email];
            
            if ($excludeId) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Authentication method
    public function authenticate($username, $password) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT u.*, r.name as role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.email = ? AND u.status = 'active'
            ");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                return $user;
            }
            return false;
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Create user
    public function create($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO users (role_id, password, email, phone, name, address, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'active')
            ");
            
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            return $stmt->execute([
                $data['role_id'] ?? 2, // Default to user role
                $hashedPassword,
                $data['email'],
                $data['phone'] ?? null,
                $data['name'],
                $data['address'] ?? null
            ]);
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Get user by username/email
    public function getByUsername($username) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT u.*, r.name as role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.email = ?
            ");
            $stmt->execute([$username]);
            return $stmt->fetch();
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Get user by email
    public function getByEmail($email) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT u.*, r.name as role_name 
                FROM users u 
                JOIN roles r ON u.role_id = r.id 
                WHERE u.email = ?
            ");
            $stmt->execute([$email]);
            return $stmt->fetch();
        } catch(PDOException $e) {
            return false;
        }
    }
    
    // Get total users
    public function getTotal() {
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM users");
            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            return 0;
        }
    }
}
?> 