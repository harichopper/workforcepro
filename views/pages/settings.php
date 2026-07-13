<?php declare(strict_types=1);
$settingsCtrl = new SettingsController();
$settings = $settingsCtrl->get()['data'];
?>
<div class="page-header"><h1>Settings</h1></div>
<div class="row g-4">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header"><h5><i class="fa-solid fa-building me-2 text-primary"></i>Company Settings</h5></div>
      <div class="card-body">
        <form id="settingsForm">
          <div class="mb-3"><label class="form-label">Company Name</label><input type="text" name="company_name" class="form-control" value="<?= e($settings['company_name']??'') ?>"></div>
          <div class="mb-3"><label class="form-label">Company Email</label><input type="email" name="company_email" class="form-control" value="<?= e($settings['company_email']??'') ?>"></div>
          <div class="mb-3"><label class="form-label">Timezone</label><input type="text" name="timezone" class="form-control" value="<?= e($settings['timezone']??'') ?>"></div>
          <div class="mb-3"><label class="form-label">Records Per Page</label><input type="number" name="records_per_page" class="form-control" value="<?= e($settings['records_per_page']??10) ?>"></div>
          <div class="mb-3"><label class="form-label">Currency Symbol</label><input type="text" name="currency_symbol" class="form-control" value="<?= e($settings['currency_symbol']??'₹') ?>"></div>
          <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i>Save Settings</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card mb-3">
      <div class="card-header"><h5><i class="fa-solid fa-database me-2 text-primary"></i>Database Backup</h5></div>
      <div class="card-body">
        <p class="text-muted" style="font-size:.85rem">Generate a full SQL backup of the database. Backups are stored in <code>database/backups/</code>.</p>
        <button class="btn btn-warning" id="backupBtn"><i class="fa-solid fa-download"></i>Create Backup</button>
      </div>
    </div>
    <div class="card">
      <div class="card-header"><h5><i class="fa-solid fa-file-lines me-2 text-primary"></i>Application Logs</h5></div>
      <div class="card-body">
        <div id="logOutput" style="background:var(--bg);border-radius:8px;padding:12px;font-family:monospace;font-size:.78rem;max-height:200px;overflow-y:auto;color:var(--muted)">Loading…</div>
      </div>
    </div>
  </div>
</div>
<script>
document.getElementById('settingsForm').addEventListener('submit', async e => {
  e.preventDefault();
  const res = await Api.post('/ajax/settings.php', { action:'save', ...Object.fromEntries(new FormData(e.target)) });
  res.success ? Toast.success(res.message) : Toast.error(res.message);
});
document.getElementById('backupBtn').addEventListener('click', async () => {
  const res = await Api.post('/ajax/settings.php', { action:'backup' });
  res.success ? Toast.success(`Backup created: ${res.file}`) : Toast.error(res.message);
});
(async()=>{
  const res = await Api.post('/ajax/settings.php', { action:'logs' });
  const out = document.getElementById('logOutput');
  if (!res.success || !res.data.length) { out.textContent = 'No log entries.'; return; }
  out.textContent = res.data.join('\n');
})();
</script>
