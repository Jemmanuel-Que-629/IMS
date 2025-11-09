<?php
// unified_logs_management.php - read-only logs API with filters
session_start();
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../../database/db_connection.php';
    require_once __DIR__ . '/../../helpers/auth.php';
    // Ensure only privileged roles can view logs
    if (!is_logged_in() || !in_array($_SESSION['user_role'], ['admin','manager'])) {
        echo json_encode(['success'=>false,'message'=>'Forbidden']);
        exit;
    }
} catch (Throwable $e) {
    echo json_encode(['success'=>false,'message'=>'Bootstrap failed: '.$e->getMessage()]);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'list_logs';
if ($action !== 'list_logs') {
    echo json_encode(['success'=>false,'message'=>'Unknown action']);
    exit;
}

// Filters
$dateFrom = trim($_GET['date_from'] ?? $_POST['date_from'] ?? '');
$dateTo   = trim($_GET['date_to'] ?? $_POST['date_to'] ?? '');
$fAction  = trim($_GET['log_action'] ?? $_POST['log_action'] ?? '');
$fEntity  = trim($_GET['entity'] ?? $_POST['entity'] ?? '');
$fUserId  = isset($_GET['user_id']) ? (int)$_GET['user_id'] : (isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0);

$where = [];
$params = [];

if ($dateFrom !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
    $where[] = 'DATE(l.created_at) >= ?';
    $params[] = $dateFrom;
}
if ($dateTo !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
    $where[] = 'DATE(l.created_at) <= ?';
    $params[] = $dateTo;
}
if ($fAction !== '') {
    $where[] = 'l.action = ?';
    $params[] = $fAction;
}
if ($fEntity !== '') {
    $where[] = 'l.entity = ?';
    $params[] = $fEntity;
}
if ($fUserId > 0) {
    $where[] = 'l.user_id = ?';
    $params[] = $fUserId;
}

// Join logs with users to get full name
$sql = 'SELECT 
            l.log_id, 
            l.user_id, 
            l.action, 
            l.entity, 
            l.entity_id, 
            l.description, 
            l.ip_address, 
            l.user_agent, 
            l.created_at,
            u.first_name,
            u.middle_name,
            u.last_name,
            u.extension
        FROM logs l
        LEFT JOIN users u ON l.user_id = u.user_id';

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY l.log_id DESC LIMIT 1000'; // safety cap

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build full name for each row
foreach ($rows as &$row) {
    $row['full_name'] = $row['first_name']
                      . (!empty($row['middle_name']) ? ' ' . $row['middle_name'] : '')
                      . ' ' . $row['last_name']
                      . (!empty($row['extension']) ? ' ' . $row['extension'] : '');
}

echo json_encode(['success'=>true,'message'=>'OK','data'=>['items'=>$rows]]);
