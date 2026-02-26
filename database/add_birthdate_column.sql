-- Add birthdate column to users table
-- Run in MySQL: SOURCE database/add_birthdate_column.sql; or run the ALTER directly.

ALTER TABLE `users`
  ADD COLUMN `birthdate` DATE NULL AFTER `phone`;
