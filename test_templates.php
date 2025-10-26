<?php
// Quick test script to check if templates table exists
require __DIR__.'/includes/db.php';

echo "<h2>Testing Templates Table</h2>";

try {
  $templates = $pdo->query('SELECT * FROM design_templates ORDER BY category, id ASC')->fetchAll();
  
  if (empty($templates)) {
    echo "<p style='color: orange;'>⚠️ Table exists but is empty. Run migration:</p>";
    echo "<pre>mysql -u rabetin -pMoody-006M rabetin < design_system_migration.sql</pre>";
  } else {
    echo "<p style='color: green;'>✅ Found " . count($templates) . " templates:</p>";
    echo "<ul>";
    foreach ($templates as $tpl) {
      echo "<li><strong>{$tpl['name']}</strong> ({$tpl['category']}) - {$tpl['slug']}</li>";
    }
    echo "</ul>";
  }
  
} catch (Exception $e) {
  echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
  echo "<p>The table probably doesn't exist. Run migration:</p>";
  echo "<pre>mysql -u rabetin -pMoody-006M rabetin < design_system_migration.sql</pre>";
}

echo "<hr>";
echo "<h2>Quick Fix Commands</h2>";
echo "<pre>";
echo "# In terminal/command prompt:\n";
echo "cd " . __DIR__ . "\n";
echo "mysql -u rabetin -pMoody-006M rabetin < design_system_migration.sql\n";
echo "</pre>";
?>

