<?php
require __DIR__.'/../includes/db.php';
require __DIR__.'/../includes/auth.php';
require_login();

$user_id = current_user_id();
$title   = trim($_POST['title'] ?? '');
$url     = trim($_POST['url'] ?? '');
$icon    = trim($_POST['icon'] ?? '');
$pos     = (int)($_POST['position'] ?? 0);

if ($title && filter_var($url, FILTER_VALIDATE_URL)) {
  $pdo->prepare('INSERT INTO links (user_id, title, url, icon, position, is_active) VALUES (?, ?, ?, ?, ?, 1)')
      ->execute([$user_id, $title, $url, $icon ?: null, $pos]);
}

header('Location: /dashboard/index.php'); exit;
