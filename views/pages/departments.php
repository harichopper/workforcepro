<?php declare(strict_types=1); ?>
<div class="page-header"><h1>Departments</h1><div class="breadcrumb"><a href="#">Home</a><i class="fa-solid fa-chevron-right" style="font-size:.6rem"></i>Departments</div></div>
<div class="card">
  <div class="card-header"><h5><i class="fa-solid fa-building me-2 text-primary"></i>Department List</h5>
    <div class="d-flex gap-2">
      <button class="btn btn-primary btn-sm" id="addDeptBtn"><i class="fa-solid fa-plus"></i>Add Department</button>
      <button class="btn btn-secondary btn-sm" data-bulk="deptTable" onclick="deptRes.bulkDeleteSelected()"><i class="fa-solid fa-trash"></i>Bulk Delete (0)</button>
    </div>
  </div>
  <div class="card-body">
    <div class="table-toolbar mb-3">
      <div class="search-wrap"><i class="fa-solid fa-search"></i><input type="text" class="form-control" placeholder="Search…" data-table="deptTable-search"></div>
    </div>
    <div class="table-wrapper">
      <table id="deptTable" class="wf-table w-100">
        <thead><tr><th><input type="checkbox" id="deptTable_selectAll"></th><th>Name</th><th>Code</th><th>Manager</th><th>Employees</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<div class="modal fade" id="deptModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Add Department</h5><button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1)"></button></div>
    <div class="modal-body">
      <form id="deptForm">
        <input type="hidden" name="id">
        <div class="mb-3"><label class="form-label">Name*</label><input type="text" name="name" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Code*</label><input type="text" name="code" class="form-control" placeholder="e.g. ENG" required></div>
        <div class="mb-3"><label class="form-label">Manager</label><input type="text" name="manager_name" class="form-control"></div>
        <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
        <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
      </form>
    </div>
    <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" form="deptForm" type="submit"><i class="fa-solid fa-save"></i>Save</button></div>
  </div></div>
</div>
<script>
const deptRes = new Resource({
  endpoint:'/ajax/departments.php', tableId:'deptTable', modalId:'deptModal', formId:'deptForm',
  columns:[
    {data:'id',render:d=>`<input type="checkbox" data-id="${d}">`,orderable:false},
    {data:'name',render:(d,t,r)=>`<div class="fw-600">${d}</div>${r.description?`<div style="font-size:.75rem;color:var(--muted)">${r.description.substring(0,40)}</div>`:'`'}`},
    {data:'code',render:d=>`<code>${d}</code>`},
    {data:'manager_name',render:d=>d||'—'},
    {data:'emp_count'},
    {data:'status',render:d=>`<span class="badge badge-${d}">${d}</span>`},
    {data:'id',orderable:false,render:d=>`<div class="d-flex gap-2">
      <button class="btn btn-sm btn-secondary btn-icon" data-action="edit" data-id="${d}"><i class="fa-solid fa-pen-to-square"></i></button>
      <button class="btn btn-sm btn-secondary btn-icon" data-action="toggle" data-id="${d}"><i class="fa-solid fa-toggle-on"></i></button>
      <button class="btn btn-sm btn-danger btn-icon" data-action="delete" data-id="${d}"><i class="fa-solid fa-trash"></i></button>
    </div>`}
  ]
}).init();
document.getElementById('addDeptBtn').addEventListener('click',()=>deptRes.openCreate());
</script>
