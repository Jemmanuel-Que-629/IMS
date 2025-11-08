<?php
// 002_alter_users.php
require_once __DIR__ . '/../db_connection.php'; // adjust path if needed

try {
    $sql = "
        ALTER TABLE users
        ADD COLUMN first_name VARCHAR(50) NOT NULL AFTER role_id,
        ADD COLUMN middle_name VARCHAR(50) DEFAULT NULL AFTER first_name,
        ADD COLUMN last_name VARCHAR(50) NOT NULL AFTER middle_name,
        ADD COLUMN extension VARCHAR(10) DEFAULT NULL AFTER last_name;
    ";

    $pdo->exec($sql);

    echo "Users table updated successfully: added first_name, middle_name, last_name, extension columns.";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
