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
  category VARCHAR(50) DEFAULT 'general',
  preview_gradient VARCHAR(255),
  preview_accent VARCHAR(7),
  theme VARCHAR(10) DEFAULT 'dark',
  button_style VARCHAR(20) DEFAULT 'rounded',
  button_color VARCHAR(7) DEFAULT '#667eea',
  button_text_color VARCHAR(7) DEFAULT '#ffffff',
  button_shadow BOOLEAN DEFAULT TRUE,
  link_layout VARCHAR(20) DEFAULT 'standard',
  card_style VARCHAR(20) DEFAULT 'glass',
  bg_color VARCHAR(7),
  bg_image VARCHAR(500),
  gradient_start VARCHAR(7),
  gradient_end VARCHAR(7),
  text_color VARCHAR(7),
  font_family VARCHAR(50) DEFAULT 'Inter',
  is_premium BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_slug (slug),
  INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert industry-specific preset templates with background images
INSERT INTO design_templates (name, slug, description, category, preview_gradient, preview_accent, theme, button_style, button_color, button_text_color, button_shadow, link_layout, card_style, bg_color, bg_image, gradient_start, gradient_end, text_color, font_family) VALUES
('Creator', 'creator', 'Perfect for bloggers and content creators', 'blogger', 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', '#667eea', 'dark', 'rounded', '#667eea', '#ffffff', TRUE, 'standard', 'glass', '#0f172a', 'https://images.unsplash.com/photo-1557683316-973673baf926?w=1920&q=80', '#667eea', '#764ba2', '#e2e8f0', 'Inter'),
('TechPro', 'techpro', 'Modern design for developers and tech professionals', 'tech', 'linear-gradient(135deg, #00f2fe 0%, #4facfe 100%)', '#00d4ff', 'dark', 'sharp', '#00d4ff', '#000000', TRUE, 'full', 'neon', '#0a0f1c', 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1920&q=80', '#00f2fe', '#4facfe', '#e0f7ff', 'Inter'),
('Engineer', 'engineer', 'Industrial and professional for engineers', 'engineering', 'linear-gradient(135deg, #f5af19 0%, #f12711 100%)', '#ff6b35', 'dark', 'sharp', '#ff6b35', '#ffffff', TRUE, 'standard', 'solid', '#1a1a1a', 'https://images.unsplash.com/photo-1581094794329-c8112a89af12?w=1920&q=80', '#f5af19', '#f12711', '#ffffff', 'Inter'),
('Artist', 'artist', 'Vibrant and creative for artists and designers', 'creative', 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)', '#ff6ba9', 'light', 'pill', '#ff6ba9', '#ffffff', TRUE, 'standard', 'glass', '#fff5f7', 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=1920&q=80', '#fa709a', '#fee140', '#4a0e4e', 'Poppins'),
('Business', 'business', 'Professional corporate design', 'business', 'linear-gradient(135deg, #2b5876 0%, #4e4376 100%)', '#2563eb', 'light', 'sharp', '#2563eb', '#ffffff', TRUE, 'standard', 'card', '#f8fafc', 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1920&q=80', '#2b5876', '#4e4376', '#1e293b', 'Inter'),
('Fitness', 'fitness', 'Energetic design for fitness and health', 'fitness', 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)', '#e84545', 'dark', 'rounded', '#e84545', '#ffffff', TRUE, 'full', 'glass', '#1a0a0f', 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=1920&q=80', '#f093fb', '#f5576c', '#ffe9e9', 'Poppins'),
('Musician', 'musician', 'Bold design for musicians and entertainers', 'music', 'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)', '#8b5cf6', 'light', 'pill', '#8b5cf6', '#ffffff', TRUE, 'standard', 'glass', '#fef3f7', 'https://images.unsplash.com/photo-1511379938547-c1f69419868d?w=1920&q=80', '#a8edea', '#fed6e3', '#2d1b69', 'Poppins'),
('Fashion', 'fashion', 'Elegant design for fashion and beauty', 'fashion', 'linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%)', '#ec4899', 'light', 'rounded', '#ec4899', '#ffffff', TRUE, 'compact', 'flat', '#fff7ed', 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?w=1920&q=80', '#ffecd2', '#fcb69f', '#831843', 'Poppins'),
('Foodie', 'foodie', 'Warm design for food and restaurants', 'food', 'linear-gradient(135deg, #ff9a56 0%, #ff6a00 100%)', '#f97316', 'dark', 'rounded', '#f97316', '#ffffff', TRUE, 'standard', 'glass', '#1c0a00', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1920&q=80', '#ff9a56', '#ff6a00', '#ffe8d6', 'Poppins'),
('Educator', 'educator', 'Clean design for teachers and education', 'education', 'linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%)', '#22c55e', 'light', 'rounded', '#22c55e', '#ffffff', TRUE, 'standard', 'card', '#f0fdf4', 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=1920&q=80', '#84fab0', '#8fd3f4', '#064e3b', 'Inter')
ON DUPLICATE KEY UPDATE name=VALUES(name), bg_image=VALUES(bg_image);

