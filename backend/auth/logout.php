<?php
session_start();
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../database/db_connection.php';
require_once __DIR__ . '/../../helpers/logger.php';

// Log logout before destroying the session
try {
	if (isset($pdo)) {
		$uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
		$uname = $_SESSION['username'] ?? null;
		log_action($pdo, 'logout', 'auth', $uid, [ 'username' => $uname ]);
	}
} catch (Throwable $e) {
	// fail-safe
}

// Logout user
logout();
