<?php
require __DIR__.'/../includes/db.php';
require __DIR__.'/../includes/auth.php'; 
require_login();
$user_id = current_user_id();

$errors = [];
$display_name = trim($_POST['display_name'] ?? '');
$bio = trim($_POST['bio'] ?? '');
$theme = $_POST['theme'] ?? 'dark';
$font = $_POST['font_family'] ?? 'system-ui';

// Validation
if (empty($display_name)) {
  $errors[] = 'Display name is required';
} elseif (strlen($display_name) > 100) {
  $errors[] = 'Display name must be less than 100 characters';
}

if (strlen($bio) > 500) {
  $errors[] = 'Bio must be less than 500 characters';
}

if (!in_array($theme, ['dark', 'light'])) {
  $errors[] = 'Invalid theme selected';
}

if (!in_array($font, ['system-ui', 'Inter', 'Poppins'])) {
  $errors[] = 'Invalid font selected';
}

// Handle avatar upload with optimization
$avatarPath = null;
if (!empty($_FILES['avatar']['name'])) {
  $file = $_FILES['avatar'];
  
  // Validate file
  if ($file['error'] !== UPLOAD_ERR_OK) {
    $errors[] = 'File upload error';
  } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB max
    $errors[] = 'Avatar must be less than 5MB';
  } else {
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    
    if (!in_array($ext, $allowed)) {
      $errors[] = 'Avatar must be JPG, PNG, or WEBP';
    } else {
      // Verify it's actually an image
      $imageInfo = @getimagesize($file['tmp_name']);
      if ($imageInfo === false) {
        $errors[] = 'Uploaded file is not a valid image';
      } else {
        $fn = 'av_'.$user_id.'_'.time().'.'.$ext;
        $dest = __DIR__.'/../uploads/profile_images/'.$fn;
        
        if (!is_dir(dirname($dest))) {
          mkdir(dirname($dest), 0775, true);
        }
        
        // Optimize and resize image
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
          // Resize to max 500x500
          $width = imagesx($source);
          $height = imagesy($source);
          $max = 500;
          
          if ($width > $max || $height > $max) {
            $ratio = $width / $height;
            if ($width > $height) {
              $newWidth = $max;
              $newHeight = $max / $ratio;
            } else {
              $newHeight = $max;
              $newWidth = $max * $ratio;
            }
          } else {
            $newWidth = $width;
            $newHeight = $height;
          }
          
          $resized = imagecreatetruecolor($newWidth, $newHeight);
          
          // Preserve transparency for PNG
          if ($imageInfo[2] == IMAGETYPE_PNG) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
          }
          
          imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
          
          // Save optimized image
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
            $avatarPath = '/uploads/profile_images/'.$fn;
          }
        }
      }
    }
  }
}

if (!empty($errors)) {
  $_SESSION['error'] = implode('. ', $errors);
  header('Location: /dashboard/index.php?tab=profile');
  exit;
}

try {
  $sql = 'UPDATE profiles SET display_name=?, bio=?, theme=?, font_family=?'.
         ($avatarPath ? ', avatar=?' : '').' WHERE user_id=?';
  $params = [$display_name, $bio, $theme, $font];
  if ($avatarPath) $params[] = $avatarPath;
  $params[] = $user_id;
  
  $pdo->prepare($sql)->execute($params);
  
  $_SESSION['success'] = 'Profile updated successfully!';
} catch (Exception $e) {
  $_SESSION['error'] = 'Failed to update profile. Please try again.';
  error_log('Profile update error: ' . $e->getMessage());
}

header('Location: /dashboard/index.php?tab=profile');
exit;
