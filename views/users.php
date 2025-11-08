<?php
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
                                    $pic = $u['profile_picture'] ?? '';
                                    $avatarUrl = $pic ? $pic : ($appUrl . '/public/images/default_profile_pic.png');
                                ?>
                                <tr data-id="<?= (int)$u['user_id'] ?>">
                                    <td>
                                        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="avatar" width="40" height="40" style="object-fit:cover;border-radius:50%">
                                    </td>
                                    <td><?= htmlspecialchars($fullName) ?></td>
                                    <td><?= htmlspecialchars($u['username']) ?></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td><span class="badge bg-primary-subtle text-primary"><?= htmlspecialchars($u['role_name']) ?></span></td>
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
    <!-- SweetAlert2 -->
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

            // Add user
            $('#btnAddUser').on('click', function(){
                const modal = new bootstrap.Modal(document.getElementById('addUserModal'));
                document.getElementById('addUserForm').reset();
                modal.show();
            });

                        $('#addUserForm').on('submit', function(e){
                                e.preventDefault();
                                const fd = new FormData(this);
                                fd.append('action','create_user');
                                                fetch(backend, { method:'POST', body: fd })
                                                    .then(r=>r.json()).then(j=>{
                                                        console.debug('create_user response:', j);
                                                        if(j.success){
                                                            Swal.fire({
                                                                icon: 'success',
                                                                title: 'User Created',
                                                                html: 'The user has been created successfully.' + (j.message?.includes('email failed') ? '<br><small class="text-muted">Email did not send.</small>' : ''),
                                                                confirmButtonColor: '#4169e1'
                                                            }).then(()=>location.reload());
                                                        } else {
                                                            Swal.fire({ icon: 'error', title: 'Create Failed', text: j.message || 'Failed to create user', confirmButtonColor: '#4169e1' });
                                                            if (j.debug) console.debug('create_user debug:', j.debug);
                                                        }
                                                    }).catch((err)=>{ console.error('create_user error:', err); Swal.fire({ icon:'error', title:'Request failed' }); });
                        });

            // Actions dropdown handling via delegation
            $('#usersTable').on('click', '.dropdown-item', function(e){
                e.preventDefault();
                const $row = $(this).closest('tr');
                const username = $row.find('td:nth-child(3)').text().trim();
                const userId = $row.data('user-id') || $(this).data('user-id');
            });

            // Attach data-user-id to each row for convenience
            $('#usersTable tbody tr').each(function(){
                const idCellIndex = 3; // no explicit id column; fetch from data attribute injected below
            });

            // Wire specific actions
            $('#usersTable').on('click', '.act-view', function(){
                const id = $(this).data('id');
                const fd = new FormData(); fd.append('action','get_user'); fd.append('user_id', id);
                fetch(backend, { method:'POST', body: fd }).then(r=>r.json()).then(j=>{
                    if(!j.success) return alert(j.message||'Not found');
                    const u=j.data;
                    const mi = u.middle_name ? (u.middle_name.substring(0,1).toUpperCase()+'.') : '';
                    const full = [u.first_name, mi, u.last_name].filter(Boolean).join(' ');
                    $('#view_full_name').text(full);
                    $('#view_role').text(u.role_name);
                    $('#view_username').text(u.username);
                    $('#view_email').text(u.email);
                    $('#view_extension').text(u.extension || '');
                    $('#view_created').text(u.created_at || '');
                    $('#view_updated').text(u.updated_at || '');
                    const pic = u.profile_picture || (appUrl + '/public/images/default_profile_pic.png');
                    $('#view_avatar').attr('src', pic);
                    new bootstrap.Modal(document.getElementById('viewUserModal')).show();
                }).catch(()=>alert('Request failed'));
            });

            $('#usersTable').on('click', '.act-edit', function(){
                const id = $(this).data('id');
                const fd = new FormData(); fd.append('action','get_user'); fd.append('user_id', id);
                fetch(backend, { method:'POST', body: fd }).then(r=>r.json()).then(j=>{
                    if(!j.success) return alert(j.message||'Not found');
                    const u=j.data;
                    $('#edit_user_id').val(u.user_id);
                    $('#edit_username').val(u.username);
                    $('#edit_email').val(u.email);
                    $('#edit_role_id').val(u.role_id);
                    $('#edit_first_name').val(u.first_name);
                    $('#edit_middle_name').val(u.middle_name);
                    $('#edit_last_name').val(u.last_name);
                    $('#edit_extension').val(u.extension);
                    new bootstrap.Modal(document.getElementById('editUserModal')).show();
                }).catch(()=>alert('Request failed'));
            });

            $('#editUserForm').on('submit', function(e){
                e.preventDefault();
                const fd = new FormData(this);
                fd.append('action','update_user');
                fetch(backend, { method:'POST', body: fd }).then(r=>r.json()).then(j=>{
                    console.debug('update_user response:', j);
                    if(j.success){ Swal.fire({ icon:'success', title:'User Updated', confirmButtonColor: '#4169e1' }).then(()=>location.reload()); }
                    else { Swal.fire({ icon:'error', title:'Update Failed', text:j.message||'Update failed', confirmButtonColor: '#4169e1' }); if(j.debug) console.debug('update_user debug:', j.debug); }
                }).catch((err)=>{ console.error('update_user error:', err); Swal.fire({ icon:'error', title:'Request failed' }); });
            });

            $('#usersTable').on('click', '.act-archive', function(){
                const id = $(this).data('id');
                $('#archive_user_id').val(id);
                new bootstrap.Modal(document.getElementById('archiveUserModal')).show();
            });
            $('#confirmArchiveBtn').on('click', function(){
                const id = $('#archive_user_id').val();
                const fd = new FormData(); fd.append('action','archive_user'); fd.append('user_id', id);
                fetch(backend, { method:'POST', body: fd }).then(r=>r.json()).then(j=>{
                    console.debug('archive_user response:', j);
                    if(j.success){ Swal.fire({ icon:'success', title:'User Archived', confirmButtonColor: '#4169e1' }).then(()=>location.reload()); }
                    else { Swal.fire({ icon:'error', title:'Archive Failed', text:j.message||'Archive failed', confirmButtonColor: '#4169e1' }); if(j.debug) console.debug('archive_user debug:', j.debug); }
                }).catch((err)=>{ console.error('archive_user error:', err); Swal.fire({ icon:'error', title:'Request failed' }); });
            });
        });
    </script>
</main>

<?php require_once __DIR__ . '/../global/footer.php'; ?>