<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$config = require __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../helpers/auth.php';

// Attempt to load profile picture for logged-in user
$profilePic = null;
if (is_logged_in()) {
	try {
		require_once __DIR__ . '/../database/db_connection.php';
		$uid = (int)($_SESSION['user_id'] ?? 0);
		if ($uid > 0) {
			$stmt = $pdo->prepare('SELECT profile_pic FROM users WHERE user_id = ? LIMIT 1');
			$stmt->execute([$uid]);
			$pic = $stmt->fetchColumn();
			if ($pic) {
				$profilePic = $pic; // Stored with leading /uploads/...
			}
		}
	} catch (Throwable $e) {
		// Silently ignore to avoid breaking header rendering
	}
}

$appUrl = $config['app_url'];
$fullName = $_SESSION['full_name'] ?? ($_SESSION['username'] ?? 'User');
$initials = function($name){
		$parts = preg_split('/\s+/', trim($name));
		$ini = '';
		foreach ($parts as $p) { if ($p !== '') { $ini .= mb_strtoupper(mb_substr($p,0,1)); } }
		return mb_substr($ini, 0, 2);
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle : 'Default App Title' ?></title>

	<!-- Bootstrap CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<!-- Bootstrap Icons -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
	<!-- Google Fonts: Poppins -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

	<!-- Dashboard Styles -->
	<link rel="stylesheet" href="<?= $appUrl ?>/public/css/dashboard.css">
</head>
<body>
	<div class="layout-wrapper">
		<!-- Topbar -->
		<header class="topbar">
			<div class="left brand-toggle">
				<button id="sidebarToggle" class="btn-icon" aria-label="Toggle sidebar">
					<i class="bi bi-list"></i>
				</button>
					<?php
						$roleDisplayMap = [ 'admin' => 'Admin', 'manager' => 'Manager', 'operator' => 'Operator' ];
						$currentRoleKey = strtolower($_SESSION['user_role'] ?? '');
						$roleDisplay = $roleDisplayMap[$currentRoleKey] ?? 'User';
					?>
					<span class="fw-semibold">IMS - <?= htmlspecialchars($roleDisplay) ?></span>
			</div>

			<div class="center">
				<i class="bi bi-clock me-2"></i>
				<span id="currentDateTime">--</span>
			</div>

			<div class="right">
				<button class="btn-icon" aria-label="Notifications">
					<i class="bi bi-bell"></i>
				</button>

				<div class="dropdown">
					<button class="btn p-0 border-0" data-bs-toggle="dropdown" aria-expanded="false">
						<?php if ($profilePic): ?>
							<img src="<?= htmlspecialchars($config['app_url'] . $profilePic) ?>" alt="Avatar" class="avatar" style="object-fit:cover; border-radius:50%;" />
						<?php else: ?>
							<span class="avatar"><?= htmlspecialchars($initials($fullName)) ?></span>
						<?php endif; ?>
					</button>
					<ul class="dropdown-menu dropdown-menu-end shadow">
						<li class="px-3 py-2 small text-muted">Signed in as<br><strong><?= htmlspecialchars($fullName) ?></strong></li>
						<li><hr class="dropdown-divider"></li>
						<li><a class="dropdown-item" href="<?= $appUrl ?>/views/profile.php"><i class="bi bi-person-circle me-2"></i>Profile</a></li>
						<li><a class="dropdown-item" href="<?= $appUrl ?>/backend/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
					</ul>
				</div>
			</div>
		</header>

		<!-- Sidebar include goes in the page template before content -->

