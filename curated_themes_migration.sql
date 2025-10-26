-- Add new customization columns to profiles table
ALTER TABLE profiles 
  ADD COLUMN IF NOT EXISTS button_radius INT DEFAULT 14,
  ADD COLUMN IF NOT EXISTS button_border_color VARCHAR(20) DEFAULT '#667eea',
  ADD COLUMN IF NOT EXISTS button_border_width INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS button_border_style VARCHAR(20) DEFAULT 'solid',
  ADD COLUMN IF NOT EXISTS button_hover_effect TINYINT(1) DEFAULT 1;

-- Clear existing templates and add comprehensive curated themes
TRUNCATE TABLE design_templates;

-- INSERT SEASONAL & TRENDING THEMES
INSERT INTO design_templates (
  slug, name, category, description, preview_gradient, preview_accent,
  theme, font_family, bg_color, gradient_start, gradient_end, bg_image,
  button_style, button_color, button_text_color, button_shadow,
  link_layout, card_style, text_color,
  button_radius, button_border_color, button_border_width
) VALUES

-- 🎃 HALLOWEEN (Seasonal)
('halloween', 'Halloween', 'seasonal', 'Spooky season vibes', 
 'linear-gradient(135deg, #2d1b69 0%, #ff6b00 100%)', '#ff6b00',
 'dark', 'Creepster', '#0a0a0a', '#2d1b69', '#ff6b00',
 'https://images.unsplash.com/photo-1509557965875-b88c97052f0e?w=1200&q=80&auto=format',
 'rounded', '#ff6b00', '#ffffff', 1,
 'standard', 'neon', '#ffffff',
 14, '#ff6b00', 0),

-- 🎄 CHRISTMAS (Seasonal)
('christmas', 'Christmas', 'seasonal', 'Festive holiday spirit', 
 'linear-gradient(135deg, #165b33 0%, #bb2528 100%)', '#bb2528',
 'dark', 'Mountains of Christmas', '#0f1f0f', '#165b33', '#bb2528',
 'https://images.unsplash.com/photo-1512389142860-9c449e58a543?w=1200&q=80&auto=format',
 'rounded', '#bb2528', '#ffffff', 1,
 'standard', 'glass', '#ffffff',
 14, '#bb2528', 0),

-- 🎆 NEW YEAR (Seasonal)
('newyear', 'New Year', 'seasonal', 'Celebrate new beginnings', 
 'linear-gradient(135deg, #1a1a2e 0%, #ffd700 100%)', '#ffd700',
 'dark', 'Bebas Neue', '#16213e', '#1a1a2e', '#ffd700',
 'https://images.unsplash.com/photo-1467810563316-b5476525c0f9?w=1200&q=80&auto=format',
 'pill', '#ffd700', '#000000', 1,
 'standard', 'neon', '#ffffff',
 30, '#ffd700', 0),

-- 💝 VALENTINE (Seasonal)
('valentine', 'Valentine', 'seasonal', 'Love is in the air', 
 'linear-gradient(135deg, #ff758c 0%, #ff7eb3 100%)', '#ff7eb3',
 'light', 'Dancing Script', '#fff5f7', '#ff758c', '#ff7eb3',
 'https://images.unsplash.com/photo-1518199266791-5375a83190b7?w=1200&q=80&auto=format',
 'pill', '#ff7eb3', '#ffffff', 1,
 'standard', 'glass', '#1e293b',
 30, '#ff7eb3', 0),

-- 🌸 SPRING (Seasonal)
('spring', 'Spring Bloom', 'seasonal', 'Fresh and blooming', 
 'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)', '#ff6bcb',
 'light', 'Quicksand', '#fef9f3', '#a8edea', '#fed6e3',
 'https://images.unsplash.com/photo-1490750967868-88aa4486c946?w=1200&q=80&auto=format',
 'rounded', '#ff6bcb', '#ffffff', 1,
 'standard', 'glass', '#1e293b',
 14, '#ff6bcb', 0),

