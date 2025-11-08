<?php
$config = require __DIR__ . '/../config/app.php';
$appUrl = $config['app_url'];
// Determine current path for active menu highlighting
$basePath = rtrim(parse_url($appUrl, PHP_URL_PATH) ?? '', '/');
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$active = function(string $target) use ($basePath, $currentPath) {
    $full = $basePath . $target;
    if ($currentPath === $full) return 'active';
    // Fallback by filename match
    return (basename($currentPath) === basename($target)) ? 'active' : '';
};
?>

<aside class="sidebar">
	<div class="menu-title">Main</div>
	<nav class="nav flex-column">
		<a class="nav-link <?= $active('/views/dashboard.php') ?>" href="<?= $appUrl ?>/views/dashboard.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Dashboard">
			<i class="bi bi-speedometer2"></i>
			<span class="text">Dashboard</span>
		</a>
			<a class="nav-link <?= $active('/views/users.php') ?>" href="<?= $appUrl ?>/views/users.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Users">
			<i class="bi bi-people"></i>
			<span class="text">Users</span>
		</a>
		<a class="nav-link <?= $active('/views/products.php') ?>" href="<?= $appUrl ?>/views/products.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Products">
			<i class="bi bi-box-seam"></i>
			<span class="text">Products</span>
		</a>
		<a class="nav-link <?= $active('/views/stock_history.php') ?>" href="<?= $appUrl ?>/views/stock_history.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Stock History">
			<i class="bi bi-arrow-left-right"></i>
			<span class="text">Stock History</span>
		</a>
		<a class="nav-link mt-5" href="<?= $appUrl ?>/backend/auth/logout.php" data-bs-toggle="tooltip" data-bs-placement="right" title="Logout">
			<i class="bi bi-box-arrow-right"></i>
			<span class="text">Logout</span>
		</a>
	</nav>
</aside>

