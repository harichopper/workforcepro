<?php declare(strict_types=1); ?>
<div class="page-header"><h1>Audit Logs</h1></div>
<div class="card">
  <div class="card-header"><h5><i class="fa-solid fa-shield-halved me-2 text-primary"></i>Activity History</h5></div>
  <div class="card-body">
    <div class="table-toolbar mb-3"><div class="search-wrap"><i class="fa-solid fa-search"></i><input type="text" class="form-control" placeholder="Search…" data-table="auditTable-search"></div></div>
    <div class="table-wrapper">
      <table id="auditTable" class="wf-table w-100">
        <thead><tr><th>User</th><th>Action</th><th>Entity</th><th>Entity ID</th><th>IP Address</th><th>Date</th></tr></thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>
<script>
const auditRes = new Resource({
  endpoint: '/ajax/audit_logs.php',
  tableId: 'auditTable',
  modalId: '',
  formId: '',
  columns: [
    { data: 'user_name' },
    { data: 'action',   render: d => `<span class="badge badge-info">${d}</span>` },
    { data: 'entity' },
    { data: 'entity_id', render: d => d || '—' },
    { data: 'ip_address' },
    { data: 'created_at' }
  ]
}).init();
</script>
