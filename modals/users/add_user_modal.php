<?php $config = require __DIR__ . '/../../config/app.php'; $appUrl = $config['app_url']; ?>
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add New User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addUserForm">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Role</label>
              <select name="role_id" class="form-select" required>
                <option value="">Select role</option>
                <option value="1">admin</option>
                <option value="2">manager</option>
                <option value="3">operator</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">First Name</label>
              <input type="text" name="first_name" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Middle Name</label>
              <input type="text" name="middle_name" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Last Name</label>
              <input type="text" name="last_name" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Extension</label>
              <select name="extension" class="form-select">
                <option value="">None</option>
                <option value="Jr.">Jr.</option>
                <option value="Sr.">Sr.</option>
                <option value="II">II</option>
                <option value="III">III</option>
                <option value="IV">IV</option>
              </select>
            </div>
            <div class="col-12">
              <div class="alert alert-info small m-0"><i class="bi bi-info-circle me-1"></i> Username and a secure password will be generated automatically and emailed to the user.</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
