<?php
require_once __DIR__ . '/../config/database.php';

class Product {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Lấy tất cả sản phẩm
    public function getAll($limit = null, $offset = null, $category_id = null, $search = null, $min_price = null, $max_price = null) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'active'";
        $params = [];
        
        if ($category_id) {
            $sql .= " AND p.category_id = ?";
            $params[] = $category_id;
        }
        
        if ($search) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($min_price !== null) {
            $sql .= " AND p.price >= ?";
            $params[] = $min_price;
        }
        
        if ($max_price !== null) {
            $sql .= " AND p.price <= ?";
            $params[] = $max_price;
        }
        
        $sql .= " ORDER BY p.id DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
            if ($offset) {
                $sql .= " OFFSET $offset";
            }
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Lấy sản phẩm theo ID
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT p.*, c.name as category_name 
                                    FROM products p 
                                    LEFT JOIN categories c ON p.category_id = c.id 
                                    WHERE p.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    // Lấy sản phẩm nổi bật
    public function getFeatured($limit = 8) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'active' AND p.is_available = 1 
                ORDER BY p.id DESC LIMIT " . (int)$limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Tạo sản phẩm mới
    public function create($data) {
        $this->pdo->beginTransaction();
        
        try {
            // Debug: Log data being inserted
            error_log("Creating product with data: " . json_encode($data));
            
            $sql = "INSERT INTO products (category_id, name, price, stock, description, 
                    image_des, image_, main_images, description_images, is_available, status, size, color, sale) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['category_id'],
                $data['name'],
                $data['price'],
                $data['stock'] ?? 0,
                $data['description'] ?? null,
                $data['image_des'] ?? null,
                $data['image_'] ?? null,
                $data['main_images'] ?? null,
                $data['description_images'] ?? null,
                $data['is_available'] ?? 1,
                $data['status'] ?? 'active',
                $data['size'] ?? null,
                $data['color'] ?? null,
                $data['sale'] ?? null
            ]);
            
            $product_id = $this->pdo->lastInsertId();
            error_log("Product inserted with ID: " . $product_id);
            
            // Lưu hình ảnh vào bảng product_images
            if (!empty($data['main_images_array'])) {
                foreach ($data['main_images_array'] as $index => $image_path) {
                    $this->addProductImage($product_id, $image_path, 'main', $index);
                }
            }
            
            if (!empty($data['description_images_array'])) {
                foreach ($data['description_images_array'] as $index => $image_path) {
                    $this->addProductImage($product_id, $image_path, 'description', $index);
                }
            }
            
            $this->pdo->commit();
            error_log("Transaction committed successfully. Returning product_id: " . $product_id);
            return $product_id; // Trả về product_id thay vì true
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Transaction rolled back due to error: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Cập nhật sản phẩm
    public function update($id, $data) {
        $this->pdo->beginTransaction();
        
        try {
            $sql = "UPDATE products SET 
                    category_id = ?, 
                    name = ?, 
                    price = ?, 
                    stock = ?, 
                    description = ?, 
                    image_des = ?, 
                    image_ = ?, 
                    main_images = ?, 
                    description_images = ?, 
                    is_available = ?, 
                    status = ?, 
                    size = ?, 
                    color = ?, 
                    sale = ? 
                    WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['category_id'],
                $data['name'],
                $data['price'],
                $data['stock'] ?? 0,
                $data['description'] ?? null,
                $data['image_des'] ?? null,
                $data['image_'] ?? null,
                $data['main_images'] ?? null,
                $data['description_images'] ?? null,
                $data['is_available'] ?? 1,
                $data['status'] ?? 'active',
                $data['size'] ?? null,
                $data['color'] ?? null,
                $data['sale'] ?? null,
                $id
            ]);
            
            // Cập nhật hình ảnh trong bảng product_images
            if (isset($data['main_images_array'])) {
                // Xóa hình ảnh cũ
                $this->deleteProductImages($id, 'main');
                // Thêm hình ảnh mới
                if (!empty($data['main_images_array'])) {
                    foreach ($data['main_images_array'] as $index => $image_path) {
                        $this->addProductImage($id, $image_path, 'main', $index);
                    }
                }
            }
            
            if (isset($data['description_images_array'])) {
                // Xóa hình ảnh cũ
                $this->deleteProductImages($id, 'description');
                // Thêm hình ảnh mới
                if (!empty($data['description_images_array'])) {
                    foreach ($data['description_images_array'] as $index => $image_path) {
                        $this->addProductImage($id, $image_path, 'description', $index);
                    }
                }
            }
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    // Xóa sản phẩm
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // Cập nhật stock
    public function updateStock($id, $quantity) {
        $stmt = $this->pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
        return $stmt->execute([$quantity, $id, $quantity]);
    }
    
    // Đếm tổng số sản phẩm
    public function count($category_id = null, $search = null) {
        $sql = "SELECT COUNT(*) FROM products p WHERE p.status = 'active'";
        
        $params = [];
        
        if ($category_id) {
            $sql .= " AND p.category_id = ?";
            $params[] = $category_id;
        }
        
        if ($search) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    // Lấy tổng số sản phẩm (alias cho count)
    public function getTotal($category_id = null, $search = null, $min_price = null, $max_price = null) {
        $sql = "SELECT COUNT(*) FROM products p WHERE p.status = 'active'";
        
        $params = [];
        
        if ($category_id) {
            $sql .= " AND p.category_id = ?";
            $params[] = $category_id;
        }
        
        if ($search) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($min_price !== null) {
            $sql .= " AND p.price >= ?";
            $params[] = $min_price;
        }
        
        if ($max_price !== null) {
            $sql .= " AND p.price <= ?";
            $params[] = $max_price;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    // Lấy sản phẩm theo danh mục
    public function getByCategory($category_id, $limit = null, $offset = null) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.category_id = ? AND p.status = 'active' 
                ORDER BY p.id DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
            if ($offset) {
                $sql .= " OFFSET $offset";
            }
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$category_id]);
        return $stmt->fetchAll();
    }
    
    // Tìm kiếm sản phẩm
    public function search($keyword, $limit = null, $offset = null) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'active' AND 
                      (p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?) 
                ORDER BY p.id DESC";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
            if ($offset) {
                $sql .= " OFFSET $offset";
            }
        }
        
        $keyword = "%$keyword%";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$keyword, $keyword, $keyword]);
        return $stmt->fetchAll();
    }
    
    // Lấy sản phẩm có giảm giá
    public function getOnSale($limit = null) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'active' AND p.sale IS NOT NULL AND p.sale > 0 
                ORDER BY p.sale DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Tính giá sau giảm giá
    public function getFinalPrice($product) {
        if ($product['sale'] && $product['sale'] > 0) {
            return $product['price'] - $product['sale'];
        }
        return $product['price'];
    }
    
    // Lấy chi tiết sản phẩm
    public function getDetails($product_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM detail_products WHERE id_products = ?");
        $stmt->execute([$product_id]);
        return $stmt->fetchAll();
    }
    
    // Thêm hình ảnh sản phẩm
    public function addProductImage($product_id, $image_path, $image_type = 'main', $sort_order = 0) {
        $sql = "INSERT INTO product_images (product_id, image_path, image_type, sort_order) VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$product_id, $image_path, $image_type, $sort_order]);
    }
    
    // Lấy hình ảnh sản phẩm
    public function getProductImages($product_id, $image_type = null) {
        $sql = "SELECT * FROM product_images WHERE product_id = ?";
        $params = [$product_id];
        
        if ($image_type) {
            $sql .= " AND image_type = ?";
            $params[] = $image_type;
        }
        
        $sql .= " ORDER BY sort_order ASC, id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // Xóa hình ảnh sản phẩm
    public function deleteProductImage($image_id) {
        $stmt = $this->pdo->prepare("DELETE FROM product_images WHERE id = ?");
        return $stmt->execute([$image_id]);
    }
    
    // Xóa tất cả hình ảnh của sản phẩm theo loại
    public function deleteProductImages($product_id, $image_type) {
        $stmt = $this->pdo->prepare("DELETE FROM product_images WHERE product_id = ? AND image_type = ?");
        return $stmt->execute([$product_id, $image_type]);
    }
    
    // Cập nhật thứ tự hình ảnh
    public function updateImageOrder($image_id, $sort_order) {
        $stmt = $this->pdo->prepare("UPDATE product_images SET sort_order = ? WHERE id = ?");
        return $stmt->execute([$sort_order, $image_id]);
    }
    
    // Lưu nhiều hình ảnh sản phẩm
    public function saveProductImages($product_id, $images, $image_type = 'main') {
        // Xóa hình ảnh cũ
        $this->deleteProductImages($product_id, $image_type);
        
        // Thêm hình ảnh mới
        foreach ($images as $index => $image_path) {
            $this->addProductImage($product_id, $image_path, $image_type, $index);
        }
        
        return true;
    }
    
    // Thêm chi tiết sản phẩm
    public function addDetail($product_id, $data) {
        // Validation
        if (!$product_id || $product_id <= 0) {
            throw new Exception("Invalid product_id: " . $product_id);
        }
        
        if (empty($data['name'])) {
            throw new Exception("Detail name cannot be empty");
        }
        
        $sql = "INSERT INTO detail_products (id_products, name, description) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $product_id,
            $data['name'],
            $data['description'] ?? null
        ]);
    }
    
    // Xóa chi tiết sản phẩm
    public function deleteDetail($detail_id) {
        $stmt = $this->pdo->prepare("DELETE FROM detail_products WHERE id = ?");
        return $stmt->execute([$detail_id]);
    }
    
    // Xóa tất cả chi tiết sản phẩm
    public function deleteAllDetails($product_id) {
        $stmt = $this->pdo->prepare("DELETE FROM detail_products WHERE id_products = ?");
        return $stmt->execute([$product_id]);
    }
    
    // Lấy sản phẩm nổi bật cho hero section
    public function getFeaturedProduct() {
        $stmt = $this->pdo->prepare("SELECT p.*, c.name as category_name 
                                    FROM products p 
                                    LEFT JOIN categories c ON p.category_id = c.id 
                                    WHERE p.status = 'active' AND p.is_available = 1 
                                    ORDER BY p.id DESC LIMIT 1");
        $stmt->execute();
        return $stmt->fetch();
    }
    
    // Lấy sản phẩm bán chạy
    public function getBestSellers($limit = 8) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'active' AND p.is_available = 1 
                ORDER BY p.id DESC LIMIT " . (int)$limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Lấy sản phẩm gợi ý
    public function getSuggestedProducts($limit = 8) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'active' AND p.is_available = 1 
                ORDER BY RAND() LIMIT " . (int)$limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Lấy sản phẩm hot deal
    public function getHotDeals($limit = 8) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'active' AND p.is_available = 1 AND p.sale IS NOT NULL AND p.sale > 0 
                ORDER BY p.sale DESC LIMIT " . (int)$limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Lấy sản phẩm mới
    public function getNewProducts($limit = 8) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.status = 'active' AND p.is_available = 1 
                ORDER BY p.id DESC LIMIT " . (int)$limit;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Lấy sản phẩm mới nhất (alias cho getNewProducts)
    public function getLatest($limit = 8) {
        return $this->getNewProducts($limit);
    }
    
    // Tính phần trăm giảm giá
    public function getDiscountPercent($product) {
        if ($product['sale'] && $product['sale'] > 0 && $product['price'] > 0) {
            return round(($product['sale'] / $product['price']) * 100);
        }
        return 0;
    }
    
    // Lấy giá gốc (original price)
    public function getOriginalPrice($product) {
        if ($product['sale'] && $product['sale'] > 0) {
            return $product['price'];
        }
        return $product['price'];
    }
    
    // Lấy giá hiện tại (current price)
    public function getCurrentPrice($product) {
        if ($product['sale'] && $product['sale'] > 0) {
            return $product['price'] - $product['sale'];
        }
        return $product['price'];
    }
}
?> 