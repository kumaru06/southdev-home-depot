-- Product size options (per-product Choose Your Size)
-- Prefer: php tools/apply_product_sizes_migration.php

CREATE TABLE IF NOT EXISTS `product_size_options` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `size_label` VARCHAR(50) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `stock` INT NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_size_options_product` (`product_id`),
    CONSTRAINT `fk_size_options_product`
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
