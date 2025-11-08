<?php
// migrations.php
require_once __DIR__ . '/../db_connection.php'; // make sure $pdo is defined

try {
    // -------- Roles table --------
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS roles (
            role_id INT AUTO_INCREMENT PRIMARY KEY,
            role_name VARCHAR(50) NOT NULL UNIQUE
        );
    ");

    // Insert default roles
    $pdo->exec("
        INSERT INTO roles (role_name) VALUES
        ('admin'), ('manager'), ('operator')
        ON DUPLICATE KEY UPDATE role_name=role_name;
    ");

    // -------- Users table --------
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            user_id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (role_id) REFERENCES roles(role_id)
        );
    ");

    // -------- Products table --------
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS products (
            product_id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            quantity INT DEFAULT 0,
            price DECIMAL(10,2) NOT NULL,
            picture VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );
    ");

    // -------- Stock history table --------
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS stock_history (
            history_id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            action ENUM('added','removed','updated') NOT NULL,
            quantity INT NOT NULL,
            performed_by INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(product_id),
            FOREIGN KEY (performed_by) REFERENCES users(user_id)
        );
    ");

    echo "All tables created successfully.";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
