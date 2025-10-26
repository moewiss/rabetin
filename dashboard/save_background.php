<?php
require __DIR__.'/../includes/db.php';
require __DIR__.'/../includes/auth.php'; 
require_login();
$user_id = current_user_id();

$errors = [];
$bg_color = $_POST['bg_color'] ?? null;
$gs = $_POST['gradient_start'] ?? null;
$ge = $_POST['gradient_end'] ?? null;
$bgImagePath = null;

// Validate colors
if ($bg_color && !preg_match('/^#[0-9A-F]{6}$/i', $bg_color)) {
  $errors[] = 'Invalid background color format';
}
if ($gs && !preg_match('/^#[0-9A-F]{6}$/i', $gs)) {
  $errors[] = 'Invalid gradient start color format';
}
if ($ge && !preg_match('/^#[0-9A-F]{6}$/i', $ge)) {
  $errors[] = 'Invalid gradient end color format';
}

// Handle background image upload with optimization
if (!empty($_FILES['bg_image']['name'])) {
  $file = $_FILES['bg_image'];
  
  if ($file['error'] !== UPLOAD_ERR_OK) {
    $errors[] = 'File upload error';
  } elseif ($file['size'] > 10 * 1024 * 1024) { // 10MB max for backgrounds
    $errors[] = 'Background image must be less than 10MB';
  } else {
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    
    if (!in_array($ext, $allowed)) {
      $errors[] = 'Background must be JPG, PNG, or WEBP';
    } else {
      $imageInfo = @getimagesize($file['tmp_name']);
      if ($imageInfo === false) {
        $errors[] = 'Uploaded file is not a valid image';
      } else {
        $fn = 'bg_'.$user_id.'_'.time().'.'.$ext;
        $dest = __DIR__.'/../uploads/profile_images/'.$fn;
        
        if (!is_dir(dirname($dest))) {
          mkdir(dirname($dest), 0775, true);
        }
        
        // Optimize and resize background image
        $uploaded = false;
        switch ($imageInfo[2]) {
          case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($file['tmp_name']);
            break;
          case IMAGETYPE_PNG:
            $source = imagecreatefrompng($file['tmp_name']);
            break;
          case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($file['tmp_name']);
            break;
          default:
            $source = false;
        }
        
        if ($source) {
          // Resize to max 1920px width
          $width = imagesx($source);
          $height = imagesy($source);
          $maxWidth = 1920;
          
          if ($width > $maxWidth) {
            $ratio = $width / $height;
            $newWidth = $maxWidth;
            $newHeight = $maxWidth / $ratio;
          } else {
            $newWidth = $width;
            $newHeight = $height;
          }
          
          $resized = imagecreatetruecolor($newWidth, $newHeight);
          
          if ($imageInfo[2] == IMAGETYPE_PNG) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
          }
          
          imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
          
          if ($ext === 'png') {
            $uploaded = imagepng($resized, $dest, 9);
          } elseif ($ext === 'webp') {
            $uploaded = imagewebp($resized, $dest, 85);
          } else {
            $uploaded = imagejpeg($resized, $dest, 85);
          }
          
          imagedestroy($source);
          imagedestroy($resized);
          
          if ($uploaded) {
            $bgImagePath = '/uploads/profile_images/'.$fn;
          }
        }
      }
    }
  }
}

if (!empty($errors)) {
  $_SESSION['error'] = implode('. ', $errors);
  header('Location: /dashboard/index.php?tab=background');
  exit;
}

try {
  $sql = 'UPDATE profiles SET bg_color=?, gradient_start=?, gradient_end=?'.
         ($bgImagePath ? ', bg_image=?' : '').' WHERE user_id=?';
  $params = [$bg_color, $gs, $ge];
  if ($bgImagePath) $params[] = $bgImagePath;
  $params[] = $user_id;
  
  $pdo->prepare($sql)->execute($params);
  
  $_SESSION['success'] = 'Background updated successfully!';
} catch (Exception $e) {
  $_SESSION['error'] = 'Failed to update background. Please try again.';
  error_log('Background update error: ' . $e->getMessage());
}

header('Location: /dashboard/index.php?tab=background');
exit;
