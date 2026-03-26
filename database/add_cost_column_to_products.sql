-- Add nullable cost column to products for inventory valuation by cost
ALTER TABLE `products`
  ADD COLUMN `cost` DECIMAL(10,2) NULL DEFAULT NULL AFTER `price`;

-- Example: to set cost values manually (optional)
-- UPDATE products SET cost = 0.00 WHERE cost IS NULL;

-- Note: import this file via phpMyAdmin or run using mysql CLI
-- mysql -u root -p southdev < add_cost_column_to_products.sql
