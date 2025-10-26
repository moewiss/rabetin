<?php
require __DIR__.'/../includes/db.php';
require __DIR__.'/../includes/auth.php'; require_login();
$user_id = current_user_id();

$body = json_decode(file_get_contents('php://input'), true);
$order = $body['order'] ?? [];

if (!is_array($order)) { http_response_code(400); echo 'Bad request'; exit; }

$pos = 0;
$stmt = $pdo->prepare('UPDATE links SET position=? WHERE id=? AND user_id=?');
foreach ($order as $id) {
  $stmt->execute([$pos++, (int)$id, $user_id]);
}
echo 'Order saved';
