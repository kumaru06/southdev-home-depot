-- Add shipping_fee to orders for zone-based Davao delivery
ALTER TABLE `orders`
  ADD COLUMN `shipping_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `total_amount`;
