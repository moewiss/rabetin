<?php
require __DIR__.'/includes/db.php';

// Get link ID from request
$link_id = (int)($_GET['id'] ?? 0);

if ($link_id > 0) {
  try {
    // Get link details and increment click count
    $stmt = $pdo->prepare('SELECT id, url, user_id FROM links WHERE id = ? AND is_active = 1');
    $stmt->execute([$link_id]);
    $link = $stmt->fetch();
    
    if ($link) {
      // Record click in analytics (if table exists)
      try {
        $pdo->prepare('INSERT INTO link_clicks (link_id, user_id, clicked_at, ip_address, user_agent) VALUES (?, ?, NOW(), ?, ?)')
            ->execute([$link_id, $link['user_id'], $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null]);
      } catch (Exception $e) {
        // Table might not exist yet, that's okay
        error_log('Analytics tracking error: ' . $e->getMessage());
      }
      
      // Increment simple counter
      $pdo->prepare('UPDATE links SET click_count = COALESCE(click_count, 0) + 1 WHERE id = ?')->execute([$link_id]);
      
      // Redirect to the actual URL
      header('Location: ' . $link['url'], true, 302);
      exit;
    }
  } catch (Exception $e) {
    error_log('Click tracking error: ' . $e->getMessage());
  }
}

// If we get here, something went wrong - redirect to home
header('Location: /', true, 302);
exit;

