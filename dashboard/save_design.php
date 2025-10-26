<?php
require __DIR__.'/../includes/db.php';
require __DIR__.'/../includes/auth.php';
require_login();

$user_id = current_user_id();
$errors = [];

// Get design settings from POST
$template_preset = $_POST['template_preset'] ?? 'default';
$button_style = $_POST['button_style'] ?? 'rounded';
$button_color = $_POST['button_color'] ?? '#667eea';
$button_text_color = $_POST['button_text_color'] ?? '#ffffff';
$button_shadow = isset($_POST['button_shadow']) ? 1 : 0;
$link_layout = $_POST['link_layout'] ?? 'standard';
$card_style = $_POST['card_style'] ?? 'glass';
$text_color = $_POST['text_color'] ?? null;

// Validation
$valid_button_styles = ['rounded', 'sharp', 'pill'];
if (!in_array($button_style, $valid_button_styles)) {
  $errors[] = 'Invalid button style';
}

$valid_layouts = ['standard', 'full', 'compact'];
if (!in_array($link_layout, $valid_layouts)) {
  $errors[] = 'Invalid link layout';
}

$valid_card_styles = ['glass', 'solid', 'flat', 'card', 'neon'];
if (!in_array($card_style, $valid_card_styles)) {
  $errors[] = 'Invalid card style';
}

// Validate colors
if ($button_color && !preg_match('/^#[0-9A-F]{6}$/i', $button_color)) {
  $errors[] = 'Invalid button color';
}
if ($button_text_color && !preg_match('/^#[0-9A-F]{6}$/i', $button_text_color)) {
  $errors[] = 'Invalid button text color';
}
if ($text_color && !preg_match('/^#[0-9A-F]{6}$/i', $text_color)) {
  $errors[] = 'Invalid text color';
}

if (!empty($errors)) {
  $_SESSION['error'] = implode('. ', $errors);
  header('Location: /dashboard/index.php?tab=design');
  exit;
}

try {
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
      // Apply template settings to profile
      $pdo->prepare('
        UPDATE profiles SET 
          template_preset = ?,
          button_style = ?,
          button_color = ?,
          button_text_color = ?,
          button_shadow = ?,
          link_layout = ?,
          card_style = ?,
          theme = ?,
          bg_color = ?,
          gradient_start = ?,
          gradient_end = ?,
          text_color = ?,
          font_family = ?
        WHERE user_id = ?
      ')->execute([
        $template_preset,
        $template['button_style'],
        $template['button_color'],
        $template['button_text_color'],
        $template['button_shadow'],
        $template['link_layout'],
        $template['card_style'],
        $template['theme'],
        $template['bg_color'],
        $template['gradient_start'],
        $template['gradient_end'],
        $template['text_color'],
        $template['font_family'],
        $user_id
      ]);
    }
  } else {
    // Update custom design settings
    $pdo->prepare('
      UPDATE profiles SET 
        template_preset = ?,
        button_style = ?,
        button_color = ?,
        button_text_color = ?,
        button_shadow = ?,
        link_layout = ?,
        card_style = ?,
        text_color = ?
      WHERE user_id = ?
    ')->execute([
      'custom',
      $button_style,
      $button_color,
      $button_text_color,
      $button_shadow,
      $link_layout,
      $card_style,
      $text_color,
      $user_id
    ]);
  }
  
  $_SESSION['success'] = 'Design updated successfully!';
} catch (Exception $e) {
  $_SESSION['error'] = 'Failed to update design. Please try again.';
  error_log('Save design error: ' . $e->getMessage());
}

header('Location: /dashboard/index.php?tab=design');
exit;

