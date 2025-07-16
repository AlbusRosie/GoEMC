-- Tạo CSDL
CREATE DATABASE IF NOT EXISTS go;
USE go;

-- Bảng roles
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Bảng users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    password VARCHAR(255) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    phone VARCHAR(15) DEFAULT NULL,
    name VARCHAR(100) NOT NULL,
    address VARCHAR(255) DEFAULT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT,
    CONSTRAINT unique_email_or_phone UNIQUE (email, phone)
);

-- Bảng categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Bảng detail_categories
CREATE TABLE IF NOT EXISTS detail_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_categories INT NOT NULL,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    FOREIGN KEY (id_categories) REFERENCES categories(id) ON DELETE CASCADE
);

-- Bảng products
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    description TEXT,
    image_des VARCHAR(255) DEFAULT NULL,
    image_ VARCHAR(255) DEFAULT NULL,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active',
    size VARCHAR(255) DEFAULT NULL,
    color VARCHAR(255) DEFAULT NULL,
    sale DECIMAL(10,2) DEFAULT NULL,
    sold_count INT DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT NULL,
    review_count INT DEFAULT 0,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Bảng detail_products
CREATE TABLE IF NOT EXISTS detail_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_products INT NOT NULL,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    FOREIGN KEY (id_products) REFERENCES products(id) ON DELETE CASCADE
);

-- Bảng product_images để lưu nhiều hình ảnh cho sản phẩm
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `image_type` enum('main','description') NOT NULL DEFAULT 'main',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `image_type` (`image_type`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tạo bảng product_options (các tùy chọn của sản phẩm)
CREATE TABLE IF NOT EXISTS product_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    is_required TINYINT(1) DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Tạo bảng product_option_values (các giá trị của từng option)
CREATE TABLE IF NOT EXISTS product_option_values (
    id INT AUTO_INCREMENT PRIMARY KEY,
    option_id INT NOT NULL,
    value VARCHAR(255) NOT NULL,
    stock_quantity INT DEFAULT 0,
    price_adjustment DECIMAL(10,2) DEFAULT 0.00,
    FOREIGN KEY (option_id) REFERENCES product_options(id) ON DELETE CASCADE
);

-- Bảng cart (giỏ hàng)
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    session_id VARCHAR(255) DEFAULT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    selected_options JSON DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id, selected_options(1000)),
    UNIQUE KEY unique_session_cart_item (session_id, product_id, selected_options(1000))
);

-- Bảng orders
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    guest_name VARCHAR(100) DEFAULT NULL,
    guest_email VARCHAR(100) DEFAULT NULL,
    guest_phone VARCHAR(15) DEFAULT NULL,
    total DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    shipping_fee DECIMAL(10,2) DEFAULT 0.00,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pending', 'confirmed', 'preparing', 'shipping', 'delivered', 'cancelled', 'completed') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method ENUM('cash', 'card', 'bank_transfer', 'momo', 'zalopay') DEFAULT NULL,
    delivery_address VARCHAR(255) DEFAULT NULL,
    delivery_city VARCHAR(100) DEFAULT NULL,
    delivery_district VARCHAR(100) DEFAULT NULL,
    delivery_ward VARCHAR(100) DEFAULT NULL,
    delivery_notes TEXT,
    coupon_code VARCHAR(50) DEFAULT NULL,
    coupon_discount DECIMAL(10,2) DEFAULT 0.00,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Bảng order_details
CREATE TABLE IF NOT EXISTS order_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    selected_options JSON DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Bảng coupons (mã giảm giá)
CREATE TABLE IF NOT EXISTS coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    min_order_amount DECIMAL(10,2) DEFAULT 0.00,
    max_discount DECIMAL(10,2) DEFAULT NULL,
    usage_limit INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    valid_from DATETIME NOT NULL,
    valid_to DATETIME NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Bảng order_status_history (lịch sử trạng thái đơn hàng)
