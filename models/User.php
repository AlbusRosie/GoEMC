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
            
            if ($user && password_verify($password, $user['password'])) {
                return $user;
            }
            return false;
        } catch(PDOException $e) {
            return false;
        }
    }
    
    public function register($data) {
        try {
            // Kiểm tra email hoặc phone đã tồn tại
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ? OR phone = ?");
            $stmt->execute([$data['email'], $data['phone']]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Email hoặc số điện thoại đã tồn tại'];
            }

            $stmt = $this->pdo->prepare("
                INSERT INTO users (role_id, password, email, phone, name, address, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'active')
            ");
            
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            return $stmt->execute([
                2, // role_id = 2 (user)
                $hashedPassword,
                $data['email'],
                $data['phone'],
                $data['name'],
                $data['address']
            ]);
        } catch(PDOException $e) {
            return false;
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