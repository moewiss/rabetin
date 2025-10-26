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
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Log in — Rabetin.bio</title>
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
    
    .alert-success {
      background: #d1fae5;
      color: #065f46;
      border: 1px solid #6ee7b7;
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
      
      <h2>Welcome back</h2>
      <p class="subtitle">Log in to your account to continue</p>
      
      <?php if ($msg): ?>
        <div class="alert alert-success"><?=htmlspecialchars($msg)?></div>
      <?php endif; ?>
      
      <?php if ($error): ?>
        <div class="alert alert-error"><?=htmlspecialchars($error)?></div>
      <?php endif; ?>
      
      <form method="post" autocomplete="on">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" placeholder="Enter your username" required autofocus>
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>
        
        <button type="submit" class="btn">Log in</button>
      </form>
      
      <div class="divider">or</div>
      
      <p class="footer-link">Don't have an account? <a href="/signup.php">Sign up</a></p>
    </div>
  </div>
</body>
</html>
