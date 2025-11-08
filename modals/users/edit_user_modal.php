<?php $config = require __DIR__ . '/../../config/app.php'; $appUrl = $config['app_url']; ?>
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editUserForm">
        <input type="hidden" name="user_id" id="edit_user_id">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Username</label>
              <input type="text" name="username" id="edit_username" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Email</label>
              <input type="email" name="email" id="edit_email" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Role</label>
              <select name="role_id" id="edit_role_id" class="form-select" required>
                <option value="1">admin</option>
                <option value="2">manager</option>
                <option value="3">operator</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">First Name</label>
              <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Middle Name</label>
              <input type="text" name="middle_name" id="edit_middle_name" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Last Name</label>
              <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Extension</label>
              <input type="text" name="extension" id="edit_extension" class="form-control" placeholder="Jr., Sr., III">
            </div>
            <div class="col-md-4">
              <label class="form-label">New Password (optional)</label>
              <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
            </div>
            <div class="col-md-4">
              <label class="form-label">Confirm Password</label>
              <input type="password" name="confirm_password" class="form-control" placeholder="Repeat if changing">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>
