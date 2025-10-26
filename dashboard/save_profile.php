<?php
require __DIR__.'/../includes/db.php';
require __DIR__.'/../includes/auth.php'; require_login();
$user_id = current_user_id();

$display_name = trim($_POST['display_name']??'');
$bio = trim($_POST['bio']??'');
$theme = $_POST['theme']??'dark';
$font = $_POST['font_family']??'system-ui';

$avatarPath = null;
if (!empty($_FILES['avatar']['name'])) {
  $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
  if (in_array($ext,['jpg','jpeg','png','webp'])) {
    $fn = 'av_'.$user_id.'_'.time().'.'.$ext;
    $dest = __DIR__.'/../uploads/profile_images/'.$fn;
    if (!is_dir(dirname($dest))) mkdir(dirname($dest),0775,true);
    if (move_uploaded_file($_FILES['avatar']['tmp_name'],$dest)) {
      $avatarPath = '/uploads/profile_images/'.$fn;
    }
  }
}

$sql = 'UPDATE profiles SET display_name=?, bio=?, theme=?, font_family=?'.
       ($avatarPath?', avatar=?':'').' WHERE user_id=?';
$params = [$display_name,$bio,$theme,$font];
if ($avatarPath) $params[] = $avatarPath;
$params[] = $user_id;
$pdo->prepare($sql)->execute($params);

header('Location: /dashboard/index.php');
