<?php $config = require __DIR__ . '/../../config/app.php'; $appUrl = $config['app_url']; ?>
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">User Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex align-items-center mb-3">
          <img id="view_avatar" src="<?= $appUrl ?>/public/images/default_profile_pic.png" width="64" height="64" style="object-fit:cover;border-radius:50%;box-shadow:0 0 0 4px rgba(65,105,225,.15)" alt="avatar">
          <div class="ms-3">
            <h6 id="view_full_name" class="mb-1"></h6>
            <div class="small text-muted" id="view_role"></div>
          </div>
        </div>
        <table class="table table-sm">
          <tbody>
            <tr><th>Username</th><td id="view_username"></td></tr>
            <tr><th>Email</th><td id="view_email"></td></tr>
            <tr><th>Extension</th><td id="view_extension"></td></tr>
            <tr><th>Created</th><td id="view_created"></td></tr>
            <tr><th>Updated</th><td id="view_updated"></td></tr>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
