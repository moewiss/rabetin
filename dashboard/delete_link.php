<?php
require __DIR__.'/../includes/db.php';
require __DIR__.'/../includes/auth.php';
require_login();

$user_id = current_user_id();
$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
  $_SESSION['error'] = 'Invalid link ID';
  header('Location: /dashboard/index.php?tab=links');
  exit;
}

try {
  $stmt = $pdo->prepare('DELETE FROM links WHERE id = ? AND user_id = ?');
  $stmt->execute([$id, $user_id]);
  
  if ($stmt->rowCount() > 0) {
    $_SESSION['success'] = 'Link deleted successfully!';
  } else {
    $_SESSION['error'] = 'Link not found or already deleted';
  }
} catch (Exception $e) {
  $_SESSION['error'] = 'Failed to delete link. Please try again.';
  error_log('Delete link error: ' . $e->getMessage());
}

header('Location: /dashboard/index.php?tab=links');
exit;

