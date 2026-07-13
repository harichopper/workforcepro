<?php declare(strict_types=1);
$ctrl = new ProfileController();
$profileData = $ctrl->get();
$u = $profileData['data'] ?? [];
?>
<div class="page-header"><h1>My Profile</h1></div>
<div class="row g-4">
  <div class="col-md-4">
    <div class="card">
      <div class="card-body text-center py-4">
        <div id="avatarWrap" style="margin:0 auto 16px;width:100px;height:100px;border-radius:50%;overflow:hidden;border:3px solid var(--primary)">
          <?php if(!empty($u['avatar'])): ?>
            <img id="profileAvatarImg" src="/uploads/<?= e($u['avatar']) ?>" style="width:100%;height:100%;object-fit:cover">
          <?php else: ?>
            <div id="profileAvatarImg" style="width:100%;height:100%;background:var(--primary);display:grid;place-items:center;font-size:2.5rem;font-weight:700;color:#fff"><?= strtoupper(substr($u['name']??'A',0,1)) ?></div>
          <?php endif; ?>
        </div>
        <h5 class="fw-600"><?= e($u['name']??'') ?></h5>
        <p class="text-muted" style="font-size:.85rem"><?= e(ucfirst($u['role']??'')) ?></p>
        <p class="text-muted" style="font-size:.82rem"><?= e($u['email']??'') ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card mb-3">
      <div class="card-header"><h5>Edit Profile</h5></div>
      <div class="card-body">
        <form id="profileForm" enctype="multipart/form-data">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Full Name</label><input type="text" name="name" class="form-control" value="<?= e($u['name']??'') ?>" required></div>
            <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?= e($u['phone']??'') ?>"></div>
            <div class="col-12"><label class="form-label">Bio</label><textarea name="bio" class="form-control" rows="3"><?= e($u['bio']??'') ?></textarea></div>
            <div class="col-12"><label class="form-label">Avatar</label><input type="file" name="avatar" id="profAvatarInput" class="form-control" accept="image/*"></div>
          </div>
          <button type="submit" class="btn btn-primary mt-3"><i class="fa-solid fa-save"></i>Update Profile</button>
        </form>
      </div>
    </div>
    <div class="card">
      <div class="card-header"><h5>Change Password</h5></div>
      <div class="card-body">
        <form id="pwdForm">
          <div class="mb-3"><label class="form-label">Current Password</label><input type="password" name="current_password" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">New Password</label><input type="password" name="new_password" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Confirm Password</label><input type="password" id="confirmPwd" class="form-control"></div>
          <button type="submit" class="btn btn-primary"><i class="fa-solid fa-lock"></i>Change Password</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
document.getElementById('profileForm').addEventListener('submit', async e => {
  e.preventDefault();
  const fd = new FormData(e.target);
  fd.append('action','update');
  const res = await Api.post('/ajax/profile.php', fd);
  res.success ? Toast.success(res.message) : Toast.error(res.message || 'Error');
});
document.getElementById('pwdForm').addEventListener('submit', async e => {
  e.preventDefault();
  const pw = document.getElementById('confirmPwd').value;
  const np = e.target.querySelector('[name=new_password]').value;
  if (pw !== np) { Toast.error('Passwords do not match.'); return; }
  const res = await Api.post('/ajax/profile.php', { action:'change_password', ...Object.fromEntries(new FormData(e.target)) });
  res.success ? (Toast.success(res.message), e.target.reset()) : Toast.error(res.message);
});
document.getElementById('profAvatarInput').addEventListener('change', function() {
  const reader = new FileReader();
  reader.onload = e => { document.getElementById('profileAvatarImg').src = e.target.result; };
  reader.readAsDataURL(this.files[0]);
});
</script>
