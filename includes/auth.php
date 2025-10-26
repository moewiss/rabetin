<?php
// /var/www/rabetin/includes/auth.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function require_login() {
  if (empty($_SESSION['user_id'])) {
    header('Location: /login.php'); exit;
  }
}

function current_user_id() {
  return $_SESSION['user_id'] ?? null;
}

function require_wizard_completed() {
  global $pdo;
  
  if (empty($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
  }
  
  $stmt = $pdo->prepare('SELECT wizard_completed FROM users WHERE id = ?');
  $stmt->execute([$_SESSION['user_id']]);
  $user = $stmt->fetch();
  
  if ($user && empty($user['wizard_completed'])) {
    header('Location: /wizard.php');
    exit;
  }
}
