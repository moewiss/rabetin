<?php
require __DIR__.'/../includes/db.php';
require __DIR__.'/../includes/auth.php';
require_login();

$user_id = current_user_id();

$body = json_decode(file_get_contents('php://input'), true);
$order = $body['order'] ?? [];

if (!is_array($order)) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Bad request']);
  exit;
}

if (count($order) > 50) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Too many links']);
  exit;
}

try {
  $pdo->beginTransaction();
  
  $pos = 0;
  $stmt = $pdo->prepare('UPDATE links SET position=? WHERE id=? AND user_id=?');
  foreach ($order as $id) {
    $stmt->execute([$pos++, (int)$id, $user_id]);
  }
  
  $pdo->commit();
  echo json_encode(['success' => true, 'message' => 'Order saved successfully!']);
} catch (Exception $e) {
  $pdo->rollBack();
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Failed to save order']);
  error_log('Reorder links error: ' . $e->getMessage());
}
