<?php
session_start();
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

try {
  require_once __DIR__ . '/../../database/db_connection.php';
  $config = require __DIR__ . '/../../config/app.php';
  require_once __DIR__ . '/../../vendor/autoload.php';
  require_once __DIR__ . '/../../helpers/logger.php';
} catch (Throwable $e) {
  $traceData = method_exists($e,'getTrace') ? $e->getTrace() : [];
  echo json_encode([
    'success' => false,
    'message' => 'Initialization failed: '.$e->getMessage(),
    'debug' => [
      'phase' => 'bootstrap',
      'exception' => $e->getMessage(),
      'trace' => [ $traceData ]
    ]
  ]);
  exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

function respond($success, $message, $data = [], $debug = null) {
  $payload = [ 'success' => $success, 'message' => $message ];
  if ($data !== []) $payload['data'] = $data;
  if ($debug !== null) $payload['debug'] = $debug;
  echo json_encode($payload);
  exit;
}
function json_ok($data = [], $message = 'OK', $debug = null) { respond(true, $message, $data, $debug); }
function json_err($message = 'Error', $debug = null) { respond(false, $message, [], $debug); }

function get_int($key, $default = 0) { return isset($_POST[$key]) ? (int)$_POST[$key] : (isset($_GET[$key]) ? (int)$_GET[$key] : $default); }
function get_str($key, $default = '') { return isset($_POST[$key]) ? trim((string)$_POST[$key]) : (isset($_GET[$key]) ? trim((string)$_GET[$key]) : $default); }

function generate_username(PDO $pdo, string $first, string $last): string {
  $base = strtolower(preg_replace('/[^a-z0-9]/i', '', mb_substr($first,0,1) . $last));
  if ($base === '') { $base = 'user'; }
  $candidate = $base;
  $suffix = 0;
  $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
  while (true) {
    $stmt->execute([$candidate]);
    if ((int)$stmt->fetchColumn() === 0) return $candidate;
    $suffix++;
    $candidate = $base . $suffix;
  }
}

function generate_password(int $length = 10): string {
  $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789@$!%*?&';
  $bytes = random_bytes($length);
  $out = '';
  for ($i=0; $i<$length; $i++) { $out .= $alphabet[ord($bytes[$i]) % strlen($alphabet)]; }
  return $out;
}

switch ($action) {
  case 'get_user':
    get_user($pdo, $config);
    break;
  case 'create_user':
    create_user($pdo, $config);
    break;
  case 'update_user':
    update_user($pdo, $config);
    break;
  case 'archive_user':
    archive_user($pdo, $config);
    break;
  default:
    json_err('Unknown action', build_debug('unknown_action', $config));
}

function build_debug($phase, $config, $extra = []) {
  if (empty($config['debug'])) return null;
  return array_merge([
    'phase' => $phase,
    'action' => $_POST['action'] ?? $_GET['action'] ?? null,
    'inputs' => [
      'POST' => $_POST,
      'GET'  => $_GET,
    ],
    'timestamp' => date('c'),
    'memory_usage' => memory_get_usage(),
    'client' => [
      'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
      'ua' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ]
  ], $extra);
}

function build_full_name($first, $middle, $last, $ext) {
  $mi = $middle ? strtoupper(mb_substr($middle,0,1)).'.' : '';
  $parts = array_filter([$first, $mi, $last, $ext]);
  return trim(implode(' ', $parts));
}

function get_user(PDO $pdo, $config) {
  $id = get_int('user_id');
  if ($id <= 0) json_err('Invalid user', build_debug('get_user_invalid', $config));
  $stmt = $pdo->prepare('SELECT u.*, r.role_name FROM users u INNER JOIN roles r ON u.role_id=r.role_id WHERE u.user_id=? LIMIT 1');
  $stmt->execute([$id]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) json_err('User not found', build_debug('get_user_not_found', $config, ['user_id' => $id]));
  // Add computed full name for convenience
  $row['full_name'] = build_full_name($row['first_name'] ?? '', $row['middle_name'] ?? '', $row['last_name'] ?? '', $row['extension'] ?? '');
  // Log view
  log_action($pdo, 'view', 'user', $id, ['username' => $row['username'], 'email' => $row['email']]);
  json_ok($row, 'User fetched', build_debug('get_user_ok', $config, ['user_id' => $id]));
}

function create_user(PDO $pdo, $config) {
  $email = get_str('email');
  $role_id = get_int('role_id');
  $first = get_str('first_name');
  $middle = get_str('middle_name');
  $last = get_str('last_name');
  $ext = get_str('extension');

  if ($email === '' || $role_id <= 0 || $first === '' || $last === '') {
    json_err('Please fill in required fields', build_debug('create_missing_fields', $config));
  }

  // Pre-validate email uniqueness
  $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
  $stmt->execute([$email]);
  $emailExists = (int)$stmt->fetchColumn();
  if ($emailExists > 0) json_err('Email already exists', build_debug('create_email_exists', $config, ['email' => $email]));

  // Generate unique username and secure password
  $username = generate_username($pdo, $first, $last);
  $plainPassword = generate_password(12);
  $hash = password_hash($plainPassword, PASSWORD_BCRYPT);

  // Insert user
  $stmt = $pdo->prepare('INSERT INTO users (username,email,password,role_id,first_name,middle_name,last_name,extension) VALUES (?,?,?,?,?,?,?,?)');
  $stmt->execute([$username,$email,$hash,$role_id,$first,$middle,$last,$ext]);
  $newId = (int)$pdo->lastInsertId();

  // Send credentials via email using PHPMailer
  $mail = new PHPMailer(true);
  $sendOk = false; $sendErr = '';
  try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'jc.saxophonist0629@gmail.com';
    $mail->Password = 'gzsa prqj ryxo dhqk';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('jc.saxophonist0629@gmail.com', 'IMS Admin');
  $mail->addAddress($email, build_full_name($first, $middle, $last, $ext));
    $mail->isHTML(true);
    $mail->Subject = 'Your IMS account credentials';

    $brand = '#4169e1';
    $html = '<div style="font-family:Poppins,Segoe UI,Roboto,Arial,sans-serif;background:#f5f7fb;padding:24px">'
          . '<table role="presentation" cellspacing="0" cellpadding="0" style="max-width:640px;margin:auto;background:#ffffff;border-radius:12px;overflow:hidden">'
          . '<tr><td style="background:linear-gradient(135deg,#1e3c72,' . $brand . ');padding:24px;color:#fff">'
          . '<h2 style="margin:0">Inventory Management System</h2>'
          . '<div style="opacity:.9;">Welcome to IMS</div>'
          . '</td></tr>'
          . '<tr><td style="padding:24px;color:#2c3e50">'
          . '<p>Hi ' . htmlspecialchars(build_full_name($first, $middle, $last, $ext)) . ',</p>'
          . '<p>Your account has been created. Use the credentials below to sign in.</p>'
          . '<div style="border:1px solid #e6e9ef;border-radius:10px;padding:16px;margin:12px 0">'
          . '<div><strong>Username:</strong> ' . htmlspecialchars($username) . '</div>'
          . '<div><strong>Password:</strong> ' . htmlspecialchars($plainPassword) . '</div>'
          . '</div>'
          . '<p>You can sign in at <a href="' . (require __DIR__ . '/../../config/app.php')['app_url'] . '/public/index.php" style="color:' . $brand . ';text-decoration:none">IMS Login</a>.</p>'
          . '<p style="font-size:12px;color:#6c757d">For security, please change your password after logging in.</p>'
          . '</td></tr>'
          . '<tr><td style="background:#f0f3fa;color:#6c757d;padding:16px;text-align:center;font-size:12px">'
          . '&copy; ' . date('Y') . ' Inventory Management System</td></tr>'
          . '</table></div>';

    $mail->Body = $html;
    $mail->AltBody = "Your IMS account\nUsername: $username\nPassword: $plainPassword";
    $sendOk = $mail->send();
  } catch (Throwable $ex) {
    $sendErr = $ex->getMessage();
  }

  $msg = 'User created';
    if (!$sendOk) { $msg .= ' (email failed to send)'; }
  // Log create
  log_action($pdo, 'create', 'user', $newId, [
    'username' => $username,
    'email' => $email,
    'role_id' => $role_id
  ]);
  json_ok(
    [
      'user_id' => $newId,
      'username' => $username,
      'full_name' => build_full_name($first, $middle, $last, $ext)
    ],
    $msg,
    build_debug('create_user_ok', $config, [
      'new_id' => $newId,
      'generated_username' => $username,
      'generated_password_plain' => $config['debug'] ? $plainPassword : null,
      'email_send_success' => $sendOk,
      'email_send_error' => $sendErr ?: null
    ])
  );
}

