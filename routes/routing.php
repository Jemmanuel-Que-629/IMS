<?php
// routes.php

$routes = [
    'admin' => [
        'dashboard' => '../users/admin/dashboard.php',
        'users'     => '../users/admin/users.php',
        'logs'      => '../users/admin/logs.php',
        'reports'   => '../users/admin/reports.php',
        // add more admin pages here
    ],
    'manager' => [
        'dashboard' => '../users/manager/dashboard.php',
        'reports'   => '../users/manager/reports.php',
        // add more manager pages
    ],
    'operator' => [
        'tasks'     => '../users/operator/tasks.php',
        'profile'   => '../users/operator/profile.php',
    ],
];

// Route handler function
function route($role, $page) {
    global $routes;

    if (isset($routes[$role][$page])) {
        require $routes[$role][$page];
        exit;
    } else {
        http_response_code(404);
        require __DIR__ . '/../views/errors/404.php';
        exit;
    }
}

?>