CREATE TABLE IF NOT EXISTS order_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'shipping', 'delivered', 'cancelled', 'completed') NOT NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Thêm dữ liệu mẫu cho roles
INSERT INTO roles (name) VALUES 
('admin'),
('user');

-- Thêm dữ liệu mẫu cho users (admin)
INSERT INTO users (role_id, password, email, phone, name, address, status) VALUES 
(1, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@thanhlygo.com', '0901234567', 'Administrator', 'TP.HCM', 'active');

-- Thêm dữ liệu mẫu cho categories
INSERT INTO categories (name, status) VALUES 
('Gỗ tự nhiên', 'active'),
('Gỗ công nghiệp', 'active'),
('Nội thất gỗ', 'active'),
('Ván gỗ', 'active'),
('Gỗ tấm', 'active');

-- Thêm dữ liệu mẫu cho detail_categories
INSERT INTO detail_categories (id_categories, name, description) VALUES 
(1, 'Gỗ sồi', 'Gỗ sồi tự nhiên chất lượng cao'),
(1, 'Gỗ thông', 'Gỗ thông tự nhiên giá rẻ'),
(2, 'MDF', 'Ván MDF công nghiệp'),
(2, 'Plywood', 'Ván Plywood đa lớp'),
(3, 'Bàn gỗ', 'Bàn gỗ nội thất'),
(3, 'Ghế gỗ', 'Ghế gỗ nội thất'),
(4, 'Ván ép', 'Ván ép gỗ công nghiệp'),
(5, 'Gỗ tấm 4x8', 'Gỗ tấm kích thước 4x8 feet');

-- Thêm dữ liệu mẫu cho products
INSERT INTO products (category_id, name, price, stock, description, image_des, image_, is_available, status, size, color, sale, sold_count, rating, review_count) VALUES 
(1, 'Gỗ sồi đỏ tự nhiên', 2500000.00, 50, 'Gỗ sồi đỏ tự nhiên chất lượng cao, phù hợp làm nội thất', 'gỗ sồi đỏ', 'go-soi-do.jpg', 1, 'active', '4x8 feet', '["Đỏ tự nhiên", "Nâu đậm"]', 2200000.00, 15, 4.8, 23),
(1, 'Gỗ thông trắng', 1200000.00, 30, 'Gỗ thông trắng tự nhiên, giá rẻ phù hợp nhiều mục đích', 'gỗ thông trắng', 'go-thong-trang.jpg', 1, 'active', '4x8 feet', '["Trắng tự nhiên", "Vàng"]', NULL, 8, 4.5, 12),
(2, 'Ván MDF 18mm', 450000.00, 100, 'Ván MDF công nghiệp độ dày 18mm, chất lượng tốt', 'ván MDF 18mm', 'van-mdf-18mm.jpg', 1, 'active', '4x8 feet', '["Trắng", "Nâu"]', 400000.00, 45, 4.6, 18),
(2, 'Ván Plywood 12mm', 380000.00, 80, 'Ván Plywood đa lớp 12mm, bền chắc', 'ván Plywood 12mm', 'van-plywood-12mm.jpg', 1, 'active', '4x8 feet', '["Nâu", "Đen"]', NULL, 22, 4.7, 15),
(3, 'Bàn gỗ sồi 1.2m', 3500000.00, 10, 'Bàn gỗ sồi tự nhiên kích thước 1.2m, thiết kế hiện đại', 'bàn gỗ sồi', 'ban-go-soi.jpg', 1, 'active', '120x60x75cm', '["Nâu tự nhiên", "Đen"]', 3200000.00, 5, 4.9, 8),
(3, 'Ghế gỗ thông', 850000.00, 25, 'Ghế gỗ thông tự nhiên, thiết kế đơn giản', 'ghế gỗ thông', 'ghe-go-thong.jpg', 1, 'active', '45x45x90cm', '["Vàng tự nhiên", "Nâu"]', 750000.00, 18, 4.4, 11),
(4, 'Ván ép 15mm', 280000.00, 60, 'Ván ép gỗ công nghiệp 15mm, đa dụng', 'ván ép 15mm', 'van-ep-15mm.jpg', 1, 'active', '4x8 feet', '["Nâu", "Trắng"]', NULL, 32, 4.3, 14),
(5, 'Gỗ tấm MDF 25mm', 650000.00, 40, 'Gỗ tấm MDF độ dày 25mm, chịu lực tốt', 'gỗ tấm MDF 25mm', 'go-tam-mdf-25mm.jpg', 1, 'active', '4x8 feet', '["Trắng", "Nâu"]', 580000.00, 12, 4.6, 9);

-- Thêm dữ liệu mẫu cho detail_products
INSERT INTO detail_products (id_products, name, description) VALUES 
(1, 'Đặc tính gỗ sồi đỏ', 'Gỗ sồi đỏ có độ bền cao, vân gỗ đẹp, phù hợp làm nội thất cao cấp'),
(2, 'Đặc tính gỗ thông', 'Gỗ thông nhẹ, dễ gia công, giá thành hợp lý'),
(3, 'Đặc tính MDF', 'Ván MDF có bề mặt phẳng, dễ sơn phủ, giá rẻ'),
(4, 'Đặc tính Plywood', 'Ván Plywood bền chắc, chống ẩm tốt'),
(5, 'Đặc tính bàn gỗ', 'Bàn gỗ sồi tự nhiên, thiết kế hiện đại, bền đẹp'),
(6, 'Đặc tính ghế gỗ', 'Ghế gỗ thông tự nhiên, nhẹ, dễ di chuyển'),
(7, 'Đặc tính ván ép', 'Ván ép đa dụng, giá rẻ, dễ gia công'),
(8, 'Đặc tính gỗ tấm', 'Gỗ tấm MDF dày, chịu lực tốt, phù hợp làm tủ');

-- Thêm dữ liệu mẫu cho product_options
INSERT INTO product_options (product_id, name, is_required) VALUES 
(1, 'Kích thước', 1),
(1, 'Màu sắc', 1),
(2, 'Kích thước', 1),
(2, 'Màu sắc', 0),
(3, 'Độ dày', 1),
(3, 'Màu sắc', 0),
(4, 'Độ dày', 1),
(4, 'Màu sắc', 0),
(5, 'Kích thước', 1),
(5, 'Màu sắc', 1),
(6, 'Kích thước', 1),
(6, 'Màu sắc', 0),
(7, 'Độ dày', 1),
(7, 'Màu sắc', 0),
(8, 'Độ dày', 1),
(8, 'Màu sắc', 0);

-- Thêm dữ liệu mẫu cho product_option_values
INSERT INTO product_option_values (option_id, value, stock_quantity, price_adjustment) VALUES 
-- Gỗ sồi đỏ
(1, '4x8 feet', 25, 0.00),
(1, '4x6 feet', 15, -500000.00),
(1, '6x8 feet', 10, 1000000.00),
(2, 'Đỏ tự nhiên', 30, 0.00),
(2, 'Nâu đậm', 20, 200000.00),

-- Gỗ thông trắng
(3, '4x8 feet', 20, 0.00),
(3, '4x6 feet', 10, -300000.00),
(4, 'Trắng tự nhiên', 25, 0.00),
(4, 'Vàng', 5, 100000.00),

-- Ván MDF
(5, '18mm', 50, 0.00),
(5, '12mm', 30, -100000.00),
(5, '25mm', 20, 150000.00),
(6, 'Trắng', 60, 0.00),
(6, 'Nâu', 40, 50000.00),

-- Ván Plywood
(7, '12mm', 40, 0.00),
(7, '9mm', 25, -80000.00),
(7, '18mm', 15, 120000.00),
(8, 'Nâu', 50, 0.00),
(8, 'Đen', 30, 80000.00),

-- Bàn gỗ sồi
(9, '1.2m', 5, 0.00),
(9, '1.5m', 3, 800000.00),
(9, '1.8m', 2, 1500000.00),
(10, 'Nâu tự nhiên', 7, 0.00),
(10, 'Đen', 3, 300000.00),

-- Ghế gỗ thông
(11, '45x45x90cm', 15, 0.00),
(11, '50x50x95cm', 10, 100000.00),
(12, 'Vàng tự nhiên', 20, 0.00),
(12, 'Nâu', 5, 50000.00),

-- Ván ép
(13, '15mm', 35, 0.00),
(13, '12mm', 15, -50000.00),
(13, '18mm', 10, 80000.00),
(14, 'Nâu', 40, 0.00),
(14, 'Trắng', 20, 30000.00),

-- Gỗ tấm MDF
(15, '25mm', 25, 0.00),
(15, '18mm', 10, -100000.00),
(15, '30mm', 5, 150000.00),
(16, 'Trắng', 30, 0.00),
(16, 'Nâu', 10, 50000.00);

-- Thêm dữ liệu mẫu cho coupons
INSERT INTO coupons (code, name, description, discount_type, discount_value, min_order_amount, max_discount, usage_limit, valid_from, valid_to) VALUES 
('WELCOME10', 'Giảm giá 10% cho khách hàng mới', 'Áp dụng cho đơn hàng đầu tiên', 'percentage', 10.00, 1000000.00, 500000.00, 100, '2024-01-01 00:00:00', '2024-12-31 23:59:59'),
('FREESHIP', 'Miễn phí vận chuyển', 'Miễn phí vận chuyển cho đơn hàng từ 2 triệu', 'fixed', 50000.00, 2000000.00, 50000.00, 50, '2024-01-01 00:00:00', '2024-12-31 23:59:59'),
('SALE20', 'Giảm giá 20%', 'Giảm giá 20% cho tất cả sản phẩm', 'percentage', 20.00, 500000.00, 1000000.00, 200, '2024-01-01 00:00:00', '2024-12-31 23:59:59');

-- Thêm dữ liệu mẫu cho orders
INSERT INTO orders (user_id, guest_name, guest_email, guest_phone, total, subtotal, shipping_fee, discount_amount, status, payment_status, payment_method, delivery_address, delivery_city, delivery_district, delivery_ward, notes) VALUES 
(1, 'Nguyễn Văn A', 'nguyenvana@gmail.com', '0901234568', 3500000.00, 3500000.00, 0.00, 0.00, 'completed', 'paid', 'cash', '123 Đường ABC', 'TP.HCM', 'Quận 1', 'Phường Bến Nghé', 'Giao hàng trong giờ hành chính'),
(NULL, 'Trần Thị B', 'tranthib@gmail.com', '0901234569', 1250000.00, 1200000.00, 50000.00, 0.00, 'pending', 'pending', NULL, '456 Đường XYZ', 'TP.HCM', 'Quận 2', 'Phường Thủ Thiêm', 'Khách hàng mới');

-- Thêm dữ liệu mẫu cho order_details
INSERT INTO order_details (order_id, product_id, product_name, quantity, price, selected_options) VALUES 
(1, 5, 'Bàn gỗ sồi 1.2m', 1, 3500000.00, '{"Kích thước": "1.2m", "Màu sắc": "Nâu tự nhiên"}'),
(2, 2, 'Gỗ thông trắng', 1, 1200000.00, '{"Kích thước": "4x8 feet", "Màu sắc": "Trắng tự nhiên"}');

-- Thêm dữ liệu mẫu cho order_status_history
INSERT INTO order_status_history (order_id, status, notes) VALUES 
(1, 'pending', 'Đơn hàng được tạo'),
(1, 'confirmed', 'Đơn hàng được xác nhận'),
(1, 'preparing', 'Đang chuẩn bị hàng'),
(1, 'shipping', 'Đang giao hàng'),
(1, 'delivered', 'Đã giao hàng thành công'),
(1, 'completed', 'Hoàn thành đơn hàng'),
(2, 'pending', 'Đơn hàng được tạo'); 