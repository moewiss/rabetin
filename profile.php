<?php
require __DIR__.'/includes/db.php';

$username = $_GET['username'] ?? '';
if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) { http_response_code(404); echo "Not found"; exit; }

$stmt = $pdo->prepare('
  SELECT u.id AS user_id, u.username, p.display_name, p.bio, p.avatar, p.theme, p.bg_color, p.bg_image, p.gradient_start, p.gradient_end, p.font_family,
         p.button_style, p.button_color, p.button_text_color, p.button_shadow, p.link_layout, p.card_style, p.text_color,
         p.button_radius, p.button_border_color, p.button_border_width, p.button_border_style, p.button_hover_effect
  FROM users u JOIN profiles p ON p.user_id = u.id
  WHERE u.username = ?
');
$stmt->execute([$username]);
$profile = $stmt->fetch();
if (!$profile) { http_response_code(404); echo "Profile not found"; exit; }

$links = $pdo->prepare('
  SELECT id, title, url FROM links
  WHERE user_id=? AND is_active=1
    AND (starts_at IS NULL OR starts_at<=NOW())
    AND (ends_at IS NULL OR ends_at>=NOW())
  ORDER BY position, id
');
$links->execute([$profile['user_id']]);
$links = $links->fetchAll();

$socials = $pdo->prepare('SELECT platform, handle, url FROM social_accounts WHERE user_id=? AND is_active=1 ORDER BY position, platform');
$socials->execute([$profile['user_id']]);
$socials = $socials->fetchAll();

$embeds = $pdo->prepare('SELECT type, title, embed_url, embed_html FROM embeds WHERE user_id=? AND is_active=1 ORDER BY position, id');
$embeds->execute([$profile['user_id']]);
$embeds = $embeds->fetchAll();

$theme = $profile['theme'] ?: 'dark';
$bgCss = '';
if (!empty($profile['bg_image'])) {
  $bgCss = "background: url('".htmlspecialchars($profile['bg_image'],ENT_QUOTES)."') center/cover no-repeat fixed;";
} elseif (!empty($profile['gradient_start']) && !empty($profile['gradient_end'])) {
  $bgCss = "background: linear-gradient(135deg, {$profile['gradient_start']}, {$profile['gradient_end']});";
} else {
  $bgCss = "background: ".($theme==='dark' ? '#0f172a' : ($profile['bg_color'] ?: '#ffffff')).";";
}
$font = $profile['font_family'] ?: 'system-ui';

// Design customization settings
$button_style = $profile['button_style'] ?? 'rounded';
$button_color = $profile['button_color'] ?? '#667eea';
$button_text_color = $profile['button_text_color'] ?? '#ffffff';
$button_shadow = $profile['button_shadow'] ?? 1;
$button_hover_effect = $profile['button_hover_effect'] ?? 1;
$link_layout = $profile['link_layout'] ?? 'standard';
$card_style = $profile['card_style'] ?? 'glass';
$text_color_custom = $profile['text_color'] ?? null;

// Advanced button customization
$button_radius_custom = $profile['button_radius'] ?? 14;
$button_border_color = $profile['button_border_color'] ?? $button_color;
$button_border_width = $profile['button_border_width'] ?? 0;
$button_border_style = $profile['button_border_style'] ?? 'solid';

// Button border radius based on style (or custom value)
$button_border_radius = match($button_style) {
  'pill' => '999px',
  'sharp' => '4px',
  'custom' => $button_radius_custom . 'px',
  default => '14px'
};

// Card styling based on card_style
$card_bg = match($card_style) {
  'solid' => ($theme === 'dark' ? 'rgba(30, 41, 59, 0.9)' : 'rgba(255, 255, 255, 0.95)'),
  'flat' => ($theme === 'dark' ? 'rgba(15, 23, 42, 1)' : 'rgba(248, 250, 252, 1)'),
  'card' => ($theme === 'dark' ? 'rgba(17, 24, 39, 1)' : 'rgba(255, 255, 255, 1)'),
  'neon' => 'rgba(10, 10, 10, 0.85)',
  default => ($theme === 'dark' ? 'rgba(255, 255, 255, 0.08)' : 'rgba(255, 255, 255, 0.95)')
};

$card_border = match($card_style) {
  'neon' => '2px solid ' . $button_color,
  'flat' => 'none',
  'outlined' => '2px solid ' . $button_color,
  'glass' => '2px solid ' . ($theme === 'dark' ? 'rgba(255, 255, 255, 0.12)' : 'rgba(226, 232, 240, 0.8)'),
  default => '2px solid ' . ($theme === 'dark' ? 'rgba(255, 255, 255, 0.12)' : 'rgba(226, 232, 240, 0.8)')
};

// Link width based on layout
$link_max_width = match($link_layout) {
  'full' => '100%',
  'compact' => '400px',
  default => '560px'
};

// Simple SVG icon map (inline, no external deps)
function icon_svg($p){
  $icons = [
    'facebook'=>'M10 2h4a4 4 0 014 4v3h-3a2 2 0 00-2 2v3h5l-1 4h-4v6h-4v-6H6v-4h3v-3a6 6 0 016-6h1',
    'instagram'=>'M7 2h10a5 5 0 015 5v10a5 5 0 01-5 5H7a5 5 0 01-5-5V7a5 5 0 015-5zm8 3a1 1 0 100 2 1 1 0 000-2zm-5 3a6 6 0 100 12 6 6 0 000-12z',
    'x'=>'M3 3l8.5 9.5L3 21h3.5L15 12.5 21 21h3l-8.5-9.5L24 3h-3.5L15 10.5 9 3H3z',
    'tiktok'=>'M20 8.5a6.5 6.5 0 01-4-1.3v8.3A5.5 5.5 0 1110.5 10v3A2.5 2.5 0 1013 15.5V2h3a6.5 6.5 0 004 3',
    'youtube'=>'M10 7l8 5-8 5V7zm15-2a4 4 0 014 4v6a4 4 0 01-4 4H9a4 4 0 01-4-4V9a4 4 0 014-4h16z',
    'email'=>'M4 6h24v20H4V6zm24 2l-12 9L4 8',
    'linkedin'=>'M6 9h6v18H6V9zm3-7a3 3 0 110 6 3 3 0 010-6zM16 9h6v3a5 5 0 016-3c5 0 6 3 6 8v10h-6V19c0-3-1-5-4-5s-4 2-4 5v8h-6V9z',
    'threads'=>'M16 4c8 0 12 6 12 12s-4 12-12 12S4 24 4 16C4 8 8 4 16 4zm-2 9c1-3 6-3 6 1 0 3-3 4-6 4h-2v3h-3v-9h5z',
    'github'=>'M12 2a10 10 0 00-3 19c.5.1.7-.2.7-.5v-2c-3 .7-3.6-1.4-3.6-1.4-.5-1.2-1.1-1.5-1.1-1.5-.9-.6.1-.6.1-.6 1 .1 1.6 1 1.6 1 .9 1.6 2.6 1.1 3.2.8.1-.6.3-1.1.6-1.3-2.4-.3-5-1.2-5-5.3 0-1.2.4-2.1 1-2.9 0-.3-.4-1.3.1-2.7 0 0 .9-.3 3 1a10 10 0 015.5 0c2-1.3 3-1 3-1 .5 1.4.1 2.4.1 2.7.6.7 1 1.7 1 2.9 0 4.1-2.6 5-5 5.3.4.3.7.9.7 1.8v2.7c0 .3.2.6.7.5A10 10 0 0012 2z',
    'reddit'=>'M20 18a3 3 0 10-2.8-2h-6.4a3 3 0 10-2.8 2c0 3 4 5 8 5s8-2 8-5zm-12-3a2 2 0 110-4 2 2 0 010 4zm8 0a2 2 0 110-4 2 2 0 010 4zM19 8l3-3',
    'twitch'=>'M4 4h18l3 3v12l-5 5h-6l-4 4v-4H4V4zm16 4h-2v6h2V8zm-6 0h-2v6h2V8z',
    'discord'=>'M8 6c3-2 7-2 10 0 2 3 3 6 3 9-2-1-3-1-5-2-1 1-2 1-3 1s-2 0-3-1c-2 1-3 1-5 2 0-3 1-6 3-9z',
  ];
  $d = $icons[$p] ?? 'M2 2h28v28H2z';
  return '<svg width="22" height="22" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true"><path d="'.$d.'"/></svg>';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?=htmlspecialchars($profile['display_name'])?> — Rabetin.bio</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&family=Roboto:wght@400;500;700&family=Open+Sans:wght@400;600;700&family=Lato:wght@400;700&family=Montserrat:wght@400;600;700&family=Raleway:wght@400;600;700&family=Work+Sans:wght@400;600;700&family=Nunito:wght@400;600;700&family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@400;600;700&family=Merriweather:wght@400;700&family=Lora:wght@400;600&family=PT+Serif:wght@400;700&family=Bebas+Neue&family=Archivo+Black&family=Anton&family=JetBrains+Mono:wght@400;600&family=Fira+Code:wght@400;600&family=Space+Mono:wght@400;700&family=Caveat:wght@400;600&family=Pacifico&family=Dancing+Script:wght@400;600&family=Creepster&family=Mountains+of+Christmas:wght@400;700&family=Quicksand:wght@400;600&family=Orbitron:wght@400;600;800&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    :root {
      color-scheme: <?= $theme==='dark'?'dark':'light' ?>;
    }
    
    body {
      font-family: <?=htmlspecialchars($font === 'system-ui' ? "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" : "'".$font."', 'Inter', sans-serif")?>;
      <?=$bgCss?>;
      color: <?= $text_color_custom ?: ($theme==='dark'?'#e2e8f0':'#1e293b')?>;
      min-height: 100vh;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
      position: relative;
    }
    <?php if (!empty($profile['bg_image'])): ?>
    
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: <?= $theme==='dark'?'rgba(0, 0, 0, 0.45)':'rgba(255, 255, 255, 0.8)'?>;
      backdrop-filter: blur(8px) saturate(120%);
      z-index: 0;
    }
    <?php endif; ?>
    
    .container {
      position: relative;
      z-index: 1;
      max-width: 680px;
      margin: 0 auto;
      padding: 48px 24px 80px;
      text-align: center;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    
    .profile-header {
      margin-bottom: 32px;
      animation: fadeInUp 0.6s ease-out;
    }
    
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .avatar {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      border: 5px solid <?= $theme==='dark'?'rgba(255, 255, 255, 0.15)':'rgba(255, 255, 255, 0.9)'?>;
      box-shadow: 0 8px 32px <?= $theme==='dark'?'rgba(0, 0, 0, 0.3)':'rgba(0, 0, 0, 0.12)'?>;
      margin-bottom: 24px;
      transition: transform 0.3s ease;
    }
    
    .avatar:hover {
      transform: scale(1.05);
    }
    
    .name {
      font-weight: 800;
      font-size: 32px;
      margin-bottom: 12px;
      line-height: 1.2;
      letter-spacing: -0.5px;
      color: <?= $text_color_custom ?: ($theme==='dark'?'#ffffff':'#0f172a')?>;
    }
    
    .bio {
      font-size: 16px;
      line-height: 1.6;
      color: <?= $text_color_custom ?: ($theme==='dark'?'#cbd5e1':'#475569')?>;
      opacity: <?= $text_color_custom ? '0.8' : '1'?>;
      white-space: pre-wrap;
      max-width: 480px;
      margin: 0 auto;
    }
    
    .social-icons {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      justify-content: center;
      margin: 32px 0 40px;
      animation: fadeInUp 0.6s ease-out 0.1s backwards;
    }
    
    .social-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 52px;
      height: 52px;
      border-radius: 12px;
      background: <?= $theme==='dark'?'rgba(255, 255, 255, 0.08)':'rgba(255, 255, 255, 0.9)'?>;
      border: 2px solid <?= $theme==='dark'?'rgba(255, 255, 255, 0.12)':'rgba(226, 232, 240, 0.8)'?>;
      color: <?= $theme==='dark'?'#e2e8f0':'#475569'?>;
      text-decoration: none;
      transition: all 0.2s ease;
      backdrop-filter: blur(10px);
      box-shadow: 0 4px 12px <?= $theme==='dark'?'rgba(0, 0, 0, 0.2)':'rgba(0, 0, 0, 0.06)'?>;
    }
    
    .social-icon:hover {
      transform: translateY(-4px);
      background: <?= $theme==='dark'?'rgba(255, 255, 255, 0.15)':'rgba(255, 255, 255, 1)'?>;
      border-color: <?= $theme==='dark'?'rgba(255, 255, 255, 0.25)':'#cbd5e1'?>;
      box-shadow: 0 8px 24px <?= $theme==='dark'?'rgba(0, 0, 0, 0.3)':'rgba(0, 0, 0, 0.12)'?>;
    }
    
    .social-icon svg {
      width: 24px;
      height: 24px;
    }
    
    .links-container {
      width: 100%;
      max-width: <?=$link_max_width?>;
      display: flex;
      flex-direction: column;
      gap: <?=$link_layout==='compact'?'12px':'16px'?>;
    }
    
    .link {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: <?=$link_layout==='compact'?'14px 20px':'18px 24px'?>;
      border-radius: <?=$button_border_radius?>;
      background: <?=$button_color?>;
      border: <?=$card_border?>;
      color: <?=$button_text_color?>;
      font-size: <?=$link_layout==='compact'?'15px':'16px'?>;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.2s ease;
      backdrop-filter: <?=$card_style==='glass'?'blur(10px)':'none'?>;
      box-shadow: <?=$button_shadow?'0 4px 12px rgba(0, 0, 0, 0.2)':'none'?>;
      animation: fadeInUp 0.5s ease-out backwards;
      position: relative;
      overflow: hidden;
      <?php if ($card_style === 'neon'): ?>
        box-shadow: 0 0 20px <?=$button_color?>, 0 0 40px <?=$button_color?>40;
      <?php endif; ?>
    }
    
    .link::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s ease;
    }
    
    .link:hover::before {
      left: <?=$button_hover_effect?'100%':'-100%'?>;
    }
    
    .link:hover {
      transform: <?=$button_hover_effect?'translateY(-4px) scale(1.02)':'translateY(-1px)'?>;
      filter: brightness(1.1);
      box-shadow: <?=$button_shadow?'0 8px 24px rgba(0, 0, 0, 0.3)':'0 4px 16px rgba(0, 0, 0, 0.15)'?>;
      <?php if ($card_style === 'neon'): ?>
        box-shadow: 0 0 30px <?=$button_color?>, 0 0 60px <?=$button_color?>60;
      <?php endif; ?>
    }
    
    .link:active {
      transform: translateY(-2px);
    }
    
    <?php 
    // Generate staggered animation delays for links
    foreach ($links as $i => $l) {
      $delay = 0.2 + ($i * 0.05);
      echo ".link:nth-child(".($i+1).") { animation-delay: {$delay}s; }\n";
    }
    ?>
    
    .embeds {
      width: 100%;
      max-width: 560px;
      margin-top: 48px;
      display: grid;
      gap: 24px;
      animation: fadeInUp 0.6s ease-out 0.4s backwards;
    }
    
    .embed-card {
      padding: 16px;
      border-radius: 16px;
      background: <?= $theme==='dark'?'rgba(255, 255, 255, 0.08)':'rgba(255, 255, 255, 0.95)'?>;
      border: 2px solid <?= $theme==='dark'?'rgba(255, 255, 255, 0.12)':'rgba(226, 232, 240, 0.8)'?>;
      backdrop-filter: blur(10px);
      box-shadow: 0 4px 12px <?= $theme==='dark'?'rgba(0, 0, 0, 0.2)':'rgba(0, 0, 0, 0.06)'?>;
      overflow: hidden;
    }
    
    .embed-card iframe {
      width: 100%;
      min-height: 400px;
      border: 0;
      border-radius: 12px;
      background: <?= $theme==='dark'?'#0f172a':'#f8fafc'?>;
    }
    
    .footer {
      margin-top: 64px;
      padding-top: 32px;
      border-top: 1px solid <?= $theme==='dark'?'rgba(255, 255, 255, 0.1)':'rgba(226, 232, 240, 0.6)'?>;
      animation: fadeInUp 0.6s ease-out 0.5s backwards;
    }
    
    .footer-logo {
      font-size: 14px;
      font-weight: 600;
      color: <?= $theme==='dark'?'#94a3b8':'#64748b'?>;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: color 0.2s ease;
    }
    
    .footer-logo:hover {
      color: <?= $theme==='dark'?'#cbd5e1':'#475569'?>;
    }
    
    .logo-accent {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      font-weight: 700;
    }
    
    @media (max-width: 640px) {
      .container {
        padding: 32px 20px 64px;
      }
      
      .name {
        font-size: 28px;
      }
      
      .bio {
        font-size: 15px;
      }
      
      .link {
        font-size: 15px;
        padding: 16px 20px;
      }
      
      .avatar {
        width: 100px;
        height: 100px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="profile-header">
      <?php if (!empty($profile['avatar'])): ?>
        <img class="avatar" src="<?=htmlspecialchars($profile['avatar'])?>" alt="<?=htmlspecialchars($profile['display_name'])?>">
      <?php endif; ?>
      <h1 class="name"><?=htmlspecialchars($profile['display_name'])?></h1>
      <?php if (!empty($profile['bio'])): ?>
        <p class="bio"><?=htmlspecialchars($profile['bio'])?></p>
      <?php endif; ?>
    </div>

    <?php if ($socials): ?>
      <div class="social-icons">
        <?php foreach ($socials as $s):
          $url = $s['url'] ?: '#';
          $label = ucfirst($s['platform']);
        ?>
          <a class="social-icon" href="<?=htmlspecialchars($url)?>" target="_blank" rel="noopener noreferrer" title="<?=htmlspecialchars($label)?>" aria-label="<?=htmlspecialchars($label)?>">
            <?=icon_svg($s['platform'])?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($links): ?>
      <div class="links-container">
        <?php foreach ($links as $l): ?>
          <a class="link" href="/track_click.php?id=<?=$l['id']?>" target="_blank" rel="noopener noreferrer">
            <?=htmlspecialchars($l['title'])?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($embeds): ?>
      <div class="embeds">
        <?php foreach ($embeds as $e): ?>
          <div class="embed-card">
            <?php
            $html = '';
            if (!empty($e['embed_html'])) {
              $html = $e['embed_html'];
            } elseif (!empty($e['embed_url'])) {
              // very basic iframes for known providers
              if ($e['type']==='tiktok') {
                $src = htmlspecialchars($e['embed_url'], ENT_QUOTES);
                $html = "<iframe src=\"$src\" allowfullscreen loading=\"lazy\"></iframe>";
              } elseif ($e['type']==='instagram') {
                $src = htmlspecialchars($e['embed_url'], ENT_QUOTES);
                $html = "<iframe src=\"$src\" allowtransparency=\"true\" loading=\"lazy\"></iframe>";
              } else {
                $src = htmlspecialchars($e['embed_url'], ENT_QUOTES);
                $html = "<iframe src=\"$src\" loading=\"lazy\"></iframe>";
              }
            }
            echo $html;
            ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    
    <div class="footer">
      <a href="/" class="footer-logo">
        <span>Powered by <span class="logo-accent">Rabetin</span>.bio</span>
      </a>
    </div>
  </div>
</body>
</html>
