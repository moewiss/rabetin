<?php
require __DIR__.'/../includes/db.php';
require __DIR__.'/../includes/auth.php'; require_login();
$user_id = current_user_id();

$instagram = trim($_POST['instagram'] ?? '');
$tiktok    = trim($_POST['tiktok'] ?? '');
$custom    = trim($_POST['custom'] ?? '');

$pdo->beginTransaction();
try {
  // clear existing and reinsert (simple approach)
  $pdo->prepare('DELETE FROM embeds WHERE user_id=?')->execute([$user_id]);

  $pos=0;
  if ($instagram !== '') {
    $pdo->prepare('INSERT INTO embeds (user_id,type,embed_url,embed_html,is_active,position) VALUES (?,?,?,?,1,?)')
        ->execute([$user_id,'instagram', filter_var($instagram, FILTER_VALIDATE_URL)?$instagram:null, !filter_var($instagram, FILTER_VALIDATE_URL)?$instagram:null, $pos++]);
  }
  if ($tiktok !== '') {
    $pdo->prepare('INSERT INTO embeds (user_id,type,embed_url,embed_html,is_active,position) VALUES (?,?,?,?,1,?)')
        ->execute([$user_id,'tiktok', filter_var($tiktok, FILTER_VALIDATE_URL)?$tiktok:null, !filter_var($tiktok, FILTER_VALIDATE_URL)?$tiktok:null, $pos++]);
  }
  if ($custom !== '') {
    $pdo->prepare('INSERT INTO embeds (user_id,type,embed_url,embed_html,is_active,position) VALUES (?,?,?,?,1,?)')
        ->execute([$user_id,'custom', null, $custom, $pos++]);
  }

  $pdo->commit();
} catch (Throwable $e) {
  $pdo->rollBack();
}
header('Location: /dashboard/index.php?tab=embeds');
