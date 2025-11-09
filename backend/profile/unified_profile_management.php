<?php
// unified_profile_management.php
session_start();
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../../database/db_connection.php';
    require_once __DIR__ . '/../../helpers/auth.php';
    require_once __DIR__ . '/../../helpers/logger.php';
} catch (Throwable $e) {
    echo json_encode(['success'=>false,'message'=>'Bootstrap failed: '.$e->getMessage()]);
    exit;
}

if (!is_logged_in()) {
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

function ok($data = [], $message = 'OK') { echo json_encode(['success'=>true,'message'=>$message,'data'=>$data]); exit; }
function err($message = 'Error', $data = []) { echo json_encode(['success'=>false,'message'=>$message,'data'=>$data]); exit; }

switch ($action) {
    case 'get_profile':
        get_profile($pdo);
        break;
    case 'upload_profile_pic':
        upload_profile_pic($pdo);
        break;
    case 'change_password':
        change_password($pdo);
        break;
    default:
        err('Unknown action');
}

function get_profile(PDO $pdo) {
    $uid = (int)($_SESSION['user_id'] ?? 0);
    $stmt = $pdo->prepare('SELECT user_id, username, email, first_name, middle_name, last_name, extension, profile_pic, created_at, updated_at FROM users WHERE user_id = ?');
    $stmt->execute([$uid]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) err('User not found');
    ok(['user' => $row]);
}

function upload_profile_pic(PDO $pdo) {
    if (empty($_FILES['profile_pic']['name'])) err('Please choose an image to upload');

    $uid = (int)($_SESSION['user_id'] ?? 0);
    if ($uid <= 0) err('Unauthorized');

    $allowed = ['jpg','jpeg','png','gif','webp'];
    $file = $_FILES['profile_pic'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed, true)) err('Invalid image type');
    if ($file['error'] !== UPLOAD_ERR_OK) err('Upload error code: '.$file['error']);
    if ($file['size'] > 5 * 1024 * 1024) err('Image exceeds 5MB limit');
    if (!is_uploaded_file($file['tmp_name'])) err('Upload failed');

    // Fetch current picture path to possibly delete later
    $cur = $pdo->prepare('SELECT profile_pic FROM users WHERE user_id = ?');
    $cur->execute([$uid]);
    $current = $cur->fetchColumn();

    $safeName = preg_replace('/[^a-z0-9\-_.]/i','_', pathinfo($file['name'], PATHINFO_FILENAME));
    $finalName = $safeName . '_' . uniqid('', true) . '.' . $ext;

    $baseDir = realpath(__DIR__ . '/../../');
    $targetDir = $baseDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'profile_pic' . DIRECTORY_SEPARATOR;
    if (!is_dir($targetDir)) @mkdir($targetDir, 0775, true);

    $targetFile = $targetDir . $finalName;
    if (!move_uploaded_file($file['tmp_name'], $targetFile)) err('Failed to save uploaded image');

    $webPath = '/uploads/images/profile_pic/' . $finalName;

    $up = $pdo->prepare('UPDATE users SET profile_pic = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?');
    $up->execute([$webPath, $uid]);

    // Delete old pic if exists
    if ($current) {
        $abs = $baseDir . str_replace('/', DIRECTORY_SEPARATOR, $current);
        if (is_file($abs)) @unlink($abs);
    }

    // Log
    log_action($pdo, 'update', 'profile', $uid, ['field' => 'profile_pic', 'path' => $webPath]);

    ok(['profile_pic' => $webPath], 'Profile picture updated');
}

function change_password(PDO $pdo) {
    $uid = (int)($_SESSION['user_id'] ?? 0);
    if ($uid <= 0) err('Unauthorized');

    $current = trim($_POST['current_password'] ?? '');
    $new = trim($_POST['new_password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if ($current === '' || $new === '' || $confirm === '') err('All fields are required');
    if (strlen($new) < 8) err('New password must be at least 8 characters');
    if ($new !== $confirm) err('New password and confirmation do not match');

    $stmt = $pdo->prepare('SELECT password FROM users WHERE user_id = ?');
    $stmt->execute([$uid]);
    $hash = $stmt->fetchColumn();
    if (!$hash || !password_verify($current, $hash)) err('Current password is incorrect');

    $newHash = password_hash($new, PASSWORD_BCRYPT);
    $up = $pdo->prepare('UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?');
    $up->execute([$newHash, $uid]);

    // Log (never include password contents)
    log_action($pdo, 'password_change', 'profile', $uid, 'User changed password');

    ok([], 'Password changed successfully');
}
