<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../helpers/auth.php';

// If user is already logged in, redirect to dashboard
if (is_logged_in()) {
    $role = $_SESSION['user_role']; // e.g., 'super_admin', 'manager', 'operator'
    switch ($role) {
        case 'super_admin':
            header('Location: ' . get_base_url() . '/views/dashboard.php');
            exit;
        case 'manager':
            header('Location: ' . get_base_url() . '/views/dashboard.php');
            exit;
        case 'operator':
            header('Location: ' . get_base_url() . '/views/dashboard.php');
            exit;
    }
}

// If not logged in, show login form
require_once __DIR__ . '/../views/login_form.php';
