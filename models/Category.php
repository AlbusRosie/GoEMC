<?php
require_once __DIR__ . '/../config/database.php';

class Category {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Lấy tất cả danh mục
    public function getAll($status = 'active') {
        $sql = "SELECT * FROM categories";
        $params = [];
        
        if ($status) {
            $sql .= " WHERE status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY name";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Lấy danh mục theo ID
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Tạo danh mục mới
    public function create($data) {
        $sql = "INSERT INTO categories (name, status) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['status'] ?? 'active'
        ]);
    }
    
    // Cập nhật danh mục
    public function update($id, $data) {
        $sql = "UPDATE categories SET name = ?, status = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['status'] ?? 'active',
            $id
        ]);
    }
    
    // Xóa danh mục
    public function delete($id) {
        // Kiểm tra xem có sản phẩm nào trong danh mục không
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            return false; // Không thể xóa vì có sản phẩm
        }
        
        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // Đếm số sản phẩm trong danh mục
    public function countProducts($category_id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ? AND status = 'active'");
        $stmt->execute([$category_id]);
        return $stmt->fetchColumn();
    }
    
    // Lấy danh mục với số lượng sản phẩm
    public function getAllWithProductCount() {
        $sql = "SELECT c.*, COUNT(p.id) as product_count 
                FROM categories c 
                LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active' 
                WHERE c.status = 'active' 
                GROUP BY c.id 
                ORDER BY c.name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Kiểm tra tên danh mục tồn tại
    public function nameExists($name, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM categories WHERE name = ?";
        $params = [$name];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
    
    // Lấy chi tiết danh mục
    public function getDetails($category_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM detail_categories WHERE id_categories = ?");
        $stmt->execute([$category_id]);
        return $stmt->fetchAll();
    }
    
    // Thêm chi tiết danh mục
    public function addDetail($category_id, $data) {
        $sql = "INSERT INTO detail_categories (id_categories, name, description) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $category_id,
            $data['name'],
            $data['description'] ?? null
        ]);
    }
    
    // Cập nhật chi tiết danh mục
    public function updateDetail($detail_id, $data) {
        $sql = "UPDATE detail_categories SET name = ?, description = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $detail_id
        ]);
    }
    
    // Xóa chi tiết danh mục
    public function deleteDetail($detail_id) {
        $stmt = $this->pdo->prepare("DELETE FROM detail_categories WHERE id = ?");
        return $stmt->execute([$detail_id]);
    }
    
    // Lấy danh mục theo slug (nếu có)
    public function getBySlug($slug) {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE LOWER(REPLACE(name, ' ', '-')) = ? AND status = 'active'");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }
    
    // Tạo slug từ tên danh mục
    public function createSlug($name) {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
    
    // Lấy danh mục cha và con (nếu có cấu trúc phân cấp)
    public function getHierarchy() {
        $categories = $this->getAll();
        $hierarchy = [];
        
        foreach ($categories as $category) {
            $hierarchy[] = [
                'id' => $category['id'],
                'name' => $category['name'],
                'status' => $category['status'],
                'product_count' => $this->countProducts($category['id'])
            ];
        }
        
        return $hierarchy;
    }
    
    // Tìm kiếm danh mục
    public function search($keyword) {
        $sql = "SELECT * FROM categories WHERE name LIKE ? AND status = 'active' ORDER BY name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(["%$keyword%"]);
        return $stmt->fetchAll();
    }
    
    // Lấy danh mục phổ biến (có nhiều sản phẩm)
    public function getPopular($limit = 5) {
        $sql = "SELECT c.*, COUNT(p.id) as product_count 
                FROM categories c 
                LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active' 
                WHERE c.status = 'active' 
                GROUP BY c.id 
                HAVING product_count > 0 
                ORDER BY product_count DESC 
                LIMIT " . (int)$limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Lấy tổng số danh mục
    public function getTotal() {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM categories WHERE status = 'active'");
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    
    // Lấy detail categories theo category_id
    public function getDetailCategories($category_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM detail_categories WHERE id_categories = ? ORDER BY name");
        $stmt->execute([$category_id]);
        return $stmt->fetchAll();
    }
    
    // Tạo detail category
    public function createDetailCategory($data) {
        $sql = "INSERT INTO detail_categories (id_categories, name, description) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['category_id'],
            $data['name'],
            $data['description'] ?? null
        ]);
    }
    
    // Cập nhật detail category
    public function updateDetailCategory($detail_id, $data) {
        $sql = "UPDATE detail_categories SET name = ?, description = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $detail_id
        ]);
    }
    
    // Xóa detail category
    public function deleteDetailCategory($detail_id) {
        $stmt = $this->pdo->prepare("DELETE FROM detail_categories WHERE id = ?");
        return $stmt->execute([$detail_id]);
    }
}
?> 