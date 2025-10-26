<?php
require __DIR__.'/../includes/db.php';
require __DIR__.'/../includes/auth.php';
require_login();

$user_id = current_user_id();
$id = (int)($_POST['id'] ?? 0);
$state = (int)($_POST['state'] ?? 0);

if ($id <= 0) {
  $_SESSION['error'] = 'Invalid link ID';
  header('Location: /dashboard/index.php?tab=links');
  exit;
}

if (!in_array($state, [0, 1])) {
  $_SESSION['error'] = 'Invalid state';
  header('Location: /dashboard/index.php?tab=links');
  exit;
}

try {
  $stmt = $pdo->prepare('UPDATE links SET is_active=? WHERE id=? AND user_id=?');
  $stmt->execute([$state, $id, $user_id]);
  
  if ($stmt->rowCount() > 0) {
    $_SESSION['success'] = $state ? 'Link shown successfully!' : 'Link hidden successfully!';
  } else {
    $_SESSION['error'] = 'Link not found';
  }
} catch (Exception $e) {
  $_SESSION['error'] = 'Failed to update link. Please try again.';
  error_log('Toggle link error: ' . $e->getMessage());
}

header('Location: /dashboard/index.php?tab=links');
exit;
