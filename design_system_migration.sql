-- Add design customization columns to profiles table
ALTER TABLE profiles
ADD COLUMN IF NOT EXISTS button_style VARCHAR(20) DEFAULT 'rounded' AFTER font_family,
ADD COLUMN IF NOT EXISTS button_color VARCHAR(7) DEFAULT '#667eea' AFTER button_style,
ADD COLUMN IF NOT EXISTS button_text_color VARCHAR(7) DEFAULT '#ffffff' AFTER button_color,
ADD COLUMN IF NOT EXISTS button_shadow BOOLEAN DEFAULT TRUE AFTER button_text_color,
ADD COLUMN IF NOT EXISTS link_layout VARCHAR(20) DEFAULT 'standard' AFTER button_shadow,
ADD COLUMN IF NOT EXISTS card_style VARCHAR(20) DEFAULT 'glass' AFTER link_layout,
ADD COLUMN IF NOT EXISTS text_color VARCHAR(7) DEFAULT NULL AFTER card_style,
ADD COLUMN IF NOT EXISTS template_preset VARCHAR(30) DEFAULT 'default' AFTER text_color,
ADD COLUMN IF NOT EXISTS custom_css TEXT DEFAULT NULL AFTER template_preset;

-- Create design_templates table for preset templates
CREATE TABLE IF NOT EXISTS design_templates (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(50) NOT NULL UNIQUE,
  description TEXT,
  preview_image VARCHAR(255),
  theme VARCHAR(10) DEFAULT 'dark',
  button_style VARCHAR(20) DEFAULT 'rounded',
  button_color VARCHAR(7) DEFAULT '#667eea',
  button_text_color VARCHAR(7) DEFAULT '#ffffff',
  button_shadow BOOLEAN DEFAULT TRUE,
  link_layout VARCHAR(20) DEFAULT 'standard',
  card_style VARCHAR(20) DEFAULT 'glass',
  bg_color VARCHAR(7),
  gradient_start VARCHAR(7),
  gradient_end VARCHAR(7),
  text_color VARCHAR(7),
  font_family VARCHAR(50) DEFAULT 'Inter',
  is_premium BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert preset templates
INSERT INTO design_templates (name, slug, description, theme, button_style, button_color, button_text_color, button_shadow, link_layout, card_style, bg_color, gradient_start, gradient_end, text_color, font_family) VALUES
('Default', 'default', 'Clean and professional with purple gradient', 'dark', 'rounded', '#667eea', '#ffffff', TRUE, 'standard', 'glass', '#0f172a', '#667eea', '#764ba2', '#e2e8f0', 'Inter'),
('Minimal', 'minimal', 'Simple and elegant minimalist design', 'light', 'rounded', '#1e293b', '#ffffff', FALSE, 'standard', 'flat', '#ffffff', NULL, NULL, '#1e293b', 'Inter'),
('Bold', 'bold', 'High contrast with vibrant colors', 'dark', 'sharp', '#ef4444', '#ffffff', TRUE, 'full', 'solid', '#0a0a0a', NULL, NULL, '#ffffff', 'Poppins'),
('Pastel', 'pastel', 'Soft and warm pastel gradient', 'light', 'rounded', '#f472b6', '#ffffff', TRUE, 'standard', 'glass', '#fef3c7', '#fde68a', '#fbbf24', '#92400e', 'Poppins'),
('Ocean', 'ocean', 'Cool blue gradient like the ocean', 'dark', 'rounded', '#0ea5e9', '#ffffff', TRUE, 'standard', 'glass', '#0c4a6e', '#0369a1', '#06b6d4', '#e0f2fe', 'Inter'),
('Sunset', 'sunset', 'Warm orange and pink gradient', 'dark', 'rounded', '#f97316', '#ffffff', TRUE, 'standard', 'glass', '#7c2d12', '#ea580c', '#ec4899', '#fef2f2', 'Poppins'),
('Neon', 'neon', 'Vibrant neon cyberpunk style', 'dark', 'sharp', '#a855f7', '#000000', TRUE, 'full', 'neon', '#0a0a0a', '#7c3aed', '#ec4899', '#fbbf24', 'Inter'),
('Professional', 'professional', 'Corporate and business-ready', 'light', 'sharp', '#2563eb', '#ffffff', TRUE, 'standard', 'card', '#f8fafc', NULL, NULL, '#1e293b', 'Inter'),
('Nature', 'nature', 'Fresh green gradient inspired by nature', 'light', 'rounded', '#22c55e', '#ffffff', TRUE, 'standard', 'glass', '#f0fdf4', '#34d399', '#10b981', '#064e3b', 'Poppins'),
('Monochrome', 'monochrome', 'Classic black and white', 'light', 'sharp', '#000000', '#ffffff', FALSE, 'standard', 'flat', '#ffffff', NULL, NULL, '#000000', 'Inter')
ON DUPLICATE KEY UPDATE name=VALUES(name);

