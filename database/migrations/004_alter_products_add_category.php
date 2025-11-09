<?php
// Migration: Add category column to products table if it does not exist.
require_once __DIR__ . '/../db_connection.php';

try {
  $check = $pdo->query("SHOW COLUMNS FROM products LIKE 'category'");
  if ($check->rowCount() === 0) {
    $pdo->exec("ALTER TABLE products ADD COLUMN category ENUM('consumables','non_consumables') NOT NULL DEFAULT 'consumables' AFTER name");
    echo "Category column added to products table." . PHP_EOL;
  } else {
    echo "Category column already exists." . PHP_EOL;
  }
} catch (Throwable $e) {
  echo "Migration failed: " . $e->getMessage() . PHP_EOL;
}
?>