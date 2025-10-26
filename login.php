<?php
require __DIR__.'/includes/db.php';
require __DIR__.'/includes/auth.php';

$msg = isset($_GET['registered']) ? 'Account created. Please log in.' : '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = strtolower(trim($_POST['username'] ?? ''));
  $password = $_POST['password'] ?? '';

  $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
  $stmt->execute([$username]);
  $user = $stmt->fetch();
  if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = (int)$user['id'];
    
    // Check if user has completed wizard
    if (empty($user['wizard_completed'])) {
      header('Location: /wizard.php');
      exit;
    }
    
    header('Location: /dashboard/index.php'); exit;
  } else {
    $error = 'Invalid username or password.';
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Log in — Rabetin.bio</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{font-family:system-ui,Segoe UI,Roboto,Arial;margin:0;background:#0f172a;color:#e2e8f0}
    .wrap{max-width:420px;margin:6vh auto;padding:24px;background:#111827;border:1px solid #253046;border-radius:14px}
    input,button{width:100%;padding:12px;border-radius:10px;border:1px solid #334155;background:#0b1220;color:#e2e8f0;margin-top:10px}
    .msg{background:#064e3b;padding:10px;border-radius:10px;margin-bottom:10px}
    .error{background:#7f1d1d;padding:10px;border-radius:10px;margin-bottom:10px}
    a{color:#93c5fd}
  </style>
</head>
<body>
  <div class="wrap">
    <h2>Log in</h2>
    <?php if ($msg): ?><div class="msg"><?=htmlspecialchars($msg)?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?=htmlspecialchars($error)?></div><?php endif; ?>
    <form method="post" autocomplete="on">
      <label>Username1234</label>
      <input name="username" required>
      <label>Password</label>
      <input type="password" name="password" required>
      <button type="submit">Log in</button>
    </form>
    <p>No account? <a href="/signup.php">Create one</a></p>
  </div>
</body>
</html>
