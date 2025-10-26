-- Database Migration: Add wizard_completed column
-- Run this SQL to add wizard support to your Rabetin.bio installation

-- Add wizard_completed column to users table
ALTER TABLE users ADD COLUMN wizard_completed TINYINT(1) DEFAULT 0;

-- Optional: Mark existing users as having completed the wizard
-- Uncomment the line below if you want existing users to skip the wizard
-- UPDATE users SET wizard_completed = 1;

-- To run this migration, execute:
-- mysql -u rabetin -p rabetin_bio < database_migration_wizard.sql

