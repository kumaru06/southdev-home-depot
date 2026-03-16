-- ============================================================
-- SouthDev Home Depot – Migration V2
-- Pre-Oral Defense Improvements
-- Run this AFTER the base southdev.sql schema is in place.
-- ============================================================

USE `southdev`;

-- -------------------------------------------------------
-- 1. Add Inventory In-charge role (id = 4)
-- -------------------------------------------------------
INSERT IGNORE INTO `roles` (`id`, `name`) VALUES (4, 'inventory_incharge');

-- -------------------------------------------------------
-- 2. Price History table – tracks every price change
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `price_history` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `old_price` DECIMAL(10,2) NOT NULL,
    `new_price` DECIMAL(10,2) NOT NULL,
    `changed_by` INT NULL,
    `reason` VARCHAR(500) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`changed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_ph_product` (`product_id`),
    INDEX `idx_ph_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 3. Stock Movements table – tracks all stock changes
--    type: 'purchase','sale','return','adjustment','initial'
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `stock_movements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `type` ENUM('purchase','sale','return','adjustment','initial') NOT NULL,
    `quantity` INT NOT NULL COMMENT 'positive = added, negative = removed',
    `reference_id` INT NULL COMMENT 'order_id or return_request_id if applicable',
    `notes` VARCHAR(500) NULL,
    `performed_by` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`performed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_sm_product` (`product_id`),
    INDEX `idx_sm_type` (`type`),
    INDEX `idx_sm_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------
-- 4. Supplier Requests table – low stock action tracking
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS `supplier_requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `requested_quantity` INT NOT NULL DEFAULT 0,
    `status` ENUM('pending','ordered','received','cancelled') DEFAULT 'pending',
    `notes` VARCHAR(500) NULL,
    `requested_by` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`requested_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_sr_product` (`product_id`),
    INDEX `idx_sr_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
