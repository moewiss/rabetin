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
  
  if (!$display_name) {
    throw new Exception('Display name is required');
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
  
  // Update profile
  if ($avatar_path) {
    $stmt = $pdo->prepare('UPDATE profiles SET display_name=?, bio=?, theme=?, avatar=? WHERE user_id=?');
    $stmt->execute([$display_name, $bio, $theme, $avatar_path, $user_id]);
  } else {
    $stmt = $pdo->prepare('UPDATE profiles SET display_name=?, bio=?, theme=? WHERE user_id=?');
    $stmt->execute([$display_name, $bio, $theme, $user_id]);
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

