<?php
session_start();
require_once __DIR__ . '/../helpers/auth.php';
require_role(['admin']);

// Optionally compute quick stats
$totalProducts = 0; $totalUsers = 0; $lowStock = 0;
try {
	require_once __DIR__ . '/../database/db_connection.php';
	$totalProducts = (int)($pdo->query('SELECT COUNT(*) FROM products')->fetchColumn() ?: 0);
	$totalUsers = (int)($pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() ?: 0);
	$lowStock = (int)($pdo->query('SELECT COUNT(*) FROM products WHERE quantity <= 5')->fetchColumn() ?: 0);
} catch (Throwable $e) { /* ignore for now */ }
?>
<?php require_once __DIR__ . '/../global/header.php'; ?>
<?php require_once __DIR__ . '/../global/sidebar.php'; ?>

<main class="content">
	<div class="page-title">Dashboard</div>

	<div class="row g-3 mb-3">
		<div class="col-12 col-sm-6 col-lg-4">
			<div class="card stat-card">
				<div class="icon"><i class="bi bi-box-seam fs-4"></i></div>
				<div>
					<div class="value"><?= number_format($totalProducts) ?></div>
					<div class="label">Total Products</div>
				</div>
			</div>
		</div>
		<div class="col-12 col-sm-6 col-lg-4">
			<?php $lowStockAlertThreshold = 5; $lowClass = ($lowStock >= $lowStockAlertThreshold) ? ' danger' : ''; ?>
			<div class="card stat-card<?= $lowClass; ?>">
				<div class="icon"><i class="bi bi-exclamation-triangle fs-4"></i></div>
				<div>
					<div class="value"><?= number_format($lowStock) ?></div>
					<div class="label">Low Stock Items</div>
				</div>
			</div>
		</div>
		<div class="col-12 col-sm-6 col-lg-4">
			<div class="card stat-card">
				<div class="icon"><i class="bi bi-people fs-4"></i></div>
				<div>
					<div class="value"><?= number_format($totalUsers) ?></div>
					<div class="label">System Users</div>
				</div>
			</div>
		</div>
	</div>

	<div class="card">
		<div class="card-header d-flex align-items-center justify-content-between">
			<span>Recent Activity</span>
			<a href="#" class="btn btn-sm btn-outline-primary"><i class="bi bi-clock-history me-1"></i>View All</a>
		</div>
		<div class="card-body p-0">
			<div class="table-responsive">
				<table class="table align-middle mb-0">
					<thead>
						<tr>
							<th scope="col">When</th>
							<th scope="col">Action</th>
							<th scope="col">Item</th>
							<th scope="col" class="text-end">Quantity</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>—</td>
							<td>No recent activity yet</td>
							<td>—</td>
							<td class="text-end">—</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

</main>

<?php require_once __DIR__ . '/../global/footer.php'; ?>

