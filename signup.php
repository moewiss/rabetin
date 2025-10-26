<?php
require __DIR__.'/includes/db.php';
require __DIR__.'/includes/auth.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = strtolower(trim($_POST['username'] ?? ''));
  $email    = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if (!preg_match('/^[a-z0-9_]{3,20}$/', $username)) {
    $errors[] = 'Username must be 3–20 chars (letters, numbers, underscores).';
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email.';
  }
  if (strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters.';
  }

  if (!$errors) {
    // unique checks
    $stmt = $pdo->prepare('SELECT 1 FROM users WHERE username = ? OR email = ?');
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
      $errors[] = 'Username or email already exists.';
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $pdo->beginTransaction();
      try {
        $pdo->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)')
            ->execute([$username, $email, $hash]);

        $user_id = (int)$pdo->lastInsertId();
        $pdo->prepare('INSERT INTO profiles (user_id, display_name) VALUES (?, ?)')
            ->execute([$user_id, $username]);

        $pdo->commit();
        header('Location: /login.php?registered=1'); exit;
      } catch (Throwable $e) {
        $pdo->rollBack();
        $errors[] = 'Something went wrong. Try again.';
      }
    }
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Sign up — Rabetin.bio</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body{font-family:system-ui,Segoe UI,Roboto,Arial;margin:0;background:#0f172a;color:#e2e8f0}
    .wrap{max-width:420px;margin:6vh auto;padding:24px;background:#111827;border:1px solid #253046;border-radius:14px}
    input,button{width:100%;padding:12px;border-radius:10px;border:1px solid #334155;background:#0b1220;color:#e2e8f0;margin-top:10px}
    button{cursor:pointer}
    .error{background:#7f1d1d;padding:10px;border-radius:10px;margin-bottom:10px}
    a{color:#93c5fd}
  </style>
</head>
<body>
  <div class="wrap">
    <h2>Create your account</h2>
    <?php if ($errors): ?>
      <div class="error"><?=htmlspecialchars(implode(' ', $errors))?></div>
    <?php endif; ?>
    <form method="post" autocomplete="on">
      <label>Username</label>
      <input name="username" placeholder="mohammad" required>

      <label>Email</label>
      <input type="email" name="email" placeholder="you@example.com" required>

      <label>Password</label>
      <input type="password" name="password" placeholder="••••••••" required>

      <button type="submit">Sign up</button>
    </form>
    <p>Already have an account? <a href="/login.php">Log in</a></p>
  </div>
</body>
</html>
