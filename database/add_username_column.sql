-- Add username column to users table
-- Run: source database/add_username_column.sql

ALTER TABLE `users`
    ADD COLUMN `username` VARCHAR(100) NULL AFTER `last_name`,
    ADD UNIQUE INDEX `idx_users_username` (`username`);
