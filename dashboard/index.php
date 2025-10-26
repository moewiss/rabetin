<?php
require __DIR__.'/../includes/db.php';
require __DIR__.'/../includes/auth.php';
require_login();
require_wizard_completed();

$user_id = current_user_id();
$wizard_completed_msg = isset($_GET['wizard_completed']) ? true : false;
$activeTab = $_GET['tab'] ?? 'profile';

// Get success/error messages
$success_msg = $_SESSION['success'] ?? '';
$error_msg = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Get username for profile URL
$username = $pdo->prepare('SELECT username FROM users WHERE id=?');
$username->execute([$user_id]);
$username = $username->fetchColumn();

// Fetch data
$profile = $pdo->prepare('SELECT * FROM profiles WHERE user_id=?'); $profile->execute([$user_id]); $profile=$profile->fetch();
$links = $pdo->prepare('SELECT * FROM links WHERE user_id=? ORDER BY position ASC, id ASC'); $links->execute([$user_id]); $links=$links->fetchAll();
$socials = $pdo->prepare('SELECT * FROM social_accounts WHERE user_id=? ORDER BY position ASC, platform ASC'); $socials->execute([$user_id]); $socials=$socials->fetchAll();
$embeds = $pdo->prepare('SELECT * FROM embeds WHERE user_id=? AND is_active=1 ORDER BY position ASC, id ASC'); $embeds->execute([$user_id]); $embeds=$embeds->fetchAll();

$platforms = ['facebook','instagram','x','tiktok','youtube','email','linkedin','threads','github','reddit','twitch','discord'];
$socialMap = [];
foreach ($socials as $s) { $socialMap[$s['platform']] = $s; }

