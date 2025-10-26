<?php
require __DIR__.'/../includes/db.php';
require __DIR__.'/../includes/auth.php';
require_login();

$user_id = current_user_id();
$errors = [];

// Get ALL design settings from POST
$template_preset = $_POST['template_preset'] ?? 'default';

// Profile Elements
$theme = $_POST['theme'] ?? 'dark';
$font_family = $_POST['font_family'] ?? 'Inter';

// Background
$bg_color = $_POST['bg_color'] ?? '#0f172a';
$gradient_start = $_POST['gradient_start'] ?? null;
$gradient_end = $_POST['gradient_end'] ?? null;

// Button & Link Styles
$button_style = $_POST['button_style'] ?? 'rounded';
$button_color = $_POST['button_color'] ?? '#667eea';
$button_text_color = $_POST['button_text_color'] ?? '#ffffff';
$button_shadow = isset($_POST['button_shadow']) ? 1 : 0;
$button_hover_effect = isset($_POST['button_hover_effect']) ? 1 : 0;
$link_layout = $_POST['link_layout'] ?? 'standard';
$card_style = $_POST['card_style'] ?? 'glass';
$text_color = $_POST['text_color'] ?? null;

// Advanced button customization
$button_radius = isset($_POST['button_radius']) ? intval($_POST['button_radius']) : 14;
$button_border_color = $_POST['button_border_color'] ?? '#667eea';
$button_border_width = isset($_POST['button_border_width']) ? intval($_POST['button_border_width']) : 0;
$button_border_style = $_POST['button_border_style'] ?? 'solid';

// Validation
$valid_button_styles = ['rounded', 'sharp', 'pill', 'custom'];
if (!in_array($button_style, $valid_button_styles)) {
  $errors[] = 'Invalid button style';
}

$valid_layouts = ['standard', 'full', 'compact'];
if (!in_array($link_layout, $valid_layouts)) {
  $errors[] = 'Invalid link layout';
}

$valid_card_styles = ['glass', 'solid', 'flat', 'card', 'neon', 'outlined'];
if (!in_array($card_style, $valid_card_styles)) {
  $errors[] = 'Invalid card style';
}

$valid_border_styles = ['solid', 'dashed', 'dotted', 'double'];
if (!in_array($button_border_style, $valid_border_styles)) {
  $errors[] = 'Invalid button border style';
}

if ($button_radius < 0 || $button_radius > 100) {
  $errors[] = 'Button radius must be between 0 and 100';
}

if ($button_border_width < 0 || $button_border_width > 10) {
  $errors[] = 'Button border width must be between 0 and 10';
}

// Validate colors
if ($button_color && !preg_match('/^#[0-9A-F]{6}$/i', $button_color)) {
  $errors[] = 'Invalid button color';
}
if ($button_text_color && !preg_match('/^#[0-9A-F]{6}$/i', $button_text_color)) {
  $errors[] = 'Invalid button text color';
}
if ($button_border_color && !preg_match('/^#[0-9A-F]{6}$/i', $button_border_color)) {
  $errors[] = 'Invalid button border color';
}
if ($text_color && !preg_match('/^#[0-9A-F]{6}$/i', $text_color)) {
  $errors[] = 'Invalid text color';
}
if ($bg_color && !preg_match('/^#[0-9A-F]{6}$/i', $bg_color)) {
  $errors[] = 'Invalid background color';
}
if ($gradient_start && !preg_match('/^#[0-9A-F]{6}$/i', $gradient_start)) {
  $errors[] = 'Invalid gradient start color';
}
if ($gradient_end && !preg_match('/^#[0-9A-F]{6}$/i', $gradient_end)) {
  $errors[] = 'Invalid gradient end color';
}

if (!empty($errors)) {
  $_SESSION['error'] = implode('. ', $errors);
  header('Location: /dashboard/index.php?tab=design');
  exit;
}

