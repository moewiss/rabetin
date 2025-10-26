<?php
require __DIR__.'/../includes/db.php';
require __DIR__.'/../includes/auth.php'; require_login();
$user_id = current_user_id();
$id = (int)($_POST['id']??0);
$state = (int)($_POST['state']??0);
$pdo->prepare('UPDATE links SET is_active=? WHERE id=? AND user_id=?')->execute([$state,$id,$user_id]);
header('Location: /dashboard/index.php?tab=links');
