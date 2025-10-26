<?php
require __DIR__.'/../includes/db.php';
require __DIR__.'/../includes/auth.php'; require_login();
$user_id = current_user_id();

$bg_color = $_POST['bg_color']??null;
$gs = $_POST['gradient_start']??null;
$ge = $_POST['gradient_end']??null;
$bgImagePath = null;

if (!empty($_FILES['bg_image']['name'])) {
  $ext = strtolower(pathinfo($_FILES['bg_image']['name'], PATHINFO_EXTENSION));
  if (in_array($ext,['jpg','jpeg','png','webp'])) {
    $fn = 'bg_'.$user_id.'_'.time().'.'.$ext;
    $dest = __DIR__.'/../uploads/profile_images/'.$fn;
    if (!is_dir(dirname($dest))) mkdir(dirname($dest),0775,true);
    if (move_uploaded_file($_FILES['bg_image']['tmp_name'],$dest)) {
      $bgImagePath = '/uploads/profile_images/'.$fn;
    }
  }
}

$sql = 'UPDATE profiles SET bg_color=?, gradient_start=?, gradient_end=?'.
       ($bgImagePath?', bg_image=?':'').' WHERE user_id=?';
$params = [$bg_color,$gs,$ge];
if ($bgImagePath) $params[]=$bgImagePath;
$params[]=$user_id;
$pdo->prepare($sql)->execute($params);

header('Location: /dashboard/index.php?tab=background');
