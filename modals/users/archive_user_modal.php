<?php $config = require __DIR__ . '/../../config/app.php'; $appUrl = $config['app_url']; ?>
<div class="modal fade" id="archiveUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title text-danger">Archive User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3" id="archive_user_message">Are you sure you want to archive this user?</p>
        <form id="archiveUserForm">
          <input type="hidden" name="user_id" id="archive_user_id">
        </form>
        <div class="alert alert-warning small mb-0"><i class="bi bi-exclamation-triangle me-1"></i>This will permanently delete the record (no soft delete yet).</div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="confirmArchiveBtn" class="btn btn-danger">Archive</button>
      </div>
    </div>
  </div>
</div>
