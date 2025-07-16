-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for go
CREATE DATABASE IF NOT EXISTS `go` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `go`;

-- Dumping structure for table go.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table go.roles: ~2 rows (approximately)
INSERT INTO `roles` (`id`, `name`) VALUES
	(1, 'admin'),
	(2, 'user');

-- Dumping structure for table go.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_id` int NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email_or_phone` (`email`,`phone`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table go.users: ~0 rows (approximately)
INSERT INTO `users` (`id`, `role_id`, `password`, `email`, `phone`, `name`, `address`, `status`, `created_at`) VALUES
	(1, 1, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@thanhlygo.com', '0901234567', 'Administrator', 'TP.HCM', 'active', '2025-07-15 09:54:37');

-- Dumping structure for table go.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table go.categories: ~5 rows (approximately)
INSERT INTO `categories` (`id`, `name`, `status`) VALUES
	(1, 'Gỗ tự nhiên', 'active'),
	(2, 'Gỗ công nghiệp', 'active'),
	(3, 'Nội thất gỗ', 'active'),
	(4, 'Ván gỗ', 'active'),
	(5, 'Gỗ tấm', 'active');

-- Dumping structure for table go.coupons
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT '0.00',
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int DEFAULT NULL,
  `used_count` int DEFAULT '0',
  `valid_from` datetime NOT NULL,
  `valid_to` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping structure for table go.detail_categories
CREATE TABLE IF NOT EXISTS `detail_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_categories` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `id_categories` (`id_categories`),
  CONSTRAINT `detail_categories_ibfk_1` FOREIGN KEY (`id_categories`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table go.detail_categories: ~8 rows (approximately)
INSERT INTO `detail_categories` (`id`, `id_categories`, `name`, `description`) VALUES
	(1, 1, 'Gỗ sồi', 'Gỗ sồi tự nhiên chất lượng cao'),
	(2, 1, 'Gỗ thông', 'Gỗ thông tự nhiên giá rẻ'),
	(3, 2, 'MDF', 'Ván MDF công nghiệp'),
	(4, 2, 'Plywood', 'Ván Plywood đa lớp'),
	(5, 3, 'Bàn gỗ', 'Bàn gỗ nội thất'),
	(6, 3, 'Ghế gỗ', 'Ghế gỗ nội thất'),
	(7, 4, 'Ván ép', 'Ván ép gỗ công nghiệp'),
	(8, 5, 'Gỗ tấm 4x8', 'Gỗ tấm kích thước 4x8 feet');

-- Dumping structure for table go.products
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `description` text,
  `image_des` varchar(255) DEFAULT NULL,
  `description_images` text,
  `image_` varchar(255) DEFAULT NULL,
  `main_images` text,
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `status` enum('active','inactive','out_of_stock') DEFAULT 'active',
  `size` varchar(255) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `sale` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table go.products: ~1 rows (approximately)
INSERT INTO `products` (`id`, `category_id`, `name`, `price`, `stock`, `description`, `image_des`, `description_images`, `image_`, `main_images`, `is_available`, `status`, `size`, `color`, `sale`) VALUES
	(9, 3, 'Ghế Sofa AURORA - MOHO Signature', 40287000.00, 30, '<p><img src="/go/assets/uploads/editor/editor_1752555621_6875e0656f7d5.jpg" width="2048" height="2048"><img src="/go/assets/uploads/editor/editor_1752555621_6875e06573230.jpg" width="2048" height="1457"><img src="/go/assets/uploads/editor/editor_1752555621_6875e0658320c.jpg" width="2048" height="1457"><img src="/go/assets/uploads/editor/editor_1752555621_6875e065865ca.jpg" width="2048" height="1275"><img src="/go/assets/uploads/editor/editor_1752555621_6875e0658d357.jpg" width="2048" height="1275"><img src="/go/assets/uploads/editor/editor_1752555621_6875e065b3466.jpg" width="2048" height="1275"><img src="/go/assets/uploads/editor/editor_1752555621_6875e065b8a56.jpg" width="2048" height="765"><img src="/go/assets/uploads/editor/editor_1752555621_6875e065c189c.jpg" width="2048" height="1457"><img src="/go/assets/uploads/editor/editor_1752555621_6875e065c6893.jpg" width="2048" height="1457"></p>', NULL, NULL, NULL, NULL, 1, 'active', 'D250x S109x C77 cm', '["N\\u00e2u \\u0111\\u1eadm","Tr\\u1eafng","Kem"]', 5000000.00),
	(20, 2, 'Khai vịi', 4000000.00, 0, '<p>z</p>', NULL, '["assets\\/uploads\\/product_1752659569_des_0.jpg"]', NULL, '["assets\\/uploads\\/product_1752659569_main_0.jpg"]', 1, 'active', 'D250x S109722cm', '', 100000.00);

-- Dumping structure for table go.detail_products
CREATE TABLE IF NOT EXISTS `detail_products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_products` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_product_detail` (`id_products`,`name`),
  CONSTRAINT `detail_products_ibfk_1` FOREIGN KEY (`id_products`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table go.detail_products: ~4 rows (approximately)
INSERT INTO `detail_products` (`id`, `id_products`, `name`, `description`) VALUES
	(69, 9, 'Kích thước', 'Contemporary Italian – thanh lịch, hiện đại'),
	(70, 9, 'Chất liệu', 'Vải cao cấp phối viền da nhân tạo - Khung gỗ tự nhiên - Chân inox'),
	(71, 9, 'Gối đi kèm', '8 gối rời, bao gồm gối tựa lưng và gối trang trí'),
	(72, 9, 'Màu sắc', 'Trắng xám phối đen – hiện đại, tinh tế và dễ kết hợp'),
	(83, 20, 'a', 'aaa');

-- Dumping structure for table go.orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `guest_name` varchar(100) DEFAULT NULL,
  `guest_email` varchar(100) DEFAULT NULL,
  `guest_phone` varchar(15) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `shipping_fee` decimal(10,2) DEFAULT '0.00',
  `discount_amount` decimal(10,2) DEFAULT '0.00',
  `status` enum('pending','confirmed','preparing','shipping','delivered','cancelled','completed') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `payment_method` enum('cash','card','bank_transfer','momo','zalopay') DEFAULT NULL,
  `delivery_address` varchar(255) DEFAULT NULL,
  `delivery_city` varchar(100) DEFAULT NULL,
  `delivery_district` varchar(100) DEFAULT NULL,
  `delivery_ward` varchar(100) DEFAULT NULL,
  `delivery_notes` text,
  `coupon_code` varchar(50) DEFAULT NULL,
  `coupon_discount` decimal(10,2) DEFAULT '0.00',
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table go.orders: ~0 rows (approximately)

-- Dumping structure for table go.order_details
CREATE TABLE IF NOT EXISTS `order_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `selected_options` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table go.order_details: ~0 rows (approximately)

-- Dumping structure for table go.product_images
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_type` enum('main','description') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'main',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `image_type` (`image_type`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=145 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table go.product_images: ~34 rows (approximately)
INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `image_type`, `sort_order`, `created_at`) VALUES
	(1, 9, 'assets/uploads/product_1752552955_main_0.jpg', 'main', 0, '2025-07-15 04:15:55'),
	(2, 9, 'assets/uploads/product_1752552955_main_1.jpg', 'main', 1, '2025-07-15 04:15:55'),
	(3, 9, 'assets/uploads/product_1752552955_main_2.jpg', 'main', 2, '2025-07-15 04:15:55'),
	(4, 9, 'assets/uploads/product_1752552955_main_3.jpg', 'main', 3, '2025-07-15 04:15:55'),
	(5, 9, 'assets/uploads/product_1752552955_main_4.jpg', 'main', 4, '2025-07-15 04:15:55'),
	(6, 9, 'assets/uploads/product_1752552955_main_5.jpg', 'main', 5, '2025-07-15 04:15:55'),
	(7, 9, 'assets/uploads/product_1752552955_main_6.jpg', 'main', 6, '2025-07-15 04:15:55'),
	(8, 9, 'assets/uploads/product_1752552955_main_7.jpg', 'main', 7, '2025-07-15 04:15:55'),
	(9, 9, 'assets/uploads/product_1752552955_des_0.jpg', 'description', 0, '2025-07-15 04:15:55'),
	(10, 9, 'assets/uploads/product_1752552955_des_1.jpg', 'description', 1, '2025-07-15 04:15:55'),
	(11, 9, 'assets/uploads/product_1752552955_des_2.jpg', 'description', 2, '2025-07-15 04:15:55'),
	(12, 9, 'assets/uploads/product_1752552955_des_3.jpg', 'description', 3, '2025-07-15 04:15:55'),
	(13, 9, 'assets/uploads/product_1752552955_des_4.jpg', 'description', 4, '2025-07-15 04:15:55'),
	(14, 9, 'assets/uploads/product_1752552955_des_5.jpg', 'description', 5, '2025-07-15 04:15:55'),
	(15, 9, 'assets/uploads/product_1752552955_des_6.jpg', 'description', 6, '2025-07-15 04:15:55'),
	(16, 9, 'assets/uploads/product_1752552955_des_7.jpg', 'description', 7, '2025-07-15 04:15:55'),
	(17, 9, 'assets/uploads/product_1752552955_des_8.jpg', 'description', 8, '2025-07-15 04:15:55'),
	(143, 20, 'assets/uploads/product_1752659569_main_0.jpg', 'main', 0, '2025-07-16 09:52:49'),
	(144, 20, 'assets/uploads/product_1752659569_des_0.jpg', 'description', 0, '2025-07-16 09:52:49');

-- Dumping structure for table go.product_options
CREATE TABLE IF NOT EXISTS `product_options` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `is_required` tinyint(1) DEFAULT '0',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product_options_product_id` (`product_id`),
  CONSTRAINT `product_options_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table go.product_options: ~1 rows (approximately)
INSERT INTO `product_options` (`id`, `product_id`, `name`, `description`, `is_required`, `sort_order`, `created_at`) VALUES
	(6, 9, 'Loại', NULL, 0, 0, '2025-07-15 09:46:53'),
	(7, 20, 'Loại', NULL, 0, 0, '2025-07-16 09:52:49');

-- Dumping structure for table go.product_option_values
CREATE TABLE IF NOT EXISTS `product_option_values` (
  `id` int NOT NULL AUTO_INCREMENT,
  `option_id` int NOT NULL,
  `value` varchar(255) NOT NULL,
  `price_adjustment` decimal(10,2) DEFAULT '0.00',
  `stock_quantity` int DEFAULT '0',
  `is_default` tinyint(1) DEFAULT '0',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `option_id` (`option_id`),
  CONSTRAINT `product_option_values_ibfk_1` FOREIGN KEY (`option_id`) REFERENCES `product_options` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table go.product_option_values: ~2 rows (approximately)
INSERT INTO `product_option_values` (`id`, `option_id`, `value`, `price_adjustment`, `stock_quantity`, `is_default`, `sort_order`, `created_at`) VALUES
	(3, 6, 'Milan', 0.00, 1, 0, 0, '2025-07-15 09:46:53'),
	(4, 6, 'OSLO', 0.00, 3, 0, 0, '2025-07-15 09:46:53'),
	(5, 7, 'Milan', 0.00, 12, 0, 0, '2025-07-16 09:52:49'),
	(6, 7, 'ABC', 0.00, 1, 0, 0, '2025-07-16 09:52:49');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
