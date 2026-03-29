-- Rollback for add_username_to_users.sql
-- Drops username column and associated index if you need to revert the migration.
ALTER TABLE `users` DROP INDEX `idx_users_username`;
ALTER TABLE `users` DROP COLUMN `username`;
