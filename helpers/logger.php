<?php
// helpers/logger.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Insert a log record capturing user, action, entity and request context
 *
 * @param PDO $pdo
 * @param string $action Short verb like create|update|archive|delete|login|logout
 * @param string|null $entity Resource type e.g., product|user|log
 * @param int|null $entityId Resource id
 * @param string|array|null $description Optional description or associative array (will be JSON-encoded)
 * @return void
 */
function log_action(PDO $pdo, string $action, ?string $entity = null, ?int $entityId = null, $description = null): void {
    try {
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $username = isset($_SESSION['username']) ? (string)$_SESSION['username'] : null;
        $ip = null;
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        }
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

        if (is_array($description)) {
            $description = json_encode($description, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $stmt = $pdo->prepare("INSERT INTO logs (user_id, username, action, entity, entity_id, description, ip_address, user_agent) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$userId, $username, $action, $entity, $entityId, $description, $ip, $ua]);
    } catch (\Throwable $e) {
        // Fail-safe: do not break business flow on logging failure
        // error_log('Log insert failed: ' . $e->getMessage());
    }
}
