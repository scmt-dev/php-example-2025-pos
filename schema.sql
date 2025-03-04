-- Database schema for POS system

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `role` ENUM('admin', 'manager', 'cashier') DEFAULT 'cashier',
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Categories table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products table
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `sku` VARCHAR(50) UNIQUE,
  `barcode` VARCHAR(50) UNIQUE,
  `description` TEXT,
  `price` DECIMAL(10,2) NOT NULL,
  `cost_price` DECIMAL(10,2),
  `category_id` INT,
  `stock_quantity` INT NOT NULL DEFAULT 0,
  `low_stock_threshold` INT DEFAULT 10,
  `image_url` VARCHAR(255),
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Customers table
CREATE TABLE IF NOT EXISTS `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) UNIQUE,
  `phone` VARCHAR(20),
  `address` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders table
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(50) NOT NULL UNIQUE,
  `customer_id` INT,
  `user_id` INT NOT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `tax_amount` DECIMAL(10,2) DEFAULT 0,
  `discount_amount` DECIMAL(10,2) DEFAULT 0,
  `payment_method` ENUM('cash', 'credit_card', 'debit_card', 'bank_transfer', 'other') DEFAULT 'cash',
  `payment_status` ENUM('pending', 'paid', 'partially_paid', 'refunded') DEFAULT 'pending',
  `status` ENUM('new', 'processing', 'completed', 'cancelled') DEFAULT 'new',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order items table
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `discount` DECIMAL(10,2) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inventory transactions table
CREATE TABLE IF NOT EXISTS `inventory_transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `type` ENUM('purchase', 'sale', 'adjustment', 'return') NOT NULL,
  `reference_id` INT,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample data for categories
INSERT INTO `categories` (`name`, `description`) VALUES
('Electronics', 'Electronic devices and accessories'),
('Groceries', 'Food and household items'),
('Clothing', 'Apparel and fashion accessories'),
('Stationery', 'Office and school supplies');

-- Sample data for products
INSERT INTO `products` (`name`, `sku`, `barcode`, `description`, `price`, `cost_price`, `category_id`, `stock_quantity`, `low_stock_threshold`, `is_active`) VALUES
('Smartphone X', 'SM-X001', '1234567890123', 'Latest smartphone with high-end features', 999.99, 799.99, 1, 25, 5, TRUE),
('Laptop Pro', 'LP-002', '2345678901234', 'Professional laptop for work and gaming', 1499.99, 1199.99, 1, 15, 3, TRUE),
('Bread', 'GR-001', '3456789012345', 'Fresh white bread', 2.99, 1.50, 2, 50, 10, TRUE),
('Milk', 'GR-002', '4567890123456', 'Fresh cow milk, 1 liter', 1.99, 1.20, 2, 40, 10, TRUE),
('T-Shirt', 'CL-001', '5678901234567', 'Cotton t-shirt, medium size', 19.99, 10.00, 3, 100, 20, TRUE),
('Jeans', 'CL-002', '6789012345678', 'Denim jeans, various sizes', 39.99, 25.00, 3, 80, 15, TRUE),
('Notebook', 'ST-001', '7890123456789', 'Spiral notebook, 100 pages', 4.99, 2.50, 4, 200, 50, TRUE),
('Pen Set', 'ST-002', '8901234567890', 'Set of 10 colored pens', 9.99, 5.00, 4, 150, 30, TRUE),
('Wireless Earbuds', 'SM-X002', '9012345678901', 'Bluetooth wireless earbuds', 129.99, 89.99, 1, 30, 10, TRUE),
('Tablet', 'SM-X003', '0123456789012', '10-inch tablet with HD display', 349.99, 249.99, 1, 20, 5, TRUE);

-- Sample data for users
INSERT INTO `users` (`username`, `password`, `full_name`, `email`, `role`) VALUES
('admin', '$2y$10$uXEOcZnrF7yF4DGL5qXEpuIKZljK5p1B9YhT9Y3f8FRv/2dJ9K0nW', 'Admin User', 'admin@example.com', 'admin'),
('manager', '$2y$10$L3i.n2nv2OKRCZ6xN03HxeXf.tJSl9xYHQwdiCM7Kwne0NV3BkmWO', 'Manager User', 'manager@example.com', 'manager'),
('cashier', '$2y$10$oFTRKP7mcYiQG0RgbxPWVeAuE0Pq5G4iVAYwpKFsIrxZLLjzbZm8K', 'Cashier User', 'cashier@example.com', 'cashier');
-- Note: Passwords are hashed with bcrypt, all are set to 'password123' for demo purposes

