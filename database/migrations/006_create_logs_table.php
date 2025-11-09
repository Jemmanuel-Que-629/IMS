<?php
// 006_create_logs_table.php
// Create logs table for non-repudiation: tracks user, action, entity, timestamp, and request metadata
require_once __DIR__ . '/../db_connection.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS logs (
        log_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        username VARCHAR(100) NULL,
        action VARCHAR(50) NOT NULL,
        entity VARCHAR(50) NULL,
        entity_id INT NULL,
        description TEXT NULL,
        ip_address VARCHAR(45) NULL,
        user_agent VARCHAR(255) NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id),
        INDEX (action),
        INDEX (entity),
        INDEX (created_at),
        CONSTRAINT fk_logs_user_id FOREIGN KEY (user_id) REFERENCES users(user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

    echo "Logs table ensured." . PHP_EOL;
} catch (Throwable $e) {
    echo "Migration failed: " . $e->getMessage() . PHP_EOL;
}