function update_user(PDO $pdo, $config) {
  $id = get_int('user_id');
  if ($id <= 0) json_err('Invalid user', build_debug('update_invalid_id', $config));

  $username = get_str('username');
  $email = get_str('email');
  $role_id = get_int('role_id');
  $first = get_str('first_name');
  $middle = get_str('middle_name');
  $last = get_str('last_name');
  $ext = get_str('extension');
  $password = get_str('password'); // optional
  $confirm = get_str('confirm_password');

  if ($username === '' || $email === '' || $role_id <= 0 || $first === '' || $last === '') {
    json_err('Please fill in required fields', build_debug('update_missing_fields', $config));
  }

  // Uniqueness checks excluding current user
  $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND user_id <> ?');
  $stmt->execute([$username, $email, $id]);
  $conflict = (int)$stmt->fetchColumn();
  if ($conflict > 0) json_err('Username or email already exists', build_debug('update_conflict', $config, ['user_id' => $id, 'username' => $username, 'email' => $email]));

  if ($password !== '') {
  if ($password !== $confirm) json_err('Password mismatch', build_debug('update_password_mismatch', $config));
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('UPDATE users SET username=?, email=?, password=?, role_id=?, first_name=?, middle_name=?, last_name=?, extension=? WHERE user_id=?');
    $stmt->execute([$username,$email,$hash,$role_id,$first,$middle,$last,$ext,$id]);
  } else {
    $stmt = $pdo->prepare('UPDATE users SET username=?, email=?, role_id=?, first_name=?, middle_name=?, last_name=?, extension=? WHERE user_id=?');
    $stmt->execute([$username,$email,$role_id,$first,$middle,$last,$ext,$id]);
  }

  // Log update
  log_action($pdo, 'update', 'user', $id, [
    'username' => $username,
    'email' => $email,
    'role_id' => $role_id
  ]);

  json_ok([
      'user_id' => $id,
      'username' => $username,
      'full_name' => build_full_name($first, $middle, $last, $ext)
    ], 'User updated', build_debug('update_user_ok', $config, ['user_id' => $id]));
}

function archive_user(PDO $pdo, $config) {
  $id = get_int('user_id');
  if ($id <= 0) json_err('Invalid user', build_debug('archive_invalid_id', $config));
  $stmt = $pdo->prepare('DELETE FROM users WHERE user_id = ?');
  $stmt->execute([$id]);
  // Log archive (destructive)
  log_action($pdo, 'archive', 'user', $id, 'User deleted (archive)');
  json_ok(['user_id' => $id], 'User archived', build_debug('archive_user_ok', $config, ['user_id' => $id]));
}
