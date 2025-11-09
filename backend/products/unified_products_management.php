<?php
session_start();
header('Content-Type: application/json');

try {
	require_once __DIR__ . '/../../database/db_connection.php';
	$config = require __DIR__ . '/../../config/app.php';
	require_once __DIR__ . '/../../helpers/logger.php';
} catch (Throwable $e) {
	echo json_encode(['success' => false, 'message' => 'Bootstrap failed: '.$e->getMessage()]);
	exit;
}

// Allow both GET/POST
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$allowedActions = [
	'list_products',
	'create_product',
	'view_product',
	'update_product',
	'archive_product',
	'delete_product'
];

if (!in_array($action, $allowedActions, true)) {
	err('Unknown action');
}

function ok($data = [], $message = 'OK') { echo json_encode(['success'=>true,'message'=>$message,'data'=>$data]); exit; }
function err($message = 'Error', $data = []) { echo json_encode(['success'=>false,'message'=>$message,'data'=>$data]); exit; }

switch ($action) {
	case 'list_products':
		list_products($pdo);
		break;
	case 'create_product':
		create_product($pdo);
		break;
	case 'view_product':
		view_product($pdo);
		break;
	case 'update_product':
		update_product($pdo);
		break;
	case 'archive_product':
		archive_product($pdo);
		break;
	case 'delete_product':
		delete_product($pdo);
		break;
}

function list_products(PDO $pdo) {
	$category = $_GET['category'] ?? $_POST['category'] ?? null;
	$params = [];
	$where = 'WHERE is_archived = 0';
	if ($category && in_array($category, ['consumables','non_consumables'], true)) {
		$where .= ' AND category = ?';
		$params[] = $category;
	}
	$sql = "SELECT product_id, name, category, quantity, price, picture, created_at, updated_at FROM products $where ORDER BY created_at DESC";
	$stmt = $pdo->prepare($sql);
	$stmt->execute($params);
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	ok(['items' => $rows]);
}

function create_product(PDO $pdo) {
	$name = trim($_POST['name'] ?? '');
	$qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
	$price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
	$category = $_POST['category'] ?? '';

	if ($name === '' || $qty < 0 || $price < 0 || !in_array($category, ['consumables','non_consumables'], true)) {
		err('Please provide valid product details');
	}

	// Duplicate validation (case-insensitive) among non-archived products
	$dupStmt = $pdo->prepare('SELECT product_id FROM products WHERE LOWER(name) = LOWER(?) AND category = ? AND is_archived = 0 LIMIT 1');
	$dupStmt->execute([$name, $category]);
	if ($dupStmt->fetch()) {
		err('A product with this name already exists in the selected category');
	}

	$picturePath = null;
	if (!empty($_FILES['picture']['name'])) {
		$picturePath = handle_image_upload($_FILES['picture'], $category);
	}

	$stmt = $pdo->prepare('INSERT INTO products (name, category, quantity, price, picture) VALUES (?,?,?,?,?)');
	$stmt->execute([$name, $category, $qty, $price, $picturePath]);
	$newId = (int)$pdo->lastInsertId();
	// Log create
	log_action($pdo, 'create', 'product', $newId, [
		'name' => $name,
		'category' => $category,
		'quantity' => $qty,
		'price' => $price,
		'picture' => $picturePath
	]);
	ok(['product_id' => $newId]);
}

function view_product(PDO $pdo) {
	$id = (int)($_GET['product_id'] ?? $_POST['product_id'] ?? 0);
	if ($id <= 0) err('Invalid product id');
	$stmt = $pdo->prepare('SELECT product_id, name, category, quantity, price, picture, is_archived, created_at, updated_at, archived_at FROM products WHERE product_id = ?');
	$stmt->execute([$id]);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	if (!$row) err('Product not found');
	ok(['item' => $row]);
}