-- 🌊 SUMMER (Seasonal)
('summer', 'Summer Vibes', 'seasonal', 'Beach and sunshine', 
 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)', '#f5576c',
 'light', 'Pacifico', '#fffbea', '#f093fb', '#f5576c',
 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1200&q=80&auto=format',
 'pill', '#f5576c', '#ffffff', 1,
 'standard', 'solid', '#1e293b',
 30, '#f5576c', 0),

-- 🍂 AUTUMN (Seasonal)
('autumn', 'Autumn Fall', 'seasonal', 'Cozy autumn colors', 
 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)', '#fa709a',
 'light', 'Merriweather', '#fef3e2', '#fa709a', '#fee140',
 'https://images.unsplash.com/photo-1507371341162-763b5e419408?w=1200&q=80&auto=format',
 'rounded', '#fa709a', '#ffffff', 1,
 'standard', 'card', '#1e293b',
 14, '#fa709a', 0),

-- 🎮 GAMING (Category)
('gaming', 'Gaming Pro', 'gaming', 'For gaming enthusiasts', 
 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', '#667eea',
 'dark', 'Orbitron', '#0f0f23', '#667eea', '#764ba2',
 'https://images.unsplash.com/photo-1538481199705-c710c4e965fc?w=1200&q=80&auto=format',
 'sharp', '#667eea', '#ffffff', 1,
 'full', 'neon', '#ffffff',
 4, '#667eea', 2),

-- 🎵 MUSIC (Category)
('music', 'Music Artist', 'music', 'For musicians and artists', 
 'linear-gradient(135deg, #f857a6 0%, #ff5858 100%)', '#f857a6',
 'dark', 'Bebas Neue', '#1a1a1a', '#f857a6', '#ff5858',
 'https://images.unsplash.com/photo-1511379938547-c1f69419868d?w=1200&q=80&auto=format',
 'rounded', '#f857a6', '#ffffff', 1,
 'standard', 'glass', '#ffffff',
 14, '#f857a6', 0),

-- 💼 BUSINESS (Category)
('business', 'Professional', 'business', 'Clean business profile', 
 'linear-gradient(135deg, #0f2027 0%, #2c5364 100%)', '#2c5364',
 'light', 'Inter', '#f8f9fa', '#0f2027', '#2c5364',
 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1200&q=80&auto=format',
 'sharp', '#2c5364', '#ffffff', 1,
 'standard', 'solid', '#1e293b',
 4, '#2c5364', 0),

-- ✍️ BLOGGER (Category)
('blogger', 'Blogger', 'blogging', 'Perfect for content creators', 
 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)', '#4facfe',
 'light', 'Lora', '#ffffff', '#4facfe', '#00f2fe',
 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?w=1200&q=80&auto=format',
 'rounded', '#4facfe', '#ffffff', 1,
 'standard', 'card', '#1e293b',
 14, '#4facfe', 0),

-- 🎨 ARTIST (Category)
('artist', 'Creative Artist', 'art', 'Showcase your creativity', 
 'linear-gradient(135deg, #a8e063 0%, #56ab2f 100%)', '#56ab2f',
 'light', 'Caveat', '#fefefe', '#a8e063', '#56ab2f',
 'https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?w=1200&q=80&auto=format',
 'pill', '#56ab2f', '#ffffff', 1,
 'standard', 'flat', '#1e293b',
 30, '#56ab2f', 0),

-- 🏋️ FITNESS (Category)
('fitness', 'Fitness Pro', 'fitness', 'For fitness enthusiasts', 
 'linear-gradient(135deg, #ff0844 0%, #ffb199 100%)', '#ff0844',
 'dark', 'Montserrat', '#1a1a1a', '#ff0844', '#ffb199',
 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=1200&q=80&auto=format',
 'sharp', '#ff0844', '#ffffff', 1,
 'full', 'neon', '#ffffff',
 4, '#ff0844', 0),

