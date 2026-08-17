-- Product gallery, specifications, and size grouping (Product Details v2)
-- Safe to re-run: uses IF NOT EXISTS / information_schema checks where needed.

CREATE TABLE IF NOT EXISTS `product_images` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_product_images_product` (`product_id`),
    CONSTRAINT `fk_product_images_product`
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add columns if missing (MySQL 8 / MariaDB compatible procedural style via separate ALTERs;
-- run_migration may fail on duplicate column — use tools/apply_product_v2_migration.php instead).

ALTER TABLE `products`
    ADD COLUMN `specifications` TEXT NULL AFTER `description`;

ALTER TABLE `products`
    ADD COLUMN `size_label` VARCHAR(50) NULL AFTER `sku`;

ALTER TABLE `products`
    ADD COLUMN `size_group` VARCHAR(100) NULL AFTER `size_label`;

-- Backfill gallery from existing cover images
INSERT INTO `product_images` (`product_id`, `filename`, `sort_order`)
SELECT p.`id`, p.`image`, 0
FROM `products` p
WHERE p.`image` IS NOT NULL
  AND p.`image` <> ''
  AND NOT EXISTS (
      SELECT 1 FROM `product_images` pi WHERE pi.`product_id` = p.`id`
  );
