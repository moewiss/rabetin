<?php
require __DIR__.'/../includes/db.php';
require __DIR__.'/../includes/auth.php';
require_login();
require_wizard_completed();

$user_id = current_user_id();
$wizard_completed_msg = isset($_GET['wizard_completed']) ? true : false;

// Fetch data
$profile = $pdo->prepare('SELECT * FROM profiles WHERE user_id=?'); $profile->execute([$user_id]); $profile=$profile->fetch();
$links = $pdo->prepare('SELECT * FROM links WHERE user_id=? ORDER BY position ASC, id ASC'); $links->execute([$user_id]); $links=$links->fetchAll();
$socials = $pdo->prepare('SELECT * FROM social_accounts WHERE user_id=? ORDER BY position ASC, platform ASC'); $socials->execute([$user_id]); $socials=$socials->fetchAll();
$embeds = $pdo->prepare('SELECT * FROM embeds WHERE user_id=? AND is_active=1 ORDER BY position ASC, id ASC'); $embeds->execute([$user_id]); $embeds=$embeds->fetchAll();

$platforms = ['facebook','instagram','x','tiktok','youtube','email','linkedin','threads','github','reddit','twitch','discord'];
$socialMap = [];
foreach ($socials as $s) { $socialMap[$s['platform']] = $s; }
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dashboard — Rabetin.bio</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    body{font-family:system-ui;background:#0f172a;color:#e2e8f0;margin:0}
    header{display:flex;justify-content:space-between;align-items:center;padding:16px 22px;border-bottom:1px solid #253046;background:#111827}
    .wrap{max-width:1100px;margin:0 auto;padding:22px}
    .tabs{display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap}
    .tab{padding:10px 14px;border:1px solid #253046;border-radius:10px;background:#0b1220;cursor:pointer}
    .tab.active{background:#111827}
    .panel{display:none}
    .panel.active{display:block}
    .card{padding:16px;border:1px solid #253046;border-radius:14px;background:#0b1220;margin-bottom:16px}
    input,textarea,select,button{padding:10px;border-radius:10px;border:1px solid #334155;background:#0b1220;color:#e2e8f0}
    input[type=file]{border:none}
    label{display:block;margin-top:10px;margin-bottom:6px;opacity:.9}
    .grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .grid3{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
    .row{display:flex;gap:10px;align-items:center}
    .btn{cursor:pointer}
    .list{display:flex;flex-direction:column;gap:8px;margin-top:8px}
    .item{display:flex;justify-content:space-between;align-items:center;padding:10px;border:1px solid #253046;border-radius:10px;background:#0f172a}
    .item .handle{cursor:grab;margin-right:8px}
    a{color:#93c5fd;text-decoration:none}
    small{opacity:.7}
    .driver-popover{background:#111827!important;color:#e2e8f0!important;border:1px solid #253046!important}
    .driver-popover-title{color:#e2e8f0!important;font-weight:bold}
    .driver-popover-description{color:#cbd5e1!important}
    .driver-popover-next-btn{background:#1e40af!important;color:white!important;border:none!important}
    .driver-popover-prev-btn{background:#374151!important;color:white!important;border:none!important}
    .driver-popover-close-btn{color:#e2e8f0!important}
  </style>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css"/>
  <script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>
</head>
<body>
<header>
  <strong>Rabetin.bio — Dashboard</strong>
  <nav>
    <button id="startTour" class="btn" style="margin-right:16px;padding:8px 16px;background:#1e40af;border:none;border-radius:8px;cursor:pointer">📖 Take a Tour</button>
    <a href="/logout.php">Logout</a>
  </nav>
</header>
<div class="wrap">
  <?php if ($wizard_completed_msg): ?>
    <div style="padding:16px;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);border-radius:14px;margin-bottom:20px;text-align:center">
      <h3 style="margin:0 0 8px 0">🎉 Welcome to Rabetin.bio!</h3>
      <p style="margin:0;opacity:0.95">Your profile is set up! Explore all the features below to customize your page.</p>
    </div>
  <?php endif; ?>
  <div class="tabs">
    <div class="tab active" data-tab="profile">Profile</div>
    <div class="tab" data-tab="background">Background</div>
    <div class="tab" data-tab="socials">Social Icons</div>
    <div class="tab" data-tab="links">Custom Links</div>
    <div class="tab" data-tab="embeds">Embeds</div>
    <div class="tab" data-tab="preview"><a href="/<?=htmlspecialchars($pdo->query("SELECT username FROM users WHERE id=$user_id")->fetchColumn())?>" target="_blank" style="color:inherit">Preview ↗</a></div>
  </div>

  <!-- Profile -->
  <div class="panel active" id="panel-profile">
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
              <div style="margin-top:8px"><img src="<?=htmlspecialchars($profile['avatar'])?>" alt="" style="width:96px;height:96px;border-radius:999px;object-fit:cover"></div>
            <?php endif; ?>
          </div>
        </div>
        <div style="margin-top:12px"><button class="btn" type="submit">Save Profile</button></div>
      </form>
    </div>
  </div>

  <!-- Background -->
  <div class="panel" id="panel-background">
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
        <label style="margin-top:12px">Background image</label>
        <input type="file" name="bg_image" accept="image/*">
        <?php if (!empty($profile['bg_image'])): ?>
          <div style="margin-top:8px"><img src="<?=htmlspecialchars($profile['bg_image'])?>" alt="" style="width:220px;border-radius:12px"></div>
        <?php endif; ?>
        <div style="margin-top:12px"><button class="btn" type="submit">Save Background</button></div>
        <small>Tip: set gradient OR image; image wins if both are set.</small>
      </form>
    </div>
  </div>

  <!-- Social Icons -->
  <div class="panel" id="panel-socials">
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
        <div style="margin-top:12px"><button class="btn" type="submit">Save Socials</button></div>
        <small>Drag the ☰ handle to reorder your icons.</small>
      </form>
    </div>
  </div>

  <!-- Custom Links -->
  <div class="panel" id="panel-links">
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
            <div class="row">
              <span class="handle">☰</span>
              <div><?=htmlspecialchars($l['title'])?></div>
            </div>
            <div class="row">
              <a href="<?=htmlspecialchars($l['url'])?>" target="_blank"><?=htmlspecialchars($l['url'])?></a>
              <form method="post" action="/dashboard/toggle_link.php" style="margin-left:10px">
                <input type="hidden" name="id" value="<?=$l['id']?>">
                <input type="hidden" name="state" value="<?=$l['is_active']?0:1?>">
                <button class="btn" type="submit"><?=$l['is_active']?'Disable':'Enable'?></button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <input type="hidden" id="linksOrder">
      <div style="margin-top:12px">
        <button class="btn" id="saveLinksOrder">Save Order</button>
      </div>
    </div>
  </div>

  <!-- Embeds -->
  <div class="panel" id="panel-embeds">
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
        <div style="margin-top:12px"><button class="btn" type="submit">Save Embeds</button></div>
        <small>Note: official “full feed” embeds often require third-party widgets. We support pasting any safe embed HTML.</small>
      </form>
    </div>
  </div>
</div>

<script>
  // Tabs
  document.querySelectorAll('.tab').forEach(t=>{
    t.addEventListener('click', ()=>{
      document.querySelectorAll('.tab').forEach(x=>x.classList.remove('active'));
      document.querySelectorAll('.panel').forEach(p=>p.classList.remove('active'));
      t.classList.add('active');
      const id = 'panel-'+t.dataset.tab;
      const p = document.getElementById(id);
      if (p) p.classList.add('active');
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
    const order = Array.from(linksList.children).map(x=>x.dataset.id);
    const res = await fetch('/dashboard/reorder_links.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({order})
    });
    alert(await res.text());
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
