<?php
require __DIR__.'/../includes/db.php';
require __DIR__.'/../includes/auth.php'; require_login();
$user_id = current_user_id();

$handles = $_POST['handle'] ?? [];
$urls    = $_POST['url'] ?? [];
$active  = $_POST['active'] ?? [];
$order   = $_POST['order'] ?? ''; // comma separated platforms
$ordering = array_filter(explode(',', $order));

$platforms = ['facebook','instagram','x','tiktok','youtube','email','linkedin','threads','github','reddit','twitch','discord'];

$pdo->beginTransaction();
try {
  foreach ($platforms as $idx=>$p) {
    $pos = $idx;
    if (!empty($ordering)) {
      $pos = array_search($p, $ordering);
      if ($pos===false) $pos = $idx;
    }
    $h = trim($handles[$p] ?? '');
    $u = trim($urls[$p] ?? '');
    $a = isset($active[$p]) ? 1 : 0;

    // upsert
    $pdo->prepare('
      INSERT INTO social_accounts (user_id, platform, handle, url, is_active, position)
      VALUES (?,?,?,?,?,?)
      ON DUPLICATE KEY UPDATE handle=VALUES(handle), url=VALUES(url), is_active=VALUES(is_active), position=VALUES(position)
    ')->execute([$user_id,$p,$h?:null,$u?:null,$a,$pos]);
  }
  $pdo->commit();
} catch (Throwable $e) {
  $pdo->rollBack();
}
header('Location: /dashboard/index.php?tab=socials');
