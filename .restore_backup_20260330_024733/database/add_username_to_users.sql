-- Migration: add username column to users
ALTER TABLE `users`
    ADD COLUMN `username` VARCHAR(100) NULL UNIQUE AFTER `last_name`;

-- Optional: create an index for faster lookups
CREATE INDEX `idx_users_username` ON `users` (`username`(50));
