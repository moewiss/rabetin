<?php
require __DIR__.'/../includes/db.php';
require __DIR__.'/../includes/auth.php';
require_login();

$user_id = current_user_id();

$handles = $_POST['handle'] ?? [];
$urls = $_POST['url'] ?? [];
$active = $_POST['active'] ?? [];
$order = $_POST['order'] ?? '';
$ordering = array_filter(explode(',', $order));

$platforms = ['facebook', 'instagram', 'x', 'tiktok', 'youtube', 'email', 'linkedin', 'threads', 'github', 'reddit', 'twitch', 'discord'];

$errors = [];

// Validate URLs if provided
foreach ($platforms as $p) {
  $url = trim($urls[$p] ?? '');
  if (!empty($url)) {
    if (strlen($url) > 2000) {
      $errors[] = ucfirst($p) . ' URL is too long';
    } elseif (!filter_var($url, FILTER_VALIDATE_URL) && $p !== 'email') {
      $errors[] = ucfirst($p) . ' URL is invalid';
    }
  }
  
  $handle = trim($handles[$p] ?? '');
  if (strlen($handle) > 100) {
    $errors[] = ucfirst($p) . ' handle is too long';
  }
}

if (!empty($errors)) {
  $_SESSION['error'] = implode('. ', $errors);
  header('Location: /dashboard/index.php?tab=socials');
  exit;
}

$pdo->beginTransaction();
try {
  foreach ($platforms as $idx => $p) {
    $pos = $idx;
    if (!empty($ordering)) {
      $foundPos = array_search($p, $ordering);
      if ($foundPos !== false) $pos = $foundPos;
    }
    
    $h = trim($handles[$p] ?? '');
    $u = trim($urls[$p] ?? '');
    $a = isset($active[$p]) ? 1 : 0;

    // upsert
    $pdo->prepare('
      INSERT INTO social_accounts (user_id, platform, handle, url, is_active, position)
      VALUES (?,?,?,?,?,?)
      ON DUPLICATE KEY UPDATE handle=VALUES(handle), url=VALUES(url), is_active=VALUES(is_active), position=VALUES(position)
    ')->execute([$user_id, $p, $h ?: null, $u ?: null, $a, $pos]);
  }
  
  $pdo->commit();
  $_SESSION['success'] = 'Social accounts updated successfully!';
} catch (Exception $e) {
  $pdo->rollBack();
  $_SESSION['error'] = 'Failed to update social accounts. Please try again.';
  error_log('Save socials error: ' . $e->getMessage());
}

header('Location: /dashboard/index.php?tab=socials');
exit;
