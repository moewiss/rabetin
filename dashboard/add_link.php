<?php
require __DIR__.'/../includes/db.php';
require __DIR__.'/../includes/auth.php';
require_login();

$user_id = current_user_id();
$title = trim($_POST['title'] ?? '');
$url = trim($_POST['url'] ?? '');
$icon = trim($_POST['icon'] ?? '');

// Validation
$errors = [];
if (empty($title)) {
  $errors[] = 'Link title is required';
} elseif (strlen($title) > 100) {
  $errors[] = 'Link title must be less than 100 characters';
}

if (empty($url)) {
  $errors[] = 'URL is required';
} elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
  $errors[] = 'Please enter a valid URL';
} elseif (strlen($url) > 2000) {
  $errors[] = 'URL is too long';
}

// Check link limit (optional - pro version could have unlimited)
$stmt = $pdo->prepare('SELECT COUNT(*) FROM links WHERE user_id = ?');
$stmt->execute([$user_id]);
$linkCount = $stmt->fetchColumn();
if ($linkCount >= 50) {
  $errors[] = 'Maximum 50 links allowed';
}

if (!empty($errors)) {
  $_SESSION['error'] = implode('. ', $errors);
  header('Location: /dashboard/index.php?tab=links');
  exit;
}

try {
  // Get the highest position and add 1
  $stmt = $pdo->prepare('SELECT COALESCE(MAX(position), -1) + 1 FROM links WHERE user_id = ?');
  $stmt->execute([$user_id]);
  $nextPosition = $stmt->fetchColumn();
  
  $pdo->prepare('INSERT INTO links (user_id, title, url, icon, position, is_active) VALUES (?, ?, ?, ?, ?, 1)')
      ->execute([$user_id, $title, $url, $icon ?: null, $nextPosition]);
  
  $_SESSION['success'] = 'Link added successfully!';
} catch (Exception $e) {
  $_SESSION['error'] = 'Failed to add link. Please try again.';
  error_log('Add link error: ' . $e->getMessage());
}

header('Location: /dashboard/index.php?tab=links');
exit;