function update_product(PDO $pdo) {
	$id = (int)($_POST['product_id'] ?? 0);
	if ($id <= 0) err('Invalid product id');

	$name = trim($_POST['name'] ?? '');
	$qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
	$price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
	$category = $_POST['category'] ?? '';

	if ($name === '' || $qty < 0 || $price < 0 || !in_array($category, ['consumables','non_consumables'], true)) {
		err('Please provide valid product details');
	}
	// Existence
	$stmt = $pdo->prepare('SELECT product_id, picture, category FROM products WHERE product_id = ?');
	$stmt->execute([$id]);
	$current = $stmt->fetch(PDO::FETCH_ASSOC);
	if (!$current) err('Product not found');

	// Duplicate check ignoring current id
	$dupStmt = $pdo->prepare('SELECT product_id FROM products WHERE LOWER(name) = LOWER(?) AND category = ? AND product_id <> ? AND is_archived = 0 LIMIT 1');
	$dupStmt->execute([$name, $category, $id]);
	if ($dupStmt->fetch()) err('Another product with this name exists in that category');

	$newPicturePath = $current['picture'];
	if (!empty($_FILES['picture']['name'])) {
		$newPicturePath = handle_image_upload($_FILES['picture'], $category);
	} elseif ($category !== $current['category'] && $current['picture']) {
		// Move existing picture folder if category changed and picture exists
		$newPicturePath = move_existing_image_to_new_category($current['picture'], $category);
	}

	$upStmt = $pdo->prepare('UPDATE products SET name = ?, category = ?, quantity = ?, price = ?, picture = ?, updated_at = CURRENT_TIMESTAMP WHERE product_id = ?');
	$upStmt->execute([$name, $category, $qty, $price, $newPicturePath, $id]);
	// Log update
	log_action($pdo, 'update', 'product', $id, [
		'name' => $name,
		'category' => $category,
		'quantity' => $qty,
		'price' => $price,
		'picture' => $newPicturePath
	]);
	ok(['product_id' => $id]);
}

function archive_product(PDO $pdo) {
	$id = (int)($_POST['product_id'] ?? 0);
	if ($id <= 0) err('Invalid product id');
	$stmt = $pdo->prepare('UPDATE products SET is_archived = 1, archived_at = CURRENT_TIMESTAMP WHERE product_id = ? AND is_archived = 0');
	$stmt->execute([$id]);
	if ($stmt->rowCount() === 0) err('Product not found or already archived');
	// Log archive
	log_action($pdo, 'archive', 'product', $id, 'Product archived');
	ok(['product_id' => $id], 'Archived');
}

function delete_product(PDO $pdo) {
	$id = (int)($_POST['product_id'] ?? 0);
	if ($id <= 0) err('Invalid product id');
	// Fetch image for cleanup (optional)
	$stmt = $pdo->prepare('SELECT picture FROM products WHERE product_id = ?');
	$stmt->execute([$id]);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	if (!$row) err('Product not found');
	$del = $pdo->prepare('DELETE FROM products WHERE product_id = ?');
	$del->execute([$id]);
	// Optional: delete image file
	if ($row['picture']) {
		$abs = realpath(__DIR__ . '/../../') . str_replace('/', DIRECTORY_SEPARATOR, $row['picture']);
		if (is_file($abs)) @unlink($abs);
	}
	// Log delete
	log_action($pdo, 'delete', 'product', $id, 'Product deleted');
	ok(['product_id' => $id], 'Deleted');
}

function handle_image_upload(array $file, string $category): ?string {
	if (empty($file['name'])) return null;
	$allowed = ['jpg','jpeg','png','gif','webp'];
	$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
	if (!in_array($ext, $allowed, true)) err('Invalid image type');
	if ($file['error'] !== UPLOAD_ERR_OK) err('Upload error code: '.$file['error']);
	if ($file['size'] > 5 * 1024 * 1024) err('Image exceeds 5MB limit');
	if (!is_uploaded_file($file['tmp_name'])) err('Upload failed');

	$safeName = preg_replace('/[^a-z0-9\-_.]/i','_', pathinfo($file['name'], PATHINFO_FILENAME));
	$finalName = $safeName . '_' . uniqid('', true) . '.' . $ext;
	$baseDir = realpath(__DIR__ . '/../../');
	$targetDir = $baseDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR . $category . DIRECTORY_SEPARATOR;
	if (!is_dir($targetDir)) {
		@mkdir($targetDir, 0775, true);
	}
	$targetFile = $targetDir . $finalName;
	if (!move_uploaded_file($file['tmp_name'], $targetFile)) err('Failed to save uploaded image');
	return '/uploads/products/' . $category . '/' . $finalName;
}

function move_existing_image_to_new_category(string $currentPath, string $newCategory): string {
	// currentPath is like /uploads/products/consumables/file.jpg
	$baseDir = realpath(__DIR__ . '/../../');
	$currentAbs = $baseDir . str_replace('/', DIRECTORY_SEPARATOR, $currentPath);
	if (!is_file($currentAbs)) return $currentPath; // nothing to move
	$filename = basename($currentAbs);
	$newDir = $baseDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR . $newCategory . DIRECTORY_SEPARATOR;
	if (!is_dir($newDir)) @mkdir($newDir, 0775, true);
	$newAbs = $newDir . $filename;
	if (@rename($currentAbs, $newAbs)) {
		return '/uploads/products/' . $newCategory . '/' . $filename;
	}
	return $currentPath; // fallback keep old path
}
?>
