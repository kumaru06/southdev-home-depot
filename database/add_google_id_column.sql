-- Migration: Add Google OAuth support to users table
-- Run this once against your southdev database

ALTER TABLE `users`
    ADD COLUMN `google_id` VARCHAR(128) NULL DEFAULT NULL AFTER `password`,
    ADD UNIQUE KEY `uq_users_google_id` (`google_id`);

-- Allow password to be nullable for Google-only accounts
ALTER TABLE `users` MODIFY COLUMN `password` VARCHAR(255) NULL DEFAULT NULL;
