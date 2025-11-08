<?php
// 003_seed_users.php
require_once __DIR__ . '/../db_connection.php'; // adjust path if needed

try {
    // Default users data
    $users = [
        [
            'email' => 'admin@gmail.com',
            'username' => 'admin',
            'password' => 'Christina_828',
            'role_id' => 1, // admin
            'first_name' => 'Admin',
            'middle_name' => null,
            'last_name' => 'User',
            'extension' => null
        ],
        [
            'email' => 'manager@gmail.com',
            'username' => 'manager',
            'password' => 'Christina_828',
            'role_id' => 2, // manager
            'first_name' => 'Manager',
            'middle_name' => null,
            'last_name' => 'User',
            'extension' => null
        ],
        [
            'email' => 'operator@gmail.com',
            'username' => 'operator',
            'password' => 'Christina_828',
            'role_id' => 3, // operator
            'first_name' => 'Operator',
            'middle_name' => null,
            'last_name' => 'User',
            'extension' => null
        ]
    ];

    
        // Upsert so running the seeder multiple times refreshes credentials safely
        $stmt = $pdo->prepare("
            INSERT INTO users 
            (email, username, password, role_id, first_name, middle_name, last_name, extension)
            VALUES (:email, :username, :password, :role_id, :first_name, :middle_name, :last_name, :extension)
            ON DUPLICATE KEY UPDATE
                password = VALUES(password),
                role_id = VALUES(role_id),
                first_name = VALUES(first_name),
                middle_name = VALUES(middle_name),
                last_name = VALUES(last_name),
                extension = VALUES(extension)
        ");

    foreach ($users as $user) {
        $stmt->execute([
            ':email' => $user['email'],
            ':username' => $user['username'],
            ':password' => password_hash($user['password'], PASSWORD_BCRYPT),
            ':role_id' => $user['role_id'],
            ':first_name' => $user['first_name'],
            ':middle_name' => $user['middle_name'],
            ':last_name' => $user['last_name'],
            ':extension' => $user['extension']
        ]);
    }

    echo "Default users upserted successfully.";

} catch (PDOException $e) {
    die("Seeding failed: " . $e->getMessage());
}
