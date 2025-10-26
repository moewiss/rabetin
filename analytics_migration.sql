-- Add click_count column to links table if it doesn't exist
ALTER TABLE links 
ADD COLUMN IF NOT EXISTS click_count INT UNSIGNED DEFAULT 0;

-- Create link_clicks table for detailed analytics
CREATE TABLE IF NOT EXISTS link_clicks (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  link_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  referer TEXT NULL,
  INDEX idx_link_id (link_id),
  INDEX idx_user_id (user_id),
  INDEX idx_clicked_at (clicked_at),
  FOREIGN KEY (link_id) REFERENCES links(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
CREATE INDEX IF NOT EXISTS idx_profiles_user_id ON profiles(user_id);
CREATE INDEX IF NOT EXISTS idx_links_user_active ON links(user_id, is_active, position);
CREATE INDEX IF NOT EXISTS idx_socials_user_active ON social_accounts(user_id, is_active, position);
CREATE INDEX IF NOT EXISTS idx_embeds_user_active ON embeds(user_id, is_active, position);

