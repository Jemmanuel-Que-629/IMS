<?php
require_once __DIR__ . '/../helpers/auth.php';
require_login();
include __DIR__ . '/../global/header.php';
include __DIR__ . '/../global/sidebar.php';
$appUrlEsc = htmlspecialchars($appUrl, ENT_QUOTES, 'UTF-8');
$user = get_logged_in_user();
?>
<style>
.profile-page-wrapper { max-width:1280px; margin:0 auto; }
.profile-layout { display:grid; grid-template-columns: 360px 1fr; gap:24px; }
@media (max-width: 991.98px){ .profile-layout { grid-template-columns: 1fr; } }
.card-soft { background:#fff; border:1px solid #e3e7ef; border-radius:14px; box-shadow:0 2px 4px rgba(15,23,42,.04),0 4px 16px -2px rgba(15,23,42,.06); }
.card-soft-header { padding:20px 22px 0 22px; }
.card-soft-body { padding:20px 22px 24px 22px; }
.avatar-xl { width:170px; height:170px; border-radius:50%; object-fit:cover; background:#f1f3f7; border:4px solid #fff; box-shadow:0 0 0 3px #2563eb30; }
.avatar-wrapper { position:relative; display:inline-block; }
.avatar-edit-trigger { position:absolute; right:8px; bottom:8px; border-radius:50%; width:42px; height:42px; display:flex; align-items:center; justify-content:center; background:#2563eb; color:#fff; border:0; box-shadow:0 4px 10px -2px rgba(37,99,235,.5); }
.avatar-edit-trigger:hover { filter:brightness(1.08); }
.divider { border-top:1px solid #e5e9f2; margin:20px 0 18px; }
.muted { color:#64748b; }
.badge-soft { background:#eef2ff; color:#4338ca; font-size:.65rem; padding:4px 8px; border-radius:20px; text-transform:uppercase; letter-spacing:.5px; font-weight:600; }
.info-grid { display:grid; grid-template-columns: repeat(auto-fit,minmax(200px,1fr)); gap:14px 18px; }
.info-field label { display:block; font-size:.7rem; font-weight:600; letter-spacing:.5px; text-transform:uppercase; color:#64748b; margin-bottom:4px; }
.info-field .value-box { background:#f8fafc; border:1px solid #e2e8f0; padding:10px 12px; border-radius:8px; font-size:.9rem; font-weight:500; }
.upload-zone { border:2px dashed #cbd5e1; padding:14px; border-radius:12px; background:#f8fafc; text-align:center; cursor:pointer; transition:.25s; }
.upload-zone:hover { background:#f1f5f9; }
.btn-primary-gradient { background:linear-gradient(135deg,#2563eb,#1d4ed8); border:0; }
.btn-primary-gradient:hover { filter:brightness(1.1); }
.pw-help { font-size:.7rem; color:#64748b; line-height:1.3; }
.password-strength { font-size:.7rem; font-weight:600; margin-top:4px; }
.strength-1 { color:#dc2626; } .strength-2 { color:#f97316; } .strength-3 { color:#d97706; } .strength-4 { color:#059669; } .strength-5 { color:#0284c7; }
.toggle-password-btn { position:absolute; right:6px; top:50%; transform:translateY(-50%); background:transparent; border:0; color:#64748b; }
.relative { position:relative; }
</style>
<main class="content p-3 p-md-4">
	<div class="profile-page-wrapper">
		<div class="mb-3 d-flex justify-content-between align-items-center">
			<h4 class="mb-0">My Profile</h4>
		</div>
		<div class="profile-layout">
			<!-- Left Column: Avatar & Contact -->
			<div class="card-soft">
				<div class="card-soft-body">
					<div class="text-center mb-3">
						<div class="avatar-wrapper">
							<img id="profileAvatar" src="<?= $appUrlEsc ?>/public/images/default-avatar.png" alt="Avatar" class="avatar-xl" />
							<button class="avatar-edit-trigger" id="changePicBtn" title="Change picture"><i class="bi bi-camera-fill"></i></button>
						</div>
						<h5 class="mt-3 mb-1" id="profileFullName"><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></h5>
						<div class="small muted">@<?= htmlspecialchars($user['username']) ?> • <?= htmlspecialchars($user['email']) ?></div>
						<div class="divider"></div>
						<div class="upload-zone" id="uploadZone">
							<div class="small mb-1"><i class="bi bi-cloud-upload me-1"></i><strong>Select Image</strong> or Drop Here</div>
							<div class="text-muted" style="font-size:.7rem">JPG, PNG, GIF, WEBP up to 5MB</div>
							<input type="file" id="profilePicInput" accept="image/*" class="d-none" />
						</div>
						<div class="mt-3 d-flex gap-2 justify-content-center">
							<button class="btn btn-sm btn-secondary" id="cancelPicBtn" disabled>Cancel</button>
							<button class="btn btn-sm btn-primary-gradient text-white" id="uploadPicBtn" disabled><i class="bi bi-upload me-1"></i>Upload</button>
						</div>
					</div>
				</div>
			</div>
			<!-- Right Column: Details & Password -->
			<div class="d-flex flex-column gap-3">
				<div class="card-soft">
					<div class="card-soft-header">
						<span class="badge-soft">Account Summary</span>
					</div>
					<div class="card-soft-body">
						<div class="info-grid" id="profileInfoGrid">
							<div class="info-field"><label>Full Name</label><div class="value-box" id="infoFullName"><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></div></div>
							<div class="info-field"><label>Username</label><div class="value-box"><?= htmlspecialchars($user['username']) ?></div></div>
							<div class="info-field"><label>Email</label><div class="value-box"><?= htmlspecialchars($user['email']) ?></div></div>
							<div class="info-field"><label>Role</label><div class="value-box"><?= htmlspecialchars($_SESSION['user_role'] ?? '') ?></div></div>
							<div class="info-field"><label>User ID</label><div class="value-box">#<?= (int)($_SESSION['user_id'] ?? 0) ?></div></div>
							<div class="info-field"><label>Joined</label><div class="value-box" id="infoJoined">—</div></div>
						</div>
					</div>
				</div>
				<div class="card-soft">
					<div class="card-soft-header d-flex justify-content-between align-items-center">
						<span class="badge-soft">Change Password</span>
					</div>
					<div class="card-soft-body">
						<form id="changePasswordForm" autocomplete="off" class="row g-3">
							<div class="col-12 col-md-6">
								<label class="form-label small fw-semibold">Current Password</label>
								<div class="relative"><input type="password" name="current_password" class="form-control" required /><button type="button" class="toggle-password-btn" data-target="current_password"><i class="bi bi-eye"></i></button></div>
							</div>
							<div class="col-12 col-md-6"></div>
							<div class="col-12 col-md-6">
								<label class="form-label small fw-semibold">New Password</label>
								<div class="relative"><input type="password" name="new_password" id="newPasswordInput" class="form-control" minlength="8" required /><button type="button" class="toggle-password-btn" data-target="new_password"><i class="bi bi-eye"></i></button></div>
								<div class="password-strength" id="passwordStrength"></div>
							</div>
							<div class="col-12 col-md-6">
								<label class="form-label small fw-semibold">Confirm New Password</label>
								<div class="relative"><input type="password" name="confirm_password" class="form-control" required /><button type="button" class="toggle-password-btn" data-target="confirm_password"><i class="bi bi-eye"></i></button></div>
							</div>
							<div class="col-12">
								<div class="pw-help">Must include at least 1 uppercase, 1 lowercase, 1 number, and 1 special from: ! @ # $ % ^ & * ( ) _ - + = { } [ ] : ; , . ? /</div>
							</div>
							<div class="col-12 mt-2">
								<button type="submit" class="btn btn-primary-gradient text-white"><i class="bi bi-shield-lock-fill me-1"></i>Update Password</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const appUrl = <?= json_encode($appUrl) ?>;
function toast(icon, title){
	Swal.fire({ toast:true, position:'top-end', icon, title, showConfirmButton:false, timer:1800, timerProgressBar:true });
}

// Fetch initial profile to set avatar + joined date
fetch(appUrl + '/backend/profile/unified_profile_management.php?action=get_profile')
  .then(r => r.json())
  .then(res => { if(res.success && res.data && res.data.user){ const u = res.data.user; if(u.profile_pic){ document.getElementById('profileAvatar').src = appUrl + u.profile_pic; } if(u.created_at){ const dt = new Date(u.created_at); document.getElementById('infoJoined').textContent = dt.toLocaleDateString(undefined,{ year:'numeric', month:'short', day:'numeric' }); } } });

// Avatar selection & deferred upload
const profilePicInput = document.getElementById('profilePicInput');
const changePicBtn = document.getElementById('changePicBtn');
const uploadZone = document.getElementById('uploadZone');
const uploadBtn = document.getElementById('uploadPicBtn');
const cancelBtn = document.getElementById('cancelPicBtn');
let pendingFile = null;

function resetPending(){ pendingFile=null; uploadBtn.disabled=true; cancelBtn.disabled=true; profilePicInput.value=''; }

function handleFile(file){
	const allowed = ['image/jpeg','image/png','image/gif','image/webp'];
	if(!allowed.includes(file.type)){ toast('error','Invalid file type'); return; }
	if(file.size > 5*1024*1024){ toast('error','File exceeds 5MB'); return; }
	pendingFile = file;
	// Preview
	const reader = new FileReader();
	reader.onload = e => { document.getElementById('profileAvatar').src = e.target.result; uploadBtn.disabled=false; cancelBtn.disabled=false; };
	reader.readAsDataURL(file);
}

changePicBtn.addEventListener('click', () => profilePicInput.click());
uploadZone.addEventListener('click', () => profilePicInput.click());
profilePicInput.addEventListener('change', function(){ if(this.files && this.files[0]) handleFile(this.files[0]); });

uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('border-primary'); });
uploadZone.addEventListener('dragleave', e => { e.preventDefault(); uploadZone.classList.remove('border-primary'); });
uploadZone.addEventListener('drop', e => { e.preventDefault(); uploadZone.classList.remove('border-primary'); if(e.dataTransfer.files && e.dataTransfer.files[0]) handleFile(e.dataTransfer.files[0]); });

cancelBtn.addEventListener('click', () => { resetPending(); fetch(appUrl + '/backend/profile/unified_profile_management.php?action=get_profile').then(r=>r.json()).then(res=>{ if(res.success && res.data.user && res.data.user.profile_pic){ document.getElementById('profileAvatar').src = appUrl + res.data.user.profile_pic; } else { document.getElementById('profileAvatar').src = '<?= $appUrlEsc ?>/public/images/default-avatar.png'; } }); });

uploadBtn.addEventListener('click', () => {
	if(!pendingFile){ toast('warning','No image selected'); return; }
	const fd = new FormData();
	fd.append('action','upload_profile_pic');
	fd.append('profile_pic', pendingFile);
	uploadBtn.disabled=true;
	fetch(appUrl + '/backend/profile/unified_profile_management.php', { method:'POST', body: fd })
		.then(r => r.json())
		.then(res => {
			if(res.success){
				toast('success','Profile picture updated');
				resetPending();
				// Ensure final path loaded (avoid caching)
				document.getElementById('profileAvatar').src = appUrl + res.data.profile_pic + '?v=' + Date.now();
			} else { toast('error', res.message || 'Upload failed'); }
		})
		.catch(err => { toast('error', err.message); })
		.finally(()=>{ uploadBtn.disabled=false; cancelBtn.disabled=true; });
});

// Password strength indicator
const strengthEl = document.getElementById('passwordStrength');
document.getElementById('newPasswordInput').addEventListener('input', function(){
	const v = this.value;
	let score = 0;
	if(v.length >= 8) score++;
	if(/[A-Z]/.test(v)) score++;
	if(/[a-z]/.test(v)) score++;
	if(/\d/.test(v)) score++;
	if(/[^A-Za-z0-9]/.test(v)) score++;
	const levels = ['Very Weak','Weak','Okay','Good','Strong'];
	strengthEl.textContent = v ? ('Strength: ' + levels[score-1]) : '';
	strengthEl.className = 'password-strength strength-' + score;
});

// Toggle password visibility
document.querySelectorAll('.toggle-password-btn').forEach(btn => {
	btn.addEventListener('click', () => {
		const targetName = btn.getAttribute('data-target');
		let input;
		if(targetName === 'current_password') input = document.querySelector('input[name="current_password"]');
		if(targetName === 'new_password') input = document.querySelector('input[name="new_password"]');
		if(targetName === 'confirm_password') input = document.querySelector('input[name="confirm_password"]');
		if(!input) return;
		input.type = input.type === 'password' ? 'text' : 'password';
		btn.querySelector('i').classList.toggle('bi-eye');
		btn.querySelector('i').classList.toggle('bi-eye-slash');
	});
});

// Change password form
document.getElementById('changePasswordForm').addEventListener('submit', function(e){
	e.preventDefault();
	const fd = new FormData(this);
	fd.append('action','change_password');
	fetch(appUrl + '/backend/profile/unified_profile_management.php', { method:'POST', body: fd })
		.then(r => r.json())
		.then(res => {
			if(res.success){
				this.reset();
				strengthEl.textContent='';
				toast('success','Password changed');
			} else {
				toast('error', res.message || 'Failed to change password');
			}
		})
		.catch(err => toast('error', err.message));
});
</script>
<?php include __DIR__ . '/../global/footer.php'; ?>