-- 🍔 FOOD (Category)
('food', 'Food & Culinary', 'food', 'For food bloggers & chefs', 
 'linear-gradient(135deg, #fc6c8f 0%, #ffbd84 100%)', '#fc6c8f',
 'light', 'Playfair Display', '#fffbf5', '#fc6c8f', '#ffbd84',
 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1200&q=80&auto=format',
 'rounded', '#fc6c8f', '#ffffff', 1,
 'standard', 'card', '#1e293b',
 14, '#fc6c8f', 0),

-- 📸 PHOTOGRAPHY (Category)
('photography', 'Photographer', 'photography', 'Showcase your portfolio', 
 'linear-gradient(135deg, #434343 0%, #000000 100%)', '#888888',
 'dark', 'Raleway', '#1a1a1a', '#434343', '#000000',
 'https://images.unsplash.com/photo-1452587925148-ce544e77e70d?w=1200&q=80&auto=format',
 'sharp', '#888888', '#000000', 1,
 'compact', 'outlined', '#ffffff',
 4, '#ffffff', 2),

-- 💻 TECH (Category)
('tech', 'Tech Professional', 'technology', 'Modern tech aesthetic', 
 'linear-gradient(135deg, #6a11cb 0%, #2575fc 100%)', '#2575fc',
 'dark', 'JetBrains Mono', '#0d1117', '#6a11cb', '#2575fc',
 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=1200&q=80&auto=format',
 'rounded', '#2575fc', '#ffffff', 1,
 'standard', 'glass', '#ffffff',
 14, '#2575fc', 0),

-- 🌈 PRIDE (Trending)
('pride', 'Pride', 'trending', 'Celebrate diversity', 
 'linear-gradient(135deg, #ff0080 0%, #ff8c00 50%, #40e0d0 100%)', '#ff0080',
 'light', 'Poppins', '#ffffff', '#ff0080', '#40e0d0',
 'https://images.unsplash.com/photo-1517456793572-1d8efd6dc135?w=1200&q=80&auto=format',
 'pill', '#ff0080', '#ffffff', 1,
 'standard', 'glass', '#1e293b',
 30, '#ff0080', 0),

-- 🌌 CYBERPUNK (Trending)
('cyberpunk', 'Cyberpunk', 'trending', 'Futuristic neon aesthetic', 
 'linear-gradient(135deg, #ff00ff 0%, #00ffff 100%)', '#ff00ff',
 'dark', 'Orbitron', '#0a0a0a', '#ff00ff', '#00ffff',
 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=1200&q=80&auto=format',
 'sharp', '#ff00ff', '#000000', 1,
 'full', 'neon', '#ffffff',
 0, '#ff00ff', 2),

-- 🌙 MINIMAL DARK (Classic)
('minimal-dark', 'Minimal Dark', 'classic', 'Simple and elegant dark theme', 
 'linear-gradient(135deg, #2b2b2b 0%, #1a1a1a 100%)', '#666666',
 'dark', 'Inter', '#1a1a1a', '#2b2b2b', '#1a1a1a',
 'https://images.unsplash.com/photo-1557683316-973673baf926?w=1200&q=80&auto=format',
 'rounded', '#ffffff', '#000000', 1,
 'standard', 'flat', '#ffffff',
 14, '#ffffff', 1),

-- ☀️ MINIMAL LIGHT (Classic)
('minimal-light', 'Minimal Light', 'classic', 'Clean and bright', 
 'linear-gradient(135deg, #ffffff 0%, #f5f5f5 100%)', '#333333',
 'light', 'Inter', '#ffffff', '#ffffff', '#f5f5f5',
 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&q=80&auto=format',
 'rounded', '#000000', '#ffffff', 1,
 'standard', 'solid', '#1e293b',
 14, '#000000', 1);

