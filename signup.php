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
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Sign up — Rabetin.bio</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }
    
    .container {
      width: 100%;
      max-width: 400px;
      animation: fadeIn 0.6s ease-out;
    }
    
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .card {
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      padding: 48px 40px;
    }
    
    .logo {
      text-align: center;
      margin-bottom: 32px;
    }
    
    .logo h1 {
      font-size: 28px;
      font-weight: 700;
      color: #1a1a1a;
      letter-spacing: -0.5px;
    }
    
    .logo-accent {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    h2 {
      font-size: 24px;
      font-weight: 600;
      color: #1a1a1a;
      margin-bottom: 8px;
      text-align: center;
    }
    
    .subtitle {
      text-align: center;
      color: #6b7280;
      font-size: 14px;
      margin-bottom: 32px;
    }
    
    .alert {
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 24px;
      font-size: 14px;
      animation: slideDown 0.3s ease-out;
    }
    
    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .alert-error {
      background: #fee2e2;
      color: #991b1b;
      border: 1px solid #fca5a5;
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    label {
      display: block;
      font-size: 14px;
      font-weight: 500;
      color: #374151;
      margin-bottom: 8px;
    }
    
    input {
      width: 100%;
      padding: 12px 16px;
      font-size: 15px;
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      background: #ffffff;
      color: #1a1a1a;
      transition: all 0.2s ease;
      font-family: inherit;
    }
    
    input:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    input::placeholder {
      color: #9ca3af;
    }
    
    .password-hint {
      font-size: 12px;
      color: #6b7280;
      margin-top: 4px;
    }
    
    .btn {
      width: 100%;
      padding: 14px 24px;
      font-size: 15px;
      font-weight: 600;
      color: #ffffff;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.2s ease;
      margin-top: 8px;
      font-family: inherit;
    }
    
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
    }
    
    .btn:active {
      transform: translateY(0);
    }
    
    .terms {
      font-size: 12px;
      color: #6b7280;
      text-align: center;
      margin-top: 16px;
      line-height: 1.5;
    }
    
    .terms a {
      color: #667eea;
      text-decoration: none;
    }
    
    .terms a:hover {
      text-decoration: underline;
    }
    
    .divider {
      text-align: center;
      margin: 32px 0 24px 0;
      color: #9ca3af;
      font-size: 14px;
    }
    
    .footer-link {
      text-align: center;
      font-size: 14px;
      color: #6b7280;
    }
    
    .footer-link a {
      color: #667eea;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.2s ease;
    }
    
    .footer-link a:hover {
      color: #764ba2;
      text-decoration: underline;
    }
    
    @media (max-width: 480px) {
      .card {
        padding: 32px 24px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <div class="logo">
        <h1><span class="logo-accent">Rabetin</span>.bio</h1>
      </div>
      
      <h2>Create your account</h2>
      <p class="subtitle">Join us and share your links with the world</p>
      
      <?php if ($errors): ?>
        <div class="alert alert-error">
          <?php foreach($errors as $err): ?>
            <div><?=htmlspecialchars($err)?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      
      <form method="post" autocomplete="on">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" placeholder="mohammad" value="<?=htmlspecialchars($_POST['username'] ?? '')?>" required autofocus>
          <p class="password-hint">3-20 characters (letters, numbers, underscores)</p>
        </div>
        
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="you@example.com" value="<?=htmlspecialchars($_POST['email'] ?? '')?>" required>
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="••••••••" required>
          <p class="password-hint">At least 6 characters</p>
        </div>
        
        <button type="submit" class="btn">Create account</button>
        
        <p class="terms">
          By signing up, you agree to our <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
        </p>
      </form>
      
      <div class="divider">or</div>
      
      <p class="footer-link">Already have an account? <a href="/login.php">Log in</a></p>
    </div>
  </div>
</body>
</html>
