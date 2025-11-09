<?php
// 005_alter_products_add_is_archived.php
// Adds is_archived flag (tinyint) and archived_at timestamp to products table if they do not exist
require_once __DIR__ . '/../db_connection.php';

try {
    $columns = $pdo->query("SHOW COLUMNS FROM products LIKE 'is_archived'");
    if ($columns->rowCount() === 0) {
        $pdo->exec("ALTER TABLE products ADD COLUMN is_archived TINYINT(1) NOT NULL DEFAULT 0 AFTER picture");
        $pdo->exec("ALTER TABLE products ADD COLUMN archived_at TIMESTAMP NULL DEFAULT NULL AFTER updated_at");
        echo "is_archived and archived_at columns added to products table." . PHP_EOL;
    } else {
        // Ensure archived_at exists too
        $archivedAtCheck = $pdo->query("SHOW COLUMNS FROM products LIKE 'archived_at'");
        if ($archivedAtCheck->rowCount() === 0) {
            $pdo->exec("ALTER TABLE products ADD COLUMN archived_at TIMESTAMP NULL DEFAULT NULL AFTER updated_at");
            echo "archived_at column added to products table." . PHP_EOL;
        } else {
            echo "is_archived and archived_at columns already exist." . PHP_EOL;
        }
    }
} catch (Throwable $e) {
    echo "Migration failed: " . $e->getMessage() . PHP_EOL;
}
