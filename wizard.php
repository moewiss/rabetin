<?php
require __DIR__.'/includes/db.php';
require __DIR__.'/includes/auth.php';
require_login();

$user_id = current_user_id();

// Check if wizard is already completed
$stmt = $pdo->prepare('SELECT wizard_completed FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($user && $user['wizard_completed']) {
  header('Location: /dashboard/index.php');
  exit;
}

// Get current profile data
$profile = $pdo->prepare('SELECT * FROM profiles WHERE user_id=?');
$profile->execute([$user_id]);
$profile = $profile->fetch();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Welcome Wizard — Rabetin.bio</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    body {
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      margin: 0;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: #e2e8f0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .wizard-container {
      max-width: 640px;
      width: 100%;
      background: #111827;
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.4);
      overflow: hidden;
    }
    .wizard-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 32px;
      text-align: center;
    }
    .wizard-header h1 {
      margin: 0 0 8px 0;
      font-size: 32px;
      font-weight: 700;
    }
    .wizard-header p {
      margin: 0;
      opacity: 0.95;
      font-size: 16px;
    }
    .progress-bar {
      height: 4px;
      background: #1f2937;
      position: relative;
    }
    .progress-bar-fill {
      height: 100%;
      background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
      transition: width 0.3s ease;
      width: 25%;
    }
    .wizard-body {
      padding: 40px;
    }
    .step {
      display: none;
    }
    .step.active {
      display: block;
      animation: fadeIn 0.4s ease;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .step h2 {
      margin: 0 0 12px 0;
      font-size: 24px;
      color: #f9fafb;
    }
    .step p {
      margin: 0 0 24px 0;
      color: #9ca3af;
      line-height: 1.6;
    }
    label {
      display: block;
      margin: 16px 0 8px 0;
      font-weight: 500;
      color: #d1d5db;
    }
    input, textarea, select {
      width: 100%;
      padding: 12px 16px;
      border-radius: 10px;
      border: 1px solid #374151;
      background: #0b1220;
      color: #e2e8f0;
      font-size: 15px;
      box-sizing: border-box;
      transition: border-color 0.2s;
    }
    input:focus, textarea:focus, select:focus {
      outline: none;
      border-color: #667eea;
    }
    input[type="file"] {
      padding: 10px;
      border: 2px dashed #374151;
      cursor: pointer;
    }
    input[type="file"]:hover {
      border-color: #667eea;
    }
    textarea {
      resize: vertical;
      min-height: 100px;
    }
    .avatar-preview {
      margin-top: 16px;
      text-align: center;
    }
    .avatar-preview img {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid #667eea;
    }
    .theme-selector {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
      margin-top: 16px;
    }
    .theme-option {
      padding: 24px;
      border: 2px solid #374151;
      border-radius: 12px;
      cursor: pointer;
      text-align: center;
      transition: all 0.2s;
      background: #0b1220;
    }
    .theme-option:hover {
      border-color: #667eea;
      transform: translateY(-2px);
    }
    .theme-option.selected {
      border-color: #667eea;
      background: rgba(102, 126, 234, 0.1);
    }
    .theme-option input[type="radio"] {
      display: none;
    }
    .theme-icon {
      font-size: 48px;
      margin-bottom: 8px;
    }
    .button-group {
      display: flex;
      gap: 12px;
      margin-top: 32px;
    }
    button {
      flex: 1;
      padding: 14px 24px;
      border-radius: 10px;
      border: none;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
    }
    .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }
    .btn-secondary {
      background: #374151;
      color: #d1d5db;
    }
    .btn-secondary:hover {
      background: #4b5563;
    }
    .btn-secondary:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
    .skip-link {
      text-align: center;
      margin-top: 16px;
    }
    .skip-link a {
      color: #9ca3af;
      text-decoration: none;
      font-size: 14px;
    }
    .skip-link a:hover {
      color: #d1d5db;
      text-decoration: underline;
    }
    .success-icon {
      font-size: 80px;
      text-align: center;
      margin-bottom: 24px;
    }
    .link-preview {
      background: #0b1220;
      padding: 16px;
      border-radius: 10px;
      margin-top: 16px;
      border: 1px solid #374151;
    }
    .link-preview h4 {
      margin: 0 0 8px 0;
      color: #667eea;
    }
  </style>
