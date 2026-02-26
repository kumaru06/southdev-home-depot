-- SouthDev Home Depot Seed Data
USE `southdev`;

-- Insert roles
INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'customer'),
(2, 'staff'),
(3, 'super_admin');

-- Insert default super admin
INSERT INTO `users` (`role_id`, `first_name`, `last_name`, `email`, `password`, `phone`) VALUES
(3, 'Super', 'Admin', 'admin@southdev.com', '$2y$10$defaulthashedpassword', '09123456789');

-- Insert sample categories
INSERT INTO `categories` (`name`, `description`) VALUES
('Hardware', 'Nuts, bolts, screws, fasteners, hinges, and general hardware supplies'),
('Construction Materials', 'Cement, lumber, roofing, drywall, and building essentials'),
('Tools', 'Power tools, hand tools, and professional-grade equipment'),
('Plumbing', 'Pipes, fittings, valves, faucets, and plumbing accessories'),
('Electrical Supplies', 'Wiring, outlets, switches, breakers, and electrical components');

-- Insert sample products
INSERT INTO `products` (`category_id`, `name`, `description`, `price`, `sku`) VALUES
(1, 'Stainless Steel Bolt Set (100pc)', 'Heavy-duty stainless steel bolts, assorted sizes M6-M12', 450.00, 'HW-BLT-001'),
(1, 'Cabinet Door Hinges (Pair)', 'Soft-close cabinet hinges, brushed nickel finish', 185.00, 'HW-HNG-002'),
(1, 'Padlock 50mm Heavy Duty', 'Weather-resistant laminated steel padlock', 320.00, 'HW-PLK-003'),
(2, 'Portland Cement 40kg', 'Type I general-purpose Portland cement', 280.00, 'CM-CEM-001'),
(2, 'Plywood 4x8 Marine Grade', '3/4 inch marine-grade plywood sheet', 1250.00, 'CM-PLY-002'),
(2, 'GI Corrugated Roof Sheet', 'Gauge 26, 8ft length galvanized iron roofing', 385.00, 'CM-ROF-003'),
(3, 'Cordless Drill 20V', 'Lithium-ion cordless drill with 2 batteries and charger', 3500.00, 'TL-DRL-001'),
(3, 'Measuring Tape 7.5m', 'Professional-grade steel measuring tape with auto-lock', 195.00, 'TL-MSR-002'),
(3, 'Circular Saw 7-1/4"', '1400W circular saw with carbide-tipped blade', 4200.00, 'TL-SAW-003'),
(4, 'PVC Pipe 1/2" (10ft)', 'Schedule 40 PVC pressure pipe', 65.00, 'PL-PVC-001'),
(4, 'Kitchen Faucet Single Handle', 'Chrome-plated brass kitchen faucet with sprayer', 1850.00, 'PL-FCT-002'),
(4, 'Teflon Tape 1/2"x10m', 'PTFE thread seal tape for pipe connections', 25.00, 'PL-TFL-003'),
(5, 'THHN Wire #12 (75m)', 'Stranded copper THHN wire, 75 meters', 2800.00, 'EL-WIR-001'),
(5, 'LED Panel Light 18W', 'Surface-mount 18W LED panel, daylight 6500K', 450.00, 'EL-LED-002'),
(5, 'Circuit Breaker 30A', 'Bolt-on type single-pole circuit breaker', 380.00, 'EL-BRK-003');

-- Initialize inventory for sample products
INSERT INTO `inventory` (`product_id`, `quantity`, `reorder_level`) VALUES
(1, 250, 50), (2, 120, 20), (3, 85, 15),
(4, 500, 100), (5, 45, 10), (6, 200, 30),
(7, 35, 10), (8, 150, 25), (9, 20, 5),
(10, 800, 100), (11, 15, 5), (12, 500, 50),
(13, 40, 10), (14, 65, 15), (15, 90, 20);
