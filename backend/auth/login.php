<?php
session_start();
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../helpers/logger.php';

// Set JSON header for AJAX responses
header('Content-Type: application/json');

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Check if login form was submitted
if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    // Validate input
    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please enter both username and password.'
        ]);
        exit;
    }
    
    try {
    // Load database connection
    require_once __DIR__ . '/../../database/db_connection.php';
        // DEVELOPMENT DIAGNOSTICS: verify active DB and user count
        $cfg = require __DIR__ . '/../../config/app.php';
        $connected_db = null;
        $users_count = null;
        if ($cfg['debug']) {
            try {
                $connected_db = $pdo->query('SELECT DATABASE()')->fetchColumn();
                // If users table doesn't exist, this will throw and be caught below
                $users_count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
            } catch (Throwable $t) {
                // ignore, will be reported via main catch if fatal
            }
        }

        // Check if $pdo is available
        if (!isset($pdo)) {
            throw new Exception('Database connection not available');
        }
        
        // Prepare SQL to find user by username or email
        $sql = "SELECT u.*, r.role_name 
                FROM users u 
                INNER JOIN roles r ON u.role_id = r.role_id 
                WHERE (u.username = ? OR u.email = ?)
                LIMIT 1";
        
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
    // Verify user exists and password is correct
    $user_found = $user ? true : false;
    $password_match = $user_found ? password_verify($password, $user['password']) : false;

    if ($user_found && $password_match) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_role'] = $user['role_name'];
            $_SESSION['full_name'] = trim($user['first_name'] . ' ' . 
                                         ($user['middle_name'] ? $user['middle_name'] . ' ' : '') . 
                                         $user['last_name'] . 
                                         ($user['extension'] ? ' ' . $user['extension'] : ''));
            
            // Set remember me cookie if checked (30 days)
            if ($remember_me) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
                // In production, store this token in database associated with user
            }
            
            // Set success message
            $_SESSION['success_message'] = 'Welcome back, ' . $user['first_name'] . '!';
            
            // Log success for debugging
            error_log('Login Success: ' . json_encode([
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'role' => $user['role_name'],
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            
            // Log success login
            if (isset($pdo)) {
                log_action($pdo, 'login_success', 'auth', (int)$user['user_id'], [ 'username' => $user['username'] ]);
            }

            // Return success response
            echo json_encode([
                'success' => true,
                'message' => 'Welcome back, ' . $user['first_name'] . '!',
                // Use absolute redirect URL based on app config
                'redirect' => (require __DIR__ . '/../../config/app.php')['app_url'] . '/views/dashboard.php',
                'user' => [
                    'id' => $user['user_id'],
                    'username' => $user['username'],
                    'role' => $user['role_name'],
                    'full_name' => $_SESSION['full_name']
                ],
                // Include debug hints only in development
                'debug' => $cfg['debug'] ? [
                    'user_found' => $user_found,
                    'password_match' => $password_match,
                    'db' => $connected_db,
                    'users_count' => $users_count,
                    'input_username' => $username
                ] : null
            ]);
            exit;
            
        } else {
            // Invalid credentials
            error_log('Login Failed: ' . json_encode([
                'username' => $username,
                'reason' => 'Invalid credentials',
                'timestamp' => date('Y-m-d H:i:s')
            ]));
            // Log failed login attempt
            if (isset($pdo)) {
                $entityId = ($user_found && isset($user['user_id'])) ? (int)$user['user_id'] : null;
                log_action($pdo, 'login_failed', 'auth', $entityId, [ 'attempted' => $username ]);
            }
            
            echo json_encode([
                'success' => false,
                'message' => 'Invalid username or password. Please try again.',
                'debug' => $cfg['debug'] ? [
                    'user_found' => $user_found,
                    'password_match' => $password_match,
                    'db' => $connected_db,
                    'users_count' => $users_count,
                    'input_username' => $username
                ] : null
            ]);
            exit;
        }
        
    } catch (PDOException $e) {
        // Log error and show generic message
        error_log('Login Database Error: ' . json_encode([
            'error' => $e->getMessage(),
            'code' => $e->getCode(),
            'username' => $username ?? 'N/A',
            'timestamp' => date('Y-m-d H:i:s')
        ]));
        
        $config = require __DIR__ . '/../../config/app.php';
        echo json_encode([
            'success' => false,
            'message' => $config['debug'] ? 'Database error: ' . $e->getMessage() : 'An error occurred. Please try again later.',
            'debug' => $config['debug'] ? [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ] : null
        ]);
        exit;
    } catch (Exception $e) {
        // Log error and show generic message
        error_log('Login Error: ' . json_encode([
            'error' => $e->getMessage(),
            'username' => $username ?? 'N/A',
            'timestamp' => date('Y-m-d H:i:s')
        ]));
        
        $config = require __DIR__ . '/../../config/app.php';
        echo json_encode([
            'success' => false,
            'message' => $config['debug'] ? 'Error: ' . $e->getMessage() : 'An error occurred. Please try again later.',
            'debug' => $config['debug'] ? ['error' => $e->getMessage()] : null
        ]);
        exit;
    }
}

// If we get here, invalid request
echo json_encode([
    'success' => false,
    'message' => 'Invalid request'
]);
exit;