</head>
<body>
  <div class="wizard-container">
    <div class="wizard-header">
      <h1>🎉 Welcome to Rabetin.bio!</h1>
      <p>Let's set up your profile in just a few steps</p>
    </div>
    <div class="progress-bar">
      <div class="progress-bar-fill" id="progressBar"></div>
    </div>
    
    <div class="wizard-body">
      <form id="wizardForm" method="post" action="/save_wizard.php" enctype="multipart/form-data">
        
        <!-- Step 1: Basic Info -->
        <div class="step active" data-step="1">
          <h2>👤 Let's start with the basics</h2>
          <p>Tell us a bit about yourself. You can always change this later!</p>
          
          <label for="display_name">Display Name *</label>
          <input type="text" id="display_name" name="display_name" value="<?=htmlspecialchars($profile['display_name']??'')?>" placeholder="Your name or brand" required>
          
          <label for="bio">Bio</label>
          <textarea id="bio" name="bio" placeholder="Tell people about yourself... What do you do? What are you passionate about?"><?=htmlspecialchars($profile['bio']??'')?></textarea>
          
          <div class="button-group">
            <button type="button" class="btn-primary" onclick="nextStep()">Next →</button>
          </div>
        </div>

        <!-- Step 2: Avatar & Theme -->
        <div class="step" data-step="2">
          <h2>🎨 Customize your look</h2>
          <p>Choose an avatar and theme to make your page unique</p>
          
          <label for="avatar">Profile Picture</label>
          <input type="file" id="avatar" name="avatar" accept="image/*" onchange="previewAvatar(this)">
          <div class="avatar-preview" id="avatarPreview" style="<?=empty($profile['avatar'])?'display:none':''?>">
            <?php if (!empty($profile['avatar'])): ?>
              <img src="<?=htmlspecialchars($profile['avatar'])?>" alt="Avatar">
            <?php else: ?>
              <img src="" alt="Avatar" style="display:none">
            <?php endif; ?>
          </div>
          
          <label>Theme</label>
          <div class="theme-selector">
            <div class="theme-option <?=($profile['theme']??'dark')==='dark'?'selected':''?>" onclick="selectTheme('dark')">
              <div class="theme-icon">🌙</div>
              <strong>Dark</strong>
              <input type="radio" name="theme" value="dark" <?=($profile['theme']??'dark')==='dark'?'checked':''?>>
            </div>
            <div class="theme-option <?=($profile['theme']??'')==='light'?'selected':''?>" onclick="selectTheme('light')">
              <div class="theme-icon">☀️</div>
              <strong>Light</strong>
              <input type="radio" name="theme" value="light" <?=($profile['theme']??'')==='light'?'checked':''?>>
            </div>
          </div>
          
          <div class="button-group">
            <button type="button" class="btn-secondary" onclick="prevStep()">← Back</button>
            <button type="button" class="btn-primary" onclick="nextStep()">Next →</button>
          </div>
        </div>

        <!-- Step 3: Add First Link -->
        <div class="step" data-step="3">
          <h2>🔗 Add your first link</h2>
          <p>Share your most important link - portfolio, store, social profile, or anything else!</p>
          
          <label for="link_title">Link Title</label>
          <input type="text" id="link_title" name="link_title" placeholder="e.g., My Portfolio, Shop Now, YouTube Channel">
          
          <label for="link_url">URL</label>
          <input type="url" id="link_url" name="link_url" placeholder="https://...">
          
          <div class="link-preview">
            <h4>💡 Pro Tip</h4>
            <p style="margin:0;color:#9ca3af;font-size:14px;">You can add more links and customize them in your dashboard after completing this wizard.</p>
          </div>
          
          <div class="button-group">
            <button type="button" class="btn-secondary" onclick="prevStep()">← Back</button>
            <button type="button" class="btn-primary" onclick="nextStep()">Next →</button>
          </div>
          <div class="skip-link">
            <a href="#" onclick="event.preventDefault(); nextStep();">Skip this step</a>
          </div>
        </div>

        <!-- Step 4: Complete -->
        <div class="step" data-step="4">
          <div class="success-icon">🚀</div>
          <h2 style="text-align:center">All set!</h2>
          <p style="text-align:center">Your profile is ready. Click below to go to your dashboard and explore all the features.</p>
          
          <div class="link-preview" style="background: rgba(102, 126, 234, 0.1); border-color: #667eea;">
            <h4>✨ What's next?</h4>
            <ul style="margin:8px 0 0 0;color:#d1d5db;font-size:14px;line-height:1.8">
              <li>Add more custom links</li>
              <li>Connect your social media accounts</li>
              <li>Customize your background with colors or images</li>
              <li>Embed Instagram or TikTok content</li>
              <li>Share your page: rabetin.bio/<?=htmlspecialchars($pdo->query("SELECT username FROM users WHERE id=$user_id")->fetchColumn())?></li>
            </ul>
          </div>
          
          <div class="button-group">
            <button type="button" class="btn-secondary" onclick="prevStep()">← Back</button>
            <button type="submit" class="btn-primary">Go to Dashboard →</button>
          </div>
        </div>

      </form>
    </div>
  </div>

  <script>
    let currentStep = 1;
    const totalSteps = 4;

    function updateProgress() {
      const progress = (currentStep / totalSteps) * 100;
      document.getElementById('progressBar').style.width = progress + '%';
    }

    function showStep(step) {
      document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
      document.querySelector(`.step[data-step="${step}"]`).classList.add('active');
      currentStep = step;
      updateProgress();
      window.scrollTo({top: 0, behavior: 'smooth'});
    }

    function nextStep() {
      if (currentStep < totalSteps) {
        // Validate current step
        if (currentStep === 1) {
          const displayName = document.getElementById('display_name').value.trim();
          if (!displayName) {
            alert('Please enter your display name');
            return;
          }
        }
        showStep(currentStep + 1);
      }
    }

    function prevStep() {
      if (currentStep > 1) {
        showStep(currentStep - 1);
      }
    }

    function selectTheme(theme) {
      document.querySelectorAll('.theme-option').forEach(opt => {
        opt.classList.remove('selected');
      });
      event.currentTarget.classList.add('selected');
      document.querySelector(`input[value="${theme}"]`).checked = true;
    }

    function previewAvatar(input) {
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          const preview = document.getElementById('avatarPreview');
          const img = preview.querySelector('img');
          img.src = e.target.result;
          img.style.display = 'block';
          preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
      }
    }

    // Initialize progress bar
    updateProgress();
  </script>
</body>
</html>

