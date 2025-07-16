<?php
require_once __DIR__ . '/../config/database.php';

class ProductOption {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Lấy tất cả options của sản phẩm
    public function getByProductId($product_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM product_options WHERE product_id = ? ORDER BY id ASC");
        $stmt->execute([$product_id]);
        $options = $stmt->fetchAll();
        foreach ($options as &$option) {
            $stmt2 = $this->pdo->prepare("SELECT * FROM product_option_values WHERE option_id = ? ORDER BY id ASC");
            $stmt2->execute([$option['id']]);
            $option['values'] = $stmt2->fetchAll();
        }
        return $options;
    }
    
    // Lấy option theo ID
    public function getById($option_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM product_options WHERE id = ?");
        $stmt->execute([$option_id]);
        return $stmt->fetch();
    }
    
    // Tạo option mới
    public function create($data) {
        $sql = "INSERT INTO product_options (product_id, name) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['product_id'],
            $data['name']
        ]);
        return $this->pdo->lastInsertId();
    }
    
    // Cập nhật option
    public function update($option_id, $data) {
        $sql = "UPDATE product_options SET 
                name = ?, 
                description = ?, 
                is_required = ?, 
                sort_order = ? 
                WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['is_required'] ?? false,
            $data['sort_order'] ?? 0,
            $option_id
        ]);
    }
    
    // Xóa option
    public function delete($option_id) {
        $stmt = $this->pdo->prepare("DELETE FROM product_options WHERE id = ?");
        return $stmt->execute([$option_id]);
    }
    
    // Xóa tất cả options của sản phẩm
    public function deleteByProductId($product_id) {
        $stmt = $this->pdo->prepare("DELETE FROM product_options WHERE product_id = ?");
        return $stmt->execute([$product_id]);
    }
    
    // Lấy option values theo option_id
    public function getOptionValues($option_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM product_option_values 
                                    WHERE option_id = ? 
                                    ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$option_id]);
        return $stmt->fetchAll();
    }
    
    // Tạo option value mới
    public function createOptionValue($data) {
        $sql = "INSERT INTO product_option_values (option_id, value, stock_quantity) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['option_id'],
            $data['value'],
            $data['stock_quantity'] ?? 0
        ]);
        return $this->pdo->lastInsertId();
    }
    
    // Cập nhật option value
    public function updateOptionValue($value_id, $data) {
        $sql = "UPDATE product_option_values SET 
                value = ?, 
                price_adjustment = ?, 
                stock_quantity = ?, 
                is_default = ?, 
                sort_order = ? 
                WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['value'],
            $data['price_adjustment'] ?? 0,
            $data['stock_quantity'] ?? 0,
            $data['is_default'] ?? false,
            $data['sort_order'] ?? 0,
            $value_id
        ]);
    }
    
    // Xóa option value
    public function deleteOptionValue($value_id) {
        $stmt = $this->pdo->prepare("DELETE FROM product_option_values WHERE id = ?");
        return $stmt->execute([$value_id]);
    }
    
    // Xóa tất cả option values của option
    public function deleteOptionValues($option_id) {
        $stmt = $this->pdo->prepare("DELETE FROM product_option_values WHERE option_id = ?");
        return $stmt->execute([$option_id]);
    }
    
    // Lấy product option combinations
    public function getCombinations($product_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM product_option_combinations 
                                    WHERE product_id = ? AND is_active = 1 
                                    ORDER BY id ASC");
        $stmt->execute([$product_id]);
        return $stmt->fetchAll();
    }
    
    // Tạo combination mới
    public function createCombination($data) {
        $sql = "INSERT INTO product_option_combinations 
                (product_id, option_values, sku, price_adjustment, stock_quantity) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['product_id'],
            $data['option_values'],
            $data['sku'] ?? null,
            $data['price_adjustment'] ?? 0,
            $data['stock_quantity'] ?? 0
        ]);
        return $this->pdo->lastInsertId();
    }
    
    // Cập nhật combination
    public function updateCombination($combination_id, $data) {
        $sql = "UPDATE product_option_combinations SET 
                option_values = ?, 
                sku = ?, 
                price_adjustment = ?, 
                stock_quantity = ?, 
                is_active = ? 
                WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['option_values'],
            $data['sku'] ?? null,
            $data['price_adjustment'] ?? 0,
            $data['stock_quantity'] ?? 0,
            $data['is_active'] ?? true,
            $combination_id
        ]);
    }
    
    // Xóa combination
    public function deleteCombination($combination_id) {
        $stmt = $this->pdo->prepare("DELETE FROM product_option_combinations WHERE id = ?");
        return $stmt->execute([$combination_id]);
    }
    
    // Xóa tất cả combinations của sản phẩm
    public function deleteCombinations($product_id) {
        $stmt = $this->pdo->prepare("DELETE FROM product_option_combinations WHERE product_id = ?");
        return $stmt->execute([$product_id]);
    }
    
    // Lưu toàn bộ options và values cho sản phẩm
    public function saveProductOptions($product_id, $options_data) {
        $this->pdo->beginTransaction();
        
        try {
            // Xóa tất cả options cũ
            $this->deleteByProductId($product_id);
            
            if (!empty($options_data)) {
                foreach ($options_data as $option_data) {
                    // Tạo option
                    $option_id = $this->create([
                        'product_id' => $product_id,
                        'name' => $option_data['name']
                    ]);
                    
                    // Tạo option values
                    if (!empty($option_data['values'])) {
                        foreach ($option_data['values'] as $value_data) {
                            $this->createOptionValue([
                                'option_id' => $option_id,
                                'value' => $value_data['value'],
                                'stock_quantity' => $value_data['stock_quantity'] ?? 0
                            ]);
                        }
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
    
    // Tính giá sản phẩm với options
    public function calculatePriceWithOptions($base_price, $selected_options) {
        $total_adjustment = 0;
        
        if (!empty($selected_options)) {
            foreach ($selected_options as $option_id => $value_id) {
                $stmt = $this->pdo->prepare("SELECT price_adjustment FROM product_option_values WHERE id = ?");
                $stmt->execute([$value_id]);
                $result = $stmt->fetch();
                
                if ($result) {
                    $total_adjustment += $result['price_adjustment'];
                }
            }
        }
        
        return $base_price + $total_adjustment;
    }
    
    // Kiểm tra stock của combination
    public function checkCombinationStock($product_id, $selected_options) {
        $option_values_json = json_encode($selected_options);
        
        $stmt = $this->pdo->prepare("SELECT stock_quantity FROM product_option_combinations 
                                    WHERE product_id = ? AND option_values = ? AND is_active = 1");
        $stmt->execute([$product_id, $option_values_json]);
        $result = $stmt->fetch();
        
        return $result ? $result['stock_quantity'] : 0;
    }
}
?> 