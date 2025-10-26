<?php
// /var/www/rabetin/includes/db.php
$DB_HOST = 'localhost';
$DB_NAME = 'rabetin_bio';
$DB_USER = 'rabetin';
$DB_PASS = 'Moody-006M';

$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
  $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (Throwable $e) {
  http_response_code(500);
  exit('Database connection failed.');
}
