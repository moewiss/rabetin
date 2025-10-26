<?php
// Simple router: if URL is a single segment like /mohammad, serve profile.php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$seg = trim($uri, '/');

// If it looks like a username (3–20 chars), forward to profile.php
if ($seg !== '' && preg_match('/^[a-zA-Z0-9_]{3,20}$/', $seg)) {
    $_GET['username'] = $seg;
    require __DIR__ . '/profile.php';
    exit;
}

// Otherwise show the landing page (fallback to existing HTML if present)
$landingHtml = __DIR__ . '/index.html';
if (is_file($landingHtml)) {
    readfile($landingHtml);
} else {
    echo "OK Rabetin — landing page placeholder";
}