-- Sample data for customers
INSERT INTO `customers` (`first_name`, `last_name`, `email`, `phone`) VALUES
('John', 'Doe', 'john.doe@example.com', '555-123-4567'),
('Jane', 'Smith', 'jane.smith@example.com', '555-234-5678'),
('Robert', 'Johnson', 'robert.johnson@example.com', '555-345-6789'),
('Emily', 'Davis', 'emily.davis@example.com', '555-456-7890');

-- Sample data for orders
INSERT INTO `orders` (`order_number`, `customer_id`, `user_id`, `total_amount`, `tax_amount`, `payment_method`, `payment_status`, `status`) VALUES
('ORD-001', 1, 3, 1049.97, 94.50, 'credit_card', 'paid', 'completed'),
('ORD-002', 2, 3, 59.98, 5.40, 'cash', 'paid', 'completed'),
('ORD-003', 3, 3, 499.95, 45.00, 'debit_card', 'paid', 'completed'),
('ORD-004', NULL, 3, 12.97, 1.17, 'cash', 'paid', 'completed'),
('ORD-005', 4, 3, 699.98, 63.00, 'credit_card', 'paid', 'completed');

-- Sample data for order items
INSERT INTO `order_items` (`order_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 1, 999.99, 999.99),
(1, 7, 10, 4.99, 49.90),
(2, 5, 3, 19.99, 59.97),
(3, 2, 1, 1499.99, 1499.99),
(4, 3, 2, 2.99, 5.98),
(4, 4, 1, 1.99, 1.99),
(4, 7, 1, 4.99, 4.99),
(5, 9, 1, 129.99, 129.99),
(5, 10, 1, 349.99, 349.99),
(5, 6, 3, 39.99, 119.97);

-- Sample data for inventory transactions
INSERT INTO `inventory_transactions` (`product_id`, `user_id`, `quantity`, `type`, `reference_id`, `notes`) VALUES
(1, 1, 30, 'purchase', NULL, 'Initial inventory'),
(2, 1, 20, 'purchase', NULL, 'Initial inventory'),
(3, 1, 100, 'purchase', NULL, 'Initial inventory'),
(4, 1, 100, 'purchase', NULL, 'Initial inventory'),
(5, 1, 200, 'purchase', NULL, 'Initial inventory'),
(6, 1, 100, 'purchase', NULL, 'Initial inventory'),
(7, 1, 300, 'purchase', NULL, 'Initial inventory'),
(8, 1, 200, 'purchase', NULL, 'Initial inventory'),
(9, 1, 50, 'purchase', NULL, 'Initial inventory'),
(10, 1, 30, 'purchase', NULL, 'Initial inventory'),
(1, 3, -1, 'sale', 1, 'Order #ORD-001'),
(7, 3, -10, 'sale', 1, 'Order #ORD-001'),
(5, 3, -3, 'sale', 2, 'Order #ORD-002'),
(2, 3, -1, 'sale', 3, 'Order #ORD-003'),
(3, 3, -2, 'sale', 4, 'Order #ORD-004'),
(4, 3, -1, 'sale', 4, 'Order #ORD-004'),
(7, 3, -1, 'sale', 4, 'Order #ORD-004'),
(9, 3, -1, 'sale', 5, 'Order #ORD-005'),
(10, 3, -1, 'sale', 5, 'Order #ORD-005'),
(6, 3, -3, 'sale', 5, 'Order #ORD-005');

-- View for dashboard: Low stock products
CREATE OR REPLACE VIEW `low_stock_products` AS
SELECT 
    id, 
    name, 
    stock_quantity, 
    low_stock_threshold 
FROM 
    products 
WHERE 
    stock_quantity <= low_stock_threshold 
    AND is_active = TRUE;

-- View for dashboard: Top selling products
CREATE OR REPLACE VIEW `top_selling_products` AS
SELECT 
    p.id,
    p.name,
    SUM(oi.quantity) as total_sold,
    SUM(oi.subtotal) as total_revenue
FROM 
    products p
JOIN 
    order_items oi ON p.id = oi.product_id
JOIN 
    orders o ON oi.order_id = o.id
WHERE 
    o.status = 'completed' 
    AND o.created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
GROUP BY 
    p.id, p.name
ORDER BY 
    total_sold DESC;

-- View for dashboard: Daily sales
CREATE OR REPLACE VIEW `daily_sales` AS
SELECT 
    DATE(created_at) as sale_date,
    COUNT(id) as total_orders,
    SUM(total_amount) as total_sales
FROM 
    orders
WHERE 
    status = 'completed'
    AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
GROUP BY 
    DATE(created_at)
ORDER BY 
    sale_date DESC;