<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

/**
 * Check if user has specific role
 * @param string $role
 * @return bool
 */
function has_role($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Redirect to login page if not logged in
 */
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['error_message'] = 'Please login to access this page.';
        header('Location: ' . get_base_url() . '/public/index.php');
        exit;
    }
}

/**
 * Redirect to login page if user doesn't have required role
 * @param array $allowed_roles
 */
function require_role($allowed_roles) {
    require_login();

    if (!in_array($_SESSION['user_role'], $allowed_roles)) {
        $_SESSION['error_message'] = 'You do not have permission to access this page.';
        // redirect to a safe page to prevent loop
        header('Location: ' . get_base_url() . '/views/errors/403.php');
        exit;
    }
}

/**
 * Logout user
 */
function logout() {
    session_unset();
    session_destroy();
    header('Location: ' . get_base_url() . '/public/index.php');
    exit;
}

/**
 * Get base URL
 * @return string
 */
function get_base_url() {
    $config = require __DIR__ . '/../config/app.php';
    return $config['app_url'];
}

/**
 * Get current user session data
 * @return array|null
 */
function get_logged_in_user() {
    if (!is_logged_in()) {
        return null;
    }

    return [
        'user_id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'role' => $_SESSION['user_role'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? null,
    ];
}
