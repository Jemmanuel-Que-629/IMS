<?php

// Determine environment based on host automatically
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
// Treat localhost and private LAN IPs as development
$isLocal = ($host === 'localhost')
    || (strpos($host, '127.0.0.1') !== false)
    || preg_match('/^(10\.|192\.168\.|172\.(1[6-9]|2\d|3[0-1])\.)/i', $host);
$environment = $isLocal ? 'development' : 'production';

// Build a dynamic app URL so redirects work on LAN/mobile
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
// If behind a proxy, honor X-Forwarded-Proto
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    $xfp = strtolower(trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'])[0]));
    if ($xfp === 'https' || $xfp === 'http') { $scheme = $xfp; }
}
$basePath = '/IMS'; // adjust if you deploy under a different subfolder
$dynamicAppUrl = $scheme . '://' . $host . $basePath;

date_default_timezone_set('Asia/Manila');

return array(
    // Basic Information
    'app_name' => 'Inventory Management System',
    'environment' => $environment,
    'debug' => ($environment === 'development'),
    'app_url' => $dynamicAppUrl,

    // Database Configuration, no PDO connection here, just settings
    'db_host' => ($environment === 'development') ? 'localhost' : 'production_db_host', 
    'db_name' => ($environment === 'development') ? 'ims_db' : 'prod_db', 
    'db_username' => ($environment === 'development') ? 'root' : 'prod_user', 
    'db_password' => ($environment === 'development') ? '' : 'prod_password', 

    'supported_roles' => array('admin', 'manager', 'operator'),
);