// Fetch design templates
$templates = $pdo->query('SELECT * FROM design_templates ORDER BY id ASC')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Dashboard — Rabetin.bio</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
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
      background: #f8fafc;
      color: #1e293b;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }
    
    header {
      background: #ffffff;
      border-bottom: 1px solid #e2e8f0;
      padding: 20px 24px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    
    .header-content {
      max-width: 1200px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .logo {
      font-size: 20px;
      font-weight: 700;
      color: #1e293b;
    }
    
    .logo-accent {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .header-nav {
      display: flex;
      align-items: center;
      gap: 16px;
    }
    
    .btn-tour {
      padding: 10px 20px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: #ffffff;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      font-family: inherit;
    }
    
    .btn-tour:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }
    
    .btn-logout {
      padding: 10px 20px;
      background: #ffffff;
      color: #64748b;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      text-decoration: none;
      display: inline-block;
      font-family: inherit;
    }
    
    .btn-logout:hover {
      background: #f8fafc;
      color: #475569;
      border-color: #cbd5e1;
    }
    
    .wrap {
      max-width: 1200px;
      margin: 0 auto;
      padding: 32px 24px;
    }
    
    .welcome-banner {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 16px;
      padding: 32px;
      margin-bottom: 32px;
      color: #ffffff;
      box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
      animation: slideDown 0.5s ease-out;
    }
    
    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .welcome-banner h3 {
      font-size: 24px;
      margin-bottom: 8px;
      font-weight: 700;
    }
    
    .welcome-banner p {
      font-size: 15px;
      opacity: 0.95;
    }
    
    .tabs {
      display: flex;
      gap: 8px;
      margin-bottom: 24px;
      flex-wrap: wrap;
      background: #ffffff;
      padding: 8px;
      border-radius: 12px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    
    .tab {
      padding: 12px 20px;
      border: none;
      border-radius: 8px;
      background: transparent;
      color: #64748b;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      transition: all 0.2s ease;
      font-family: inherit;
    }
    
    .tab:hover {
      background: #f8fafc;
      color: #475569;
    }
    
    .tab.active {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: #ffffff;
    }
    
    .tab a {
      color: inherit;
      text-decoration: none;
    }
    
    .panel {
      display: none;
      animation: fadeIn 0.3s ease-out;
    }
    
    @keyframes fadeIn {
      from {
        opacity: 0;
      }
      to {
        opacity: 1;
      }
    }
    
    .panel.active {
      display: block;
    }
    
    .card {
      background: #ffffff;
      border-radius: 16px;
      padding: 24px;
      margin-bottom: 24px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
      border: 1px solid #e2e8f0;
    }
    
    .card h3 {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 20px;
      color: #1e293b;
    }
    
    label {
      display: block;
      font-size: 14px;
      font-weight: 500;
      color: #475569;
      margin-bottom: 8px;
      margin-top: 16px;
    }
    
    label:first-child {
      margin-top: 0;
    }
    
    input, textarea, select {
      width: 100%;
      padding: 12px 16px;
      font-size: 15px;
      border: 2px solid #e2e8f0;
      border-radius: 8px;
      background: #ffffff;
      color: #1e293b;
      transition: all 0.2s ease;
      font-family: inherit;
    }
    
    input:focus, textarea:focus, select:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    input[type="color"] {
      height: 48px;
      padding: 4px;
      cursor: pointer;
    }
    
    input[type="file"] {
      border: 2px dashed #cbd5e1;
      background: #f8fafc;
      padding: 20px;
      cursor: pointer;
    }
    
    input[type="file"]:hover {
      border-color: #667eea;
      background: #f1f5f9;
    }
    
    input[type="checkbox"] {
      width: auto;
      margin-right: 8px;
      cursor: pointer;
    }
    
    textarea {
      resize: vertical;
      min-height: 80px;
    }
    
    .grid2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
    }
    
    .grid3 {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 24px;
    }
    
    @media (max-width: 768px) {
      .grid2, .grid3 {
        grid-template-columns: 1fr;
      }
    }
    
    .row {
      display: flex;
      gap: 12px;
      align-items: center;
    }
    
    .btn {
      padding: 12px 24px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: #ffffff;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      font-family: inherit;
    }
    
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }
    
    .btn:active {
      transform: translateY(0);
    }
    
    .btn-secondary {
      background: #ffffff;
      color: #64748b;
      border: 2px solid #e2e8f0;
    }
    
    .btn-secondary:hover {
      background: #f8fafc;
      color: #475569;
      border-color: #cbd5e1;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    
    .list {
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin-top: 16px;
    }
    
    .item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      background: #f8fafc;
      transition: all 0.2s ease;
    }
    
    .item:hover {
      border-color: #cbd5e1;
      background: #ffffff;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    
    .item .handle {
      cursor: grab;
      margin-right: 12px;
      font-size: 18px;
      color: #94a3b8;
    }
    
    .item .handle:active {
      cursor: grabbing;
    }
    
    .item strong {
      text-transform: capitalize;
      color: #1e293b;
      font-weight: 600;
    }
    
    .item input[type="text"], .item input[type="url"] {
      flex: 1;
      min-width: 0;
    }
    
    .item input::placeholder {
      color: #94a3b8;
    }
    
    small {
      display: block;
      margin-top: 12px;
      color: #64748b;
      font-size: 13px;
    }
    
    .avatar-preview {
      margin-top: 16px;
      display: flex;
      justify-content: center;
    }
    
    .avatar-preview img {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid #e2e8f0;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .bg-preview {
      margin-top: 16px;
    }
    
    .bg-preview img {
      width: 100%;
      max-width: 300px;
      border-radius: 12px;
      border: 2px solid #e2e8f0;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    .driver-popover {
      background: #ffffff !important;
      color: #1e293b !important;
      border: 1px solid #e2e8f0 !important;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15) !important;
    }
    
    .driver-popover-title {
      color: #1e293b !important;
      font-weight: 700 !important;
    }
    
    .driver-popover-description {
      color: #64748b !important;
    }
    
    .driver-popover-next-btn {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
      color: white !important;
      border: none !important;
      border-radius: 8px !important;
      padding: 8px 16px !important;
      font-weight: 600 !important;
    }
    
    .driver-popover-prev-btn {
      background: #f1f5f9 !important;
      color: #64748b !important;
      border: 1px solid #e2e8f0 !important;
      border-radius: 8px !important;
      padding: 8px 16px !important;
      font-weight: 600 !important;
    }
    
    .driver-popover-close-btn {
      color: #64748b !important;
    }
    
    /* Toast Notifications */
    .toast-container {
      position: fixed;
      top: 24px;
      right: 24px;
      z-index: 9999;
      display: flex;
      flex-direction: column;
      gap: 12px;
      max-width: 420px;
    }
    
    .toast {
      background: #ffffff;
      border-radius: 12px;
      padding: 16px 20px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
      border: 2px solid;
      display: flex;
      align-items: center;
      gap: 12px;
      animation: toastSlideIn 0.3s ease-out;
      font-size: 14px;
      font-weight: 500;
    }
    
    @keyframes toastSlideIn {
      from {
        opacity: 0;
        transform: translateX(100px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }
    
    .toast-success {
      border-color: #10b981;
      color: #065f46;
    }
    
    .toast-success::before {
      content: '✓';
      display: flex;
      align-items: center;
      justify-content: center;
      width: 24px;
      height: 24px;
      background: #10b981;
      color: #ffffff;
      border-radius: 50%;
      font-weight: 700;
      flex-shrink: 0;
    }
    
    .toast-error {
      border-color: #ef4444;
      color: #991b1b;
    }
    
    .toast-error::before {
      content: '✕';
      display: flex;
      align-items: center;
      justify-content: center;
      width: 24px;
      height: 24px;
      background: #ef4444;
      color: #ffffff;
      border-radius: 50%;
      font-weight: 700;
      flex-shrink: 0;
    }
    
    /* Copy button */
    .copy-btn {
      padding: 8px 16px;
      background: #f8fafc;
      color: #64748b;
      border: 2px solid #e2e8f0;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      font-family: inherit;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    
    .copy-btn:hover {
      background: #ffffff;
      border-color: #cbd5e1;
      color: #475569;
    }
    
    .copy-btn.copied {
      background: #d1fae5;
      border-color: #6ee7b7;
      color: #065f46;
    }
    
    /* Delete button */
    .btn-delete {
      padding: 8px 16px;
      background: #fee2e2;
      color: #991b1b;
      border: 2px solid #fca5a5;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
      font-family: inherit;
    }
    
    .btn-delete:hover {
      background: #fecaca;
      border-color: #f87171;
      color: #7f1d1d;
      box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
    }
    
    /* Loading states */
    .btn-loading {
      position: relative;
      pointer-events: none;
      opacity: 0.7;
    }
    
    .btn-loading::after {
      content: '';
      position: absolute;
      width: 16px;
      height: 16px;
      top: 50%;
      left: 50%;
      margin-left: -8px;
      margin-top: -8px;
      border: 2px solid #ffffff;
      border-radius: 50%;
      border-top-color: transparent;
      animation: spin 0.6s linear infinite;
    }
    
    @keyframes spin {
      to {
        transform: rotate(360deg);
      }
    }
    
    /* Profile URL box */
    .profile-url-box {
      background: #f8fafc;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      padding: 16px;
      margin-top: 24px;
    }
    
    .profile-url-box label {
      font-size: 14px;
      font-weight: 600;
      color: #475569;
      margin-bottom: 8px;
      display: block;
    }
    
    .url-display {
      display: flex;
      gap: 12px;
      align-items: center;
    }
    
    .url-text {
      flex: 1;
      padding: 12px 16px;
      background: #ffffff;
      border: 2px solid #cbd5e1;
      border-radius: 8px;
      font-size: 15px;
      color: #1e293b;
      font-family: 'Monaco', 'Courier New', monospace;
    }
    
    @media (max-width: 768px) {
      .toast-container {
        top: 12px;
        right: 12px;
        left: 12px;
        max-width: none;
      }
      
      .url-display {
        flex-direction: column;
      }
      
      .copy-btn {
        width: 100%;
        justify-content: center;
      }
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css"/>
  <script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>
</head>
<body>
<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<header>
  <div class="header-content">
    <div class="logo">
      <span class="logo-accent">Rabetin</span>.bio Dashboard
    </div>
    <nav class="header-nav">
      <button id="startTour" class="btn-tour">📖 Take a Tour</button>
      <a href="/logout.php" class="btn-logout">Logout</a>
  </nav>
  </div>
</header>
<div class="wrap">
  <?php if ($wizard_completed_msg): ?>
    <div class="welcome-banner">
      <h3>🎉 Welcome to Rabetin.bio!</h3>
      <p>Your profile is set up! Explore all the features below to customize your page.</p>
    </div>
  <?php endif; ?>
  <div class="tabs">
    <div class="tab <?=$activeTab==='profile'?'active':''?>" data-tab="profile">Profile</div>
    <div class="tab <?=$activeTab==='design'?'active':''?>" data-tab="design">🎨 Design</div>
    <div class="tab <?=$activeTab==='background'?'active':''?>" data-tab="background">Background</div>
    <div class="tab <?=$activeTab==='socials'?'active':''?>" data-tab="socials">Social Icons</div>
    <div class="tab <?=$activeTab==='links'?'active':''?>" data-tab="links">Custom Links</div>
    <div class="tab <?=$activeTab==='embeds'?'active':''?>" data-tab="embeds">Embeds</div>
    <div class="tab" data-tab="preview"><a href="/<?=htmlspecialchars($username)?>" target="_blank" style="color:inherit">Preview ↗</a></div>
  </div>

  <!-- Profile -->
  <div class="panel <?=$activeTab==='profile'?'active':''?>" id="panel-profile">
    <div class="card">
      <form action="/dashboard/save_profile.php" method="post" enctype="multipart/form-data">
        <div class="grid2">
          <div>
            <label>Display name</label>
            <input name="display_name" value="<?=htmlspecialchars($profile['display_name']??'')?>" required>
            <label>Bio</label>
            <textarea name="bio" rows="5" placeholder="Tell people about you..."><?=htmlspecialchars($profile['bio']??'')?></textarea>
            <label>Theme</label>
            <select name="theme">
              <option value="dark" <?=($profile['theme']??'')==='dark'?'selected':''?>>Dark</option>
              <option value="light" <?=($profile['theme']??'')==='light'?'selected':''?>>Light</option>
            </select>
            <label>Font</label>
            <select name="font_family">
              <option <?=($profile['font_family']??'')==='system-ui'?'selected':''?> value="system-ui">System</option>
              <option <?=($profile['font_family']??'')==='Inter'?'selected':''?> value="Inter">Inter</option>
              <option <?=($profile['font_family']??'')==='Poppins'?'selected':''?> value="Poppins">Poppins</option>
            </select>
          </div>
          <div>
            <label>Avatar</label>
            <input type="file" name="avatar" accept="image/*">
            <?php if (!empty($profile['avatar'])): ?>
              <div class="avatar-preview">
                <img src="<?=htmlspecialchars($profile['avatar'])?>" alt="Avatar">
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div style="margin-top: 24px;">
          <button class="btn" type="submit">Save Profile</button>
        </div>
      </form>
      
      <!-- Profile URL Section -->
      <div class="profile-url-box">
        <label>Your Profile URL</label>
        <div class="url-display">
          <div class="url-text" id="profileUrl"><?=htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'rabetin.bio').'/' .$username?></div>
          <button class="copy-btn" id="copyUrlBtn" onclick="copyProfileUrl()">
            <span id="copyBtnText">📋 Copy</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Design -->
  <div class="panel <?=$activeTab==='design'?'active':''?>" id="panel-design">
    <div class="card">
      <h3>🎨 Design Your Page</h3>
      <p style="color: #64748b; margin-bottom: 24px;">Choose a template or customize every detail of your profile page</p>
      
      <form action="/dashboard/save_design.php" method="post">
        <!-- Template Presets -->
        <div style="margin-bottom: 32px;">
          <label style="margin-top: 0;">Choose a Template</label>
          <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-top: 12px;">
            <?php foreach ($templates as $tpl): ?>
              <div class="template-card" data-template="<?=htmlspecialchars($tpl['slug'])?>" 
                   onclick="selectTemplate('<?=htmlspecialchars($tpl['slug'])?>')"
                   style="cursor: pointer; padding: 16px; border-radius: 12px; border: 3px solid <?=($profile['template_preset']??'default')===$tpl['slug']?'#667eea':'#e2e8f0'?>; background: <?=htmlspecialchars($tpl['bg_color']??'#ffffff')?>; transition: all 0.2s ease; position: relative;">
                <div style="text-align: center;">
                  <div style="font-weight: 600; color: <?=htmlspecialchars($tpl['text_color']??'#1e293b')?>; margin-bottom: 4px;">
                    <?=htmlspecialchars($tpl['name'])?>
                  </div>
                  <div style="font-size: 12px; color: <?=htmlspecialchars($tpl['text_color']??'#64748b')?>; opacity: 0.7; margin-bottom: 12px;">
                    <?=htmlspecialchars($tpl['description'])?>
                  </div>
                  <div style="height: 60px; display: flex; flex-direction: column; gap: 6px; align-items: center; justify-content: center;">
                    <div style="width: 80%; height: 10px; background: <?=htmlspecialchars($tpl['button_color'])?>; border-radius: <?=$tpl['button_style']==='rounded'?'8px':($tpl['button_style']==='pill'?'999px':'4px')?>; box-shadow: <?=$tpl['button_shadow']?'0 4px 12px rgba(0,0,0,0.15)':'none'?>;"></div>
                    <div style="width: 80%; height: 10px; background: <?=htmlspecialchars($tpl['button_color'])?>; border-radius: <?=$tpl['button_style']==='rounded'?'8px':($tpl['button_style']==='pill'?'999px':'4px')?>; box-shadow: <?=$tpl['button_shadow']?'0 4px 12px rgba(0,0,0,0.15)':'none'?>;"></div>
                  </div>
                  <?php if(($profile['template_preset']??'default')===$tpl['slug']): ?>
                    <div style="position: absolute; top: 8px; right: 8px; background: #10b981; color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700;">✓</div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <input type="hidden" name="template_preset" id="templatePreset" value="<?=htmlspecialchars($profile['template_preset']??'default')?>">
        </div>

        <hr style="border: none; border-top: 2px solid #e2e8f0; margin: 32px 0;">

        <!-- Custom Design Settings -->
        <h3 style="margin-bottom: 16px;">Custom Design Options</h3>
        <p style="color: #64748b; font-size: 14px; margin-bottom: 20px;">Or customize your design manually (will override template)</p>
        
        <div class="grid2">
          <div>
            <label>Button Style</label>
            <select name="button_style">
              <option value="rounded" <?=($profile['button_style']??'rounded')==='rounded'?'selected':''?>>Rounded (Default)</option>
              <option value="sharp" <?=($profile['button_style']??'')==='sharp'?'selected':''?>>Sharp Corners</option>
              <option value="pill" <?=($profile['button_style']??'')==='pill'?'selected':''?>>Pill Shape</option>
            </select>
          </div>

          <div>
            <label>Link Layout</label>
            <select name="link_layout">
              <option value="standard" <?=($profile['link_layout']??'standard')==='standard'?'selected':''?>>Standard (Default)</option>
              <option value="full" <?=($profile['link_layout']??'')==='full'?'selected':''?>>Full Width</option>
              <option value="compact" <?=($profile['link_layout']??'')==='compact'?'selected':''?>>Compact</option>
            </select>
          </div>
        </div>

        <div class="grid3" style="margin-top: 16px;">
          <div>
            <label>Button Color</label>
            <input type="color" name="button_color" value="<?=htmlspecialchars($profile['button_color']??'#667eea')?>">
          </div>

          <div>
            <label>Button Text Color</label>
            <input type="color" name="button_text_color" value="<?=htmlspecialchars($profile['button_text_color']??'#ffffff')?>">
          </div>

          <div>
            <label>Text Color (Optional)</label>
            <input type="color" name="text_color" value="<?=htmlspecialchars($profile['text_color']??'#1e293b')?>">
          </div>
        </div>

        <div class="grid2" style="margin-top: 16px;">
          <div>
            <label>Card Style</label>
            <select name="card_style">
              <option value="glass" <?=($profile['card_style']??'glass')==='glass'?'selected':''?>>Glassmorphism</option>
              <option value="solid" <?=($profile['card_style']??'')==='solid'?'selected':''?>>Solid</option>
              <option value="flat" <?=($profile['card_style']??'')==='flat'?'selected':''?>>Flat</option>
              <option value="card" <?=($profile['card_style']??'')==='card'?'selected':''?>>Card</option>
              <option value="neon" <?=($profile['card_style']??'')==='neon'?'selected':''?>>Neon</option>
            </select>
          </div>

          <div style="display: flex; align-items: center; padding-top: 20px;">
            <label style="display: flex; align-items: center; gap: 8px; margin: 0; cursor: pointer;">
              <input type="checkbox" name="button_shadow" value="1" <?=($profile['button_shadow']??1)?'checked':''?>>
              <span>Button Shadow</span>
            </label>
          </div>
        </div>

        <div style="margin-top: 24px;">
          <button class="btn" type="submit">Save Design</button>
        </div>
        
        <small style="display: block; margin-top: 12px;">
          💡 Tip: Select a template for instant styling, or customize individual settings below for full control
        </small>
      </form>
    </div>
  </div>

  <!-- Background -->
  <div class="panel <?=$activeTab==='background'?'active':''?>" id="panel-background">
    <div class="card">
      <form action="/dashboard/save_background.php" method="post" enctype="multipart/form-data">
        <div class="grid3">
          <div>
            <label>Background color</label>
            <input type="color" name="bg_color" value="<?=htmlspecialchars($profile['bg_color']??'#0f172a')?>">
          </div>
          <div>
            <label>Gradient (start)</label>
            <input type="color" name="gradient_start" value="<?=htmlspecialchars($profile['gradient_start']??'')?>">
          </div>
          <div>
            <label>Gradient (end)</label>
            <input type="color" name="gradient_end" value="<?=htmlspecialchars($profile['gradient_end']??'')?>">
          </div>
        </div>
        <label>Background image</label>
        <input type="file" name="bg_image" accept="image/*">
        <?php if (!empty($profile['bg_image'])): ?>
          <div class="bg-preview">
            <img src="<?=htmlspecialchars($profile['bg_image'])?>" alt="Background">
          </div>
        <?php endif; ?>
        <div style="margin-top: 24px;">
          <button class="btn" type="submit">Save Background</button>
        </div>
        <small>Tip: set gradient OR image; image wins if both are set.</small>
      </form>
    </div>
  </div>

  <!-- Social Icons -->
  <div class="panel <?=$activeTab==='socials'?'active':''?>" id="panel-socials">
    <div class="card">
      <form action="/dashboard/save_socials.php" method="post">
        <div class="list" id="socialList">
          <?php foreach ($platforms as $i=>$p):
            $row = $socialMap[$p] ?? ['handle'=>'','url'=>'','is_active'=>0,'position'=>$i];
          ?>
          <div class="item" data-platform="<?=$p?>">
            <div class="row">
              <span class="handle">☰</span>
              <strong style="text-transform:capitalize"><?=$p?></strong>
            </div>
            <div class="row">
              <input name="handle[<?=$p?>]" placeholder="handle or @username" value="<?=htmlspecialchars($row['handle'])?>">
              <input name="url[<?=$p?>]" placeholder="https://..." value="<?=htmlspecialchars($row['url'])?>">
              <label><input type="checkbox" name="active[<?=$p?>]" value="1" <?=$row['is_active']? 'checked':''?>> Active</label>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <input type="hidden" name="order" id="socialOrder">
        <div style="margin-top: 24px;">
          <button class="btn" type="submit">Save Socials</button>
        </div>
        <small>Drag the ☰ handle to reorder your icons.</small>
      </form>
    </div>
  </div>

  <!-- Custom Links -->
  <div class="panel <?=$activeTab==='links'?'active':''?>" id="panel-links">
    <div class="card">
      <form method="post" action="/dashboard/add_link.php" class="row">
        <input name="title" placeholder="Title (e.g., Portfolio)" required>
        <input name="url" placeholder="https://..." required>
        <button class="btn" type="submit">Add</button>
      </form>
    </div>
    <div class="card">
      <h3>Your Links</h3>
      <div class="list" id="linksList">
        <?php foreach ($links as $l): ?>
          <div class="item" data-id="<?=$l['id']?>">
            <div class="row" style="justify-content: space-between; width: 100%;">
              <div class="row" style="gap: 12px;">
                <span class="handle">☰</span>
                <div>
                  <div style="font-weight: 600; margin-bottom: 4px;"><?=htmlspecialchars($l['title'])?></div>
                  <a href="<?=htmlspecialchars($l['url'])?>" target="_blank" style="font-size: 13px; color: #64748b; text-decoration: none; display: block; max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?=htmlspecialchars($l['url'])?></a>
                  <?php if (isset($l['click_count']) && $l['click_count'] > 0): ?>
                    <small style="color: #10b981; margin-top: 4px; font-size: 12px;">📊 <?=$l['click_count']?> <?=$l['click_count']==1?'click':'clicks'?></small>
                  <?php endif; ?>
                </div>
              </div>
              <div class="row" style="gap: 8px;">
                <form method="post" action="/dashboard/toggle_link.php" style="margin: 0;">
                  <input type="hidden" name="id" value="<?=$l['id']?>">
                  <input type="hidden" name="state" value="<?=$l['is_active']?0:1?>">
                  <button class="btn btn-secondary" type="submit"><?=$l['is_active']?'Hide':'Show'?></button>
                </form>
                <form method="post" action="/dashboard/delete_link.php" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this link?');">
                  <input type="hidden" name="id" value="<?=$l['id']?>">
                  <button class="btn-delete" type="submit">Delete</button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <input type="hidden" id="linksOrder">
      <div style="margin-top: 24px;">
        <button class="btn" id="saveLinksOrder">Save Order</button>
      </div>
    </div>
  </div>

  <!-- Embeds -->
  <div class="panel <?=$activeTab==='embeds'?'active':''?>" id="panel-embeds">
    <div class="card">
      <form action="/dashboard/save_embeds.php" method="post">
        <div class="grid3">
          <div>
            <label>Instagram embed URL or HTML</label>
            <textarea name="instagram" rows="4" placeholder="Paste Instagram embed code or a profile widget URL"><?=$embeds ? htmlspecialchars(implode("\n", array_map(fn($e)=>$e['type']==='instagram'?$e['embed_url']?:$e['embed_html']:'', $embeds))) : ''?></textarea>
          </div>
          <div>
            <label>TikTok embed URL or HTML</label>
            <textarea name="tiktok" rows="4" placeholder="Paste TikTok embed code or a profile widget URL"><?=$embeds ? htmlspecialchars(implode("\n", array_map(fn($e)=>$e['type']==='tiktok'?$e['embed_url']?:$e['embed_html']:'', $embeds))) : ''?></textarea>
          </div>
          <div>
            <label>Custom embed (iframe/HTML)</label>
            <textarea name="custom" rows="4" placeholder="<iframe ...>"></textarea>
          </div>
        </div>
        <div style="margin-top: 24px;">
          <button class="btn" type="submit">Save Embeds</button>
        </div>
        <small>Note: official “full feed” embeds often require third-party widgets. We support pasting any safe embed HTML.</small>
      </form>
    </div>
  </div>
</div>

<script>
  // Toast Notification System
  function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    container.appendChild(toast);
    
    setTimeout(() => {
      toast.style.animation = 'toastSlideIn 0.3s ease-out reverse';
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  }
  
  // Show messages from PHP
  <?php if ($success_msg): ?>
    showToast(<?=json_encode($success_msg)?>, 'success');
  <?php endif; ?>
  <?php if ($error_msg): ?>
    showToast(<?=json_encode($error_msg)?>, 'error');
  <?php endif; ?>
  
  // Copy Profile URL
  function copyProfileUrl() {
    const url = document.getElementById('profileUrl').textContent;
    const protocol = window.location.protocol + '//';
    const fullUrl = protocol + url;
    
    navigator.clipboard.writeText(fullUrl).then(() => {
      const btn = document.getElementById('copyUrlBtn');
      const btnText = document.getElementById('copyBtnText');
      btn.classList.add('copied');
      btnText.textContent = '✓ Copied!';
      
      setTimeout(() => {
        btn.classList.remove('copied');
        btnText.textContent = '📋 Copy';
      }, 2000);
      
      showToast('Profile URL copied to clipboard!', 'success');
    }).catch(() => {
      showToast('Failed to copy URL', 'error');
    });
  }
  
  // Form Loading States
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
      const submitBtn = this.querySelector('button[type="submit"]');
      if (submitBtn && !submitBtn.classList.contains('btn-loading')) {
        submitBtn.classList.add('btn-loading');
        submitBtn.disabled = true;
      }
    });
  });
  
  // Template Selection
  function selectTemplate(slug) {
    document.getElementById('templatePreset').value = slug;
    document.querySelectorAll('.template-card').forEach(card => {
      if (card.dataset.template === slug) {
        card.style.borderColor = '#667eea';
      } else {
        card.style.borderColor = '#e2e8f0';
      }
    });
  }
  
  // Tabs with URL updates
  document.querySelectorAll('.tab').forEach(t=>{
    t.addEventListener('click', ()=>{
      const tabName = t.dataset.tab;
      if (tabName === 'preview') return; // Skip preview tab
      
      document.querySelectorAll('.tab').forEach(x=>x.classList.remove('active'));
      document.querySelectorAll('.panel').forEach(p=>p.classList.remove('active'));
      t.classList.add('active');
      const id = 'panel-'+tabName;
      const p = document.getElementById(id);
      if (p) p.classList.add('active');
      
      // Update URL without reload
      const url = new URL(window.location);
      url.searchParams.set('tab', tabName);
      window.history.pushState({}, '', url);
    });
  });

  // Sortable socials
  const socialList = document.getElementById('socialList');
  if (socialList) {
    new Sortable(socialList, {
      handle: '.handle',
      animation: 150,
      onSort: ()=>{
        const order = Array.from(socialList.children).map(x=>x.dataset.platform);
        document.getElementById('socialOrder').value = order.join(',');
      }
    });
  }

  // Sortable links
  const linksList = document.getElementById('linksList');
  if (linksList) {
    new Sortable(linksList, {
      handle: '.handle',
      animation: 150,
    });
  }
  document.getElementById('saveLinksOrder')?.addEventListener('click', async ()=>{
    const btn = document.getElementById('saveLinksOrder');
    btn.classList.add('btn-loading');
    btn.disabled = true;
    
    try {
      const order = Array.from(linksList.children).map(x=>x.dataset.id);
      const res = await fetch('/dashboard/reorder_links.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({order})
      });
      
      const data = await res.json();
      if (data.success) {
        showToast(data.message, 'success');
      } else {
        showToast(data.message, 'error');
      }
    } catch (error) {
      showToast('Failed to save order', 'error');
    } finally {
      btn.classList.remove('btn-loading');
      btn.disabled = false;
    }
  });

  // Tour Feature
  function initTour() {
    const driver = window.driver.js.driver;
    
    const driverObj = driver({
      showProgress: true,
      steps: [
        {
          element: 'header',
          popover: {
            title: 'Welcome to Rabetin.bio! 🎉',
            description: 'This is your personal bio link dashboard. Let\'s take a quick tour to show you around!',
            side: "bottom",
            align: 'start'
          }
        },
        {
          element: '.tabs',
          popover: {
            title: 'Navigation Tabs',
            description: 'Use these tabs to navigate between different sections of your dashboard. Each tab controls a different aspect of your bio page.',
            side: "bottom",
            align: 'start'
          }
        },
        {
          element: '[data-tab="profile"]',
          popover: {
            title: '👤 Profile Section',
            description: 'Customize your display name, bio, avatar, theme (dark/light), and font. This is what visitors see first!',
            side: "bottom",
            align: 'start'
          }
        },
        {
          element: '#panel-profile',
          popover: {
            title: 'Profile Settings',
            description: 'Here you can set your display name, write a bio, upload an avatar, choose between dark/light themes, and select your preferred font.',
            side: "top",
            align: 'start'
          }
        },
        {
          element: '[data-tab="background"]',
          popover: {
            title: '🎨 Background Customization',
            description: 'Make your page unique! Choose a solid color, gradient, or upload a custom background image.',
            side: "bottom",
            align: 'start',
            onNextClick: () => {
              document.querySelector('[data-tab="background"]').click();
              driverObj.moveNext();
            }
          }
        },
        {
          element: '#panel-background',
          popover: {
            title: 'Background Options',
            description: 'Pick a background color, create a beautiful gradient, or upload an image. Pro tip: background images override gradients!',
            side: "top",
            align: 'start'
          }
        },
        {
          element: '[data-tab="socials"]',
          popover: {
            title: '🔗 Social Media Icons',
            description: 'Connect all your social media accounts. Add icons for Instagram, Facebook, X (Twitter), TikTok, and more!',
            side: "bottom",
            align: 'start',
            onNextClick: () => {
              document.querySelector('[data-tab="socials"]').click();
              driverObj.moveNext();
            }
          }
        },
        {
          element: '#panel-socials',
          popover: {
            title: 'Social Accounts',
            description: 'Enter your handles and URLs for each platform. Drag the ☰ handle to reorder them. Don\'t forget to check "Active" to display them!',
            side: "top",
            align: 'start'
          }
        },
        {
          element: '[data-tab="links"]',
          popover: {
            title: '🔗 Custom Links',
            description: 'Add custom links to your portfolio, store, blog, or any URL you want to share with your audience.',
            side: "bottom",
            align: 'start',
            onNextClick: () => {
              document.querySelector('[data-tab="links"]').click();
              driverObj.moveNext();
            }
          }
        },
        {
          element: '#panel-links',
          popover: {
            title: 'Manage Your Links',
            description: 'Add new links with titles and URLs. Drag to reorder them, enable/disable individual links, and save your changes.',
            side: "top",
            align: 'start'
          }
        },
        {
          element: '[data-tab="embeds"]',
          popover: {
            title: '📺 Embeds',
            description: 'Show off your latest content! Embed Instagram posts, TikTok videos, or custom widgets directly on your page.',
            side: "bottom",
            align: 'start',
            onNextClick: () => {
              document.querySelector('[data-tab="embeds"]').click();
              driverObj.moveNext();
            }
          }
        },
        {
          element: '#panel-embeds',
          popover: {
            title: 'Add Embeds',
            description: 'Paste embed codes or URLs from Instagram, TikTok, or any custom iframe. Your content will display beautifully on your page!',
            side: "top",
            align: 'start'
          }
        },
        {
          element: '[data-tab="preview"]',
          popover: {
            title: '👁️ Preview Your Page',
            description: 'Click here anytime to see how your bio page looks to visitors. It opens in a new tab!',
            side: "bottom",
            align: 'start',
            onNextClick: () => {
              document.querySelector('[data-tab="profile"]').click();
              driverObj.moveNext();
            }
          }
        },
        {
          element: '#startTour',
          popover: {
            title: 'All Set! 🎊',
            description: 'You can restart this tour anytime by clicking this button. Now go create an amazing bio page!',
            side: "bottom",
            align: 'start'
          }
        }
      ],
      onDestroyStarted: () => {
        // Return to profile tab when tour ends
        document.querySelector('[data-tab="profile"]').click();
        driverObj.destroy();
      },
    });

    return driverObj;
  }

  // Initialize tour button
  document.getElementById('startTour')?.addEventListener('click', () => {
    const tour = initTour();
    tour.drive();
  });

  // Check if this is first visit (optional - auto-start tour)
  const hasSeenTour = localStorage.getItem('rabetin_tour_completed');
  if (!hasSeenTour) {
    // Auto-start tour on first visit
    setTimeout(() => {
      const tour = initTour();
      tour.drive();
      localStorage.setItem('rabetin_tour_completed', 'true');
    }, 800);
  }
</script>
</body>
</html>
