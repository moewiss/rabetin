<?php
require __DIR__.'/../includes/db.php';
require __DIR__.'/../includes/auth.php';
require_login();

$user_id = current_user_id();

$instagram = trim($_POST['instagram'] ?? '');
$tiktok = trim($_POST['tiktok'] ?? '');
$custom = trim($_POST['custom'] ?? '');

$errors = [];

// Validate embeds
if (strlen($instagram) > 10000) {
  $errors[] = 'Instagram embed is too long';
}
if (strlen($tiktok) > 10000) {
  $errors[] = 'TikTok embed is too long';
}
if (strlen($custom) > 10000) {
  $errors[] = 'Custom embed is too long';
}

// Basic XSS protection for HTML embeds
if (!empty($custom)) {
  // Only allow iframe tags and basic HTML
  $allowed_tags = '<iframe><script>';
  $stripped = strip_tags($custom, $allowed_tags);
  if ($stripped !== $custom) {
    $errors[] = 'Custom embed contains invalid HTML';
  }
}

if (!empty($errors)) {
  $_SESSION['error'] = implode('. ', $errors);
  header('Location: /dashboard/index.php?tab=embeds');
  exit;
}

$pdo->beginTransaction();
try {
  // Clear existing and reinsert
  $pdo->prepare('DELETE FROM embeds WHERE user_id=?')->execute([$user_id]);

  $pos = 0;
  if ($instagram !== '') {
    $isUrl = filter_var($instagram, FILTER_VALIDATE_URL);
    $pdo->prepare('INSERT INTO embeds (user_id,type,embed_url,embed_html,is_active,position) VALUES (?,?,?,?,1,?)')
        ->execute([$user_id, 'instagram', $isUrl ? $instagram : null, !$isUrl ? $instagram : null, $pos++]);
  }
  
  if ($tiktok !== '') {
    $isUrl = filter_var($tiktok, FILTER_VALIDATE_URL);
    $pdo->prepare('INSERT INTO embeds (user_id,type,embed_url,embed_html,is_active,position) VALUES (?,?,?,?,1,?)')
        ->execute([$user_id, 'tiktok', $isUrl ? $tiktok : null, !$isUrl ? $tiktok : null, $pos++]);
  }
  
  if ($custom !== '') {
    $pdo->prepare('INSERT INTO embeds (user_id,type,embed_url,embed_html,is_active,position) VALUES (?,?,?,?,1,?)')
        ->execute([$user_id, 'custom', null, $custom, $pos++]);
  }

  $pdo->commit();
  $_SESSION['success'] = 'Embeds updated successfully!';
} catch (Exception $e) {
  $pdo->rollBack();
  $_SESSION['error'] = 'Failed to update embeds. Please try again.';
  error_log('Save embeds error: ' . $e->getMessage());
}

header('Location: /dashboard/index.php?tab=embeds');
exit;
