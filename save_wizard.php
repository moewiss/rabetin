<?php
require __DIR__.'/includes/db.php';
require __DIR__.'/includes/auth.php';
require_login();

$user_id = current_user_id();

try {
  $pdo->beginTransaction();
  
  // Update profile
  $display_name = trim($_POST['display_name'] ?? '');
  $bio = trim($_POST['bio'] ?? '');
  $theme = $_POST['theme'] ?? 'dark';
  $template_preset = $_POST['template_preset'] ?? 'creator';
  
  if (!$display_name) {
    throw new Exception('Display name is required');
  }
  
  // Get template settings if a template was selected
  $template = null;
  if ($template_preset) {
    try {
      $stmt = $pdo->prepare('SELECT * FROM design_templates WHERE slug = ?');
      $stmt->execute([$template_preset]);
      $template = $stmt->fetch();
    } catch (Exception $e) {
      // If templates table doesn't exist yet, continue without template
      $template = null;
    }
  }
  
  // Handle avatar upload
  $avatar_path = null;
  if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/uploads/profile_images/';
    if (!is_dir($upload_dir)) {
      mkdir($upload_dir, 0755, true);
    }
    
    $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (in_array($ext, $allowed)) {
      $filename = 'av_' . $user_id . '_' . time() . '.' . $ext;
      $upload_path = $upload_dir . $filename;
      
      if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
        $avatar_path = '/uploads/profile_images/' . $filename;
      }
    }
  }
  
  // Update profile with template settings if available
  if ($template) {
    // Apply template design
    if ($avatar_path) {
      $stmt = $pdo->prepare('
        UPDATE profiles SET 
          display_name=?, 
          bio=?, 
          theme=?, 
          avatar=?,
          template_preset=?,
          button_style=?,
          button_color=?,
          button_text_color=?,
          button_shadow=?,
          link_layout=?,
          card_style=?,
          bg_color=?,
          bg_image=?,
          gradient_start=?,
          gradient_end=?,
          text_color=?,
          font_family=?
        WHERE user_id=?
      ');
      $stmt->execute([
        $display_name, $bio, $template['theme'], $avatar_path,
        $template_preset,
        $template['button_style'],
        $template['button_color'],
        $template['button_text_color'],
        $template['button_shadow'],
        $template['link_layout'],
        $template['card_style'],
        $template['bg_color'],
        $template['bg_image'],
        $template['gradient_start'],
        $template['gradient_end'],
        $template['text_color'],
        $template['font_family'],
        $user_id
      ]);
    } else {
      $stmt = $pdo->prepare('
        UPDATE profiles SET 
          display_name=?, 
          bio=?, 
          theme=?,
          template_preset=?,
          button_style=?,
          button_color=?,
          button_text_color=?,
          button_shadow=?,
          link_layout=?,
          card_style=?,
          bg_color=?,
          bg_image=?,
          gradient_start=?,
          gradient_end=?,
          text_color=?,
          font_family=?
        WHERE user_id=?
      ');
      $stmt->execute([
        $display_name, $bio, $template['theme'],
        $template_preset,
        $template['button_style'],
        $template['button_color'],
        $template['button_text_color'],
        $template['button_shadow'],
        $template['link_layout'],
        $template['card_style'],
        $template['bg_color'],
        $template['bg_image'],
        $template['gradient_start'],
        $template['gradient_end'],
        $template['text_color'],
        $template['font_family'],
        $user_id
      ]);
    }
  } else {
    // No template, just update basics
    if ($avatar_path) {
      $stmt = $pdo->prepare('UPDATE profiles SET display_name=?, bio=?, theme=?, avatar=? WHERE user_id=?');
      $stmt->execute([$display_name, $bio, $theme, $avatar_path, $user_id]);
    } else {
      $stmt = $pdo->prepare('UPDATE profiles SET display_name=?, bio=?, theme=? WHERE user_id=?');
      $stmt->execute([$display_name, $bio, $theme, $user_id]);
    }
  }
  
  // Add first link if provided
  $link_title = trim($_POST['link_title'] ?? '');
  $link_url = trim($_POST['link_url'] ?? '');
  
  if ($link_title && $link_url) {
    $stmt = $pdo->prepare('INSERT INTO links (user_id, title, url, is_active, position) VALUES (?, ?, ?, 1, 0)');
    $stmt->execute([$user_id, $link_title, $link_url]);
  }
  
  // Mark wizard as completed
  $stmt = $pdo->prepare('UPDATE users SET wizard_completed=1 WHERE id=?');
  $stmt->execute([$user_id]);
  
  $pdo->commit();
  
  header('Location: /dashboard/index.php?wizard_completed=1');
  exit;
  
} catch (Exception $e) {
  $pdo->rollBack();
  header('Location: /wizard.php?error=' . urlencode($e->getMessage()));
  exit;
}