try {
  // Handle avatar upload
  $avatar_path = null;
  if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/../uploads/profile_images/';
    if (!is_dir($upload_dir)) {
      mkdir($upload_dir, 0755, true);
    }
    
    $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (in_array($ext, $allowed) && getimagesize($_FILES['avatar']['tmp_name'])) {
      $filename = 'av_' . $user_id . '_' . time() . '.' . $ext;
      $upload_path = $upload_dir . $filename;
      
      if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
        // Resize/optimize image
        $avatar_path = '/uploads/profile_images/' . $filename;
      }
    }
  }
  
  // Handle background image upload
  $bg_image_path = null;
  if (isset($_FILES['bg_image_upload']) && $_FILES['bg_image_upload']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/../uploads/profile_images/';
    if (!is_dir($upload_dir)) {
      mkdir($upload_dir, 0755, true);
    }
    
    $ext = strtolower(pathinfo($_FILES['bg_image_upload']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (in_array($ext, $allowed) && getimagesize($_FILES['bg_image_upload']['tmp_name'])) {
      $filename = 'bg_' . $user_id . '_' . time() . '.' . $ext;
      $upload_path = $upload_dir . $filename;
      
      if (move_uploaded_file($_FILES['bg_image_upload']['tmp_name'], $upload_path)) {
        $bg_image_path = '/uploads/profile_images/' . $filename;
      }
    }
  }
  
  // Apply template preset if selected
  if ($template_preset !== 'custom' && $template_preset !== 'default') {
    try {
      $stmt = $pdo->prepare('SELECT * FROM design_templates WHERE slug = ?');
      $stmt->execute([$template_preset]);
      $template = $stmt->fetch();
    } catch (Exception $e) {
      // If templates table doesn't exist, fall back to custom settings
      $template = null;
    }
    
    if ($template) {
      // Apply template settings to profile (template overrides form inputs)
      $sql = 'UPDATE profiles SET 
          template_preset = ?,
          button_style = ?,
          button_color = ?,
          button_text_color = ?,
          button_shadow = ?,
          button_hover_effect = ?,
          link_layout = ?,
          card_style = ?,
          theme = ?,
          bg_color = ?,
          bg_image = ?,
          gradient_start = ?,
          gradient_end = ?,
          text_color = ?,
          font_family = ?,
          button_radius = ?,
          button_border_color = ?,
          button_border_width = ?,
          button_border_style = ?';
      
      $params = [
        $template_preset,
        $template['button_style'],
        $template['button_color'],
        $template['button_text_color'],
        $template['button_shadow'],
        1, // button_hover_effect (templates have hover effect by default)
        $template['link_layout'],
        $template['card_style'],
        $template['theme'],
        $template['bg_color'],
        $template['bg_image'],
        $template['gradient_start'],
        $template['gradient_end'],
        $template['text_color'],
        $template['font_family'],
        $template['button_radius'] ?? 14,
        $template['button_border_color'] ?? $template['button_color'],
        $template['button_border_width'] ?? 0,
        'solid' // default border style for templates
      ];
      
      // Add avatar if uploaded
      if ($avatar_path) {
        $sql .= ', avatar = ?';
        $params[] = $avatar_path;
      }
      
      $sql .= ' WHERE user_id = ?';
      $params[] = $user_id;
      
      $pdo->prepare($sql)->execute($params);
    }
  } else {
    // Update ALL custom design settings
    $sql = 'UPDATE profiles SET 
        template_preset = ?,
        button_style = ?,
        button_color = ?,
        button_text_color = ?,
        button_shadow = ?,
        button_hover_effect = ?,
        link_layout = ?,
        card_style = ?,
        text_color = ?,
        theme = ?,
        font_family = ?,
        bg_color = ?,
        gradient_start = ?,
        gradient_end = ?,
        button_radius = ?,
        button_border_color = ?,
        button_border_width = ?,
        button_border_style = ?';
    
    $params = [
      'custom',
      $button_style,
      $button_color,
      $button_text_color,
      $button_shadow,
      $button_hover_effect,
      $link_layout,
      $card_style,
      $text_color,
      $theme,
      $font_family,
      $bg_color,
      $gradient_start,
      $gradient_end,
      $button_radius,
      $button_border_color,
      $button_border_width,
      $button_border_style
    ];
    
    // Add avatar if uploaded
    if ($avatar_path) {
      $sql .= ', avatar = ?';
      $params[] = $avatar_path;
    }
    
    // Add background image if uploaded
    if ($bg_image_path) {
      $sql .= ', bg_image = ?';
      $params[] = $bg_image_path;
    }
    
    $sql .= ' WHERE user_id = ?';
    $params[] = $user_id;
    
    $pdo->prepare($sql)->execute($params);
  }
  
  $_SESSION['success'] = 'Design updated successfully!';
} catch (Exception $e) {
  $_SESSION['error'] = 'Failed to update design. Please try again.';
  error_log('Save design error: ' . $e->getMessage());
}

header('Location: /dashboard/index.php?tab=design');
exit;

