USE `southdev`;

-- Demo Inventory In-Charge Account for testing
-- Password: Demo@1234
INSERT INTO `users` (`role_id`, `first_name`, `last_name`, `email`, `password`, `phone`, `is_active`, `email_verified_at`) VALUES
(4, 'Inventory', 'Demo', 'inventory@demo.local', '$2y$10$u5u9RGJq.87xvwl7vhzskeuV6vDhZhn1aLxsxbAQdy17I6zB.ut7W', '09170000000', 1, NOW());
