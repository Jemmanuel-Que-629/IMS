<?php
$pageTitle = "User Management - IMS";
session_start();
require_once __DIR__ . '/../helpers/auth.php';
require_role(['admin']);

$config = require __DIR__ . '/../config/app.php';
$appUrl = $config['app_url'];

// Fetch users with roles
$users = [];
try {
    require_once __DIR__ . '/../database/db_connection.php';
    $stmt = $pdo->query("SELECT u.*, r.role_name FROM users u INNER JOIN roles r ON u.role_id = r.role_id ORDER BY u.user_id ASC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $users[] = $row;
    }
} catch (Throwable $e) {
    $users = [];
}
?>
<?php require_once __DIR__ . '/../global/header.php'; ?>
<?php require_once __DIR__ . '/../global/sidebar.php'; ?>

<main class="content">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="page-title m-0">User Management</div>
        <button class="btn btn-primary" id="btnAddUser"><i class="bi bi-person-plus me-1"></i>Add New User</button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="usersTable" class="table table-striped align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th>Avatar</th>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No users found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                                <?php
                                    $fn = $u['first_name'] ?? '';
                                    $mn = $u['middle_name'] ?? '';
                                    $ln = $u['last_name'] ?? '';
                                    $mi = $mn ? mb_strtoupper(mb_substr($mn, 0, 1)) . '.' : '';
                                    $fullName = trim($fn . ' ' . $mi . ' ' . $ln);
                                    $pic = $u['profile_pic'] ?? '';
                                    // If a stored profile picture exists (e.g., /uploads/images/profile_pic/xxx.jpg),
                                    // prefix it with $appUrl to form an absolute URL; otherwise use default image
                                    $avatarUrl = $pic ? ($appUrl . $pic) : ($appUrl . '/public/images/default_profile_pic.png');
                                    $roleFormatted = ucfirst(strtolower($u['role_name'])); // Capitalize first letter
                                ?>
                                <tr data-id="<?= (int)$u['user_id'] ?>">
                                    <td>
                                        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="avatar" width="40" height="40" style="object-fit:cover;border-radius:50%">
                                    </td>
                                    <td><?= htmlspecialchars($fullName) ?></td>
                                    <td><?= htmlspecialchars($u['username']) ?></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td><span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($roleFormatted) ?></span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-link text-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item act-view" data-id="<?= (int)$u['user_id'] ?>" href="#"><i class="bi bi-eye me-2"></i>View</a></li>
                                                <li><a class="dropdown-item act-edit" data-id="<?= (int)$u['user_id'] ?>" href="#"><i class="bi bi-pencil-square me-2"></i>Edit</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger act-archive" data-id="<?= (int)$u['user_id'] ?>" href="#"><i class="bi bi-archive me-2"></i>Archive</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <?php require_once __DIR__ . '/../modals/users/add_user_modal.php'; ?>
    <?php require_once __DIR__ . '/../modals/users/edit_user_modal.php'; ?>
    <?php require_once __DIR__ . '/../modals/users/view_user_modal.php'; ?>
    <?php require_once __DIR__ . '/../modals/users/archive_user_modal.php'; ?>

    <!-- Page-specific scripts: jQuery and DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(function(){
            $('#usersTable').DataTable({
                pageLength: 10,
                lengthChange: false,
                order: [[1, 'asc']]
            });

            const appUrl = <?= json_encode($appUrl) ?>;
            const backend = appUrl + '/backend/users/unified_users_management.php';

            $('#btnAddUser').on('click', function(){
                const modal = new bootstrap.Modal(document.getElementById('addUserModal'));
                document.getElementById('addUserForm').reset();
                modal.show();
            });
        });
    </script>
</main>

<?php require_once __DIR__ . '/../global/footer.php'; ?>
