-- Migration: Add client_key column to payments table for PayMongo card (Payment Intent) flow
ALTER TABLE `payments`
    ADD COLUMN `client_key` VARCHAR(512) NULL AFTER `source_id`;
