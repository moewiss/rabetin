<?php
require __DIR__.'/includes/db.php';

$username = $_GET['username'] ?? '';
if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) { http_response_code(404); echo "Not found"; exit; }

$stmt = $pdo->prepare('
  SELECT u.id AS user_id, u.username, p.display_name, p.bio, p.avatar, p.theme, p.bg_color, p.bg_image, p.gradient_start, p.gradient_end, p.font_family
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
<html>
<head>
  <meta charset="utf-8">
  <title><?=htmlspecialchars($profile['display_name'])?> — Rabetin.bio</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    :root { color-scheme: <?= $theme==='dark'?'dark':'light' ?>; }
    body{margin:0;font-family:<?=htmlspecialchars($font)?>; <?=$bgCss?>; color:<?= $theme==='dark'?'#e2e8f0':'#0b1220'?>}
    .overlay{backdrop-filter:saturate(1.1) <?=!empty($profile['bg_image'])?'brightness(0.95)':''?>}
    .wrap{max-width:560px;margin:0 auto;padding:28px;text-align:center;min-height:100vh}
    .avatar{width:120px;height:120px;border-radius:999px;object-fit:cover;border:3px solid rgba(255,255,255,.2)}
    .name{font-weight:800;font-size:28px;margin:16px 0 6px}
    .bio{opacity:.9;margin-bottom:18px;white-space:pre-wrap}
    .icons{display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin:12px 0 18px}
    .icon{display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;border:1px solid rgba(255,255,255,.18);border-radius:10px;text-decoration:none}
    .link{display:block;padding:14px 16px;margin:12px 0;border-radius:12px;text-decoration:none;border:1px solid rgba(0,0,0,.12);background:rgba(255,255,255,<?= $theme==='dark'?'0.04':'0.8'?>)}
    .link:hover{transform:translateY(-1px)}
    .embeds{margin-top:22px;display:grid;gap:16px}
    .embed-card{padding:10px;border-radius:12px;background:rgba(0,0,0,0.06);border:1px solid rgba(0,0,0,0.1)}
    iframe{width:100%;min-height:420px;border:0;border-radius:12px}
  </style>
</head>
<body>
  <div class="overlay">
  <div class="wrap">
    <?php if (!empty($profile['avatar'])): ?>
      <img class="avatar" src="<?=htmlspecialchars($profile['avatar'])?>" alt="">
    <?php endif; ?>
    <div class="name"><?=htmlspecialchars($profile['display_name'])?></div>
    <?php if (!empty($profile['bio'])): ?>
      <div class="bio"><?=htmlspecialchars($profile['bio'])?></div>
    <?php endif; ?>

    <?php if ($socials): ?>
      <div class="icons">
        <?php foreach ($socials as $s):
          $url = $s['url'] ?: '#';
          $label = ucfirst($s['platform']);
        ?>
          <a class="icon" href="<?=htmlspecialchars($url)?>" target="_blank" rel="noopener" title="<?=htmlspecialchars($label)?>">
            <?=icon_svg($s['platform'])?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php foreach ($links as $l): ?>
      <a class="link" href="<?=htmlspecialchars($l['url'])?>" target="_blank" rel="noopener">
        <?=htmlspecialchars($l['title'])?>
      </a>
    <?php endforeach; ?>

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
  </div>
  </div>
</body>
</html>
