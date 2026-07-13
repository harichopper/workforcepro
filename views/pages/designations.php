<?php declare(strict_types=1); ?>
<div class="page-header"><h1>Designations</h1><div class="breadcrumb"><a href="#">Home</a><i class="fa-solid fa-chevron-right" style="font-size:.6rem"></i>Designations</div></div>
<div class="card">
  <div class="card-header"><h5><i class="fa-solid fa-sitemap me-2 text-primary"></i>Designation List</h5>
    <div class="d-flex gap-2">
      <button class="btn btn-primary btn-sm" id="addDesigBtn"><i class="fa-solid fa-plus"></i>Add Designation</button>
      <button class="btn btn-secondary btn-sm" data-bulk="desigTable" onclick="desigRes.bulkDeleteSelected()"><i class="fa-solid fa-trash"></i>Bulk Delete (0)</button>
    </div>
  </div>
  <div class="card-body">
    <div class="table-toolbar mb-3"><div class="search-wrap"><i class="fa-solid fa-search"></i><input type="text" class="form-control" placeholder="Search…" data-table="desigTable-search"></div></div>
    <div class="table-wrapper">
      <table id="desigTable" class="wf-table w-100">
        <thead><tr><th><input type="checkbox" id="desigTable_selectAll"></th><th>Title</th><th>Department</th><th>Level</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>
<div class="modal fade" id="desigModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Add Designation</h5><button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1)"></button></div>
    <div class="modal-body">
      <form id="desigForm">
        <input type="hidden" name="id">
        <div class="mb-3"><label class="form-label">Department*</label><select name="department_id" id="desigDeptSel" class="form-select" required><option value="">Select…</option></select></div>
        <div class="mb-3"><label class="form-label">Title*</label><input type="text" name="title" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Level</label>
          <select name="level" class="form-select">
            <?php foreach(['junior','mid','senior','lead','manager','director','c-level'] as $l): ?>
            <option value="<?= $l ?>"><?= ucfirst($l) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
      </form>
    </div>
    <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" form="desigForm" type="submit"><i class="fa-solid fa-save"></i>Save</button></div>
  </div></div>
</div>
<script>
(async()=>{
  const res=await Api.get('/ajax/departments.php',{action:'options'});
  if(!res.success)return;
  const sel=document.getElementById('desigDeptSel');
  res.data.forEach(d=>sel.add(new Option(d.name,d.id)));
})();

const desigRes = new Resource({
  endpoint:'/ajax/designations.php', tableId:'desigTable', modalId:'desigModal', formId:'desigForm',
  columns:[
    {data:'id',render:d=>`<input type="checkbox" data-id="${d}">`,orderable:false},
    {data:'title',render:d=>`<div class="fw-600">${d}</div>`},
    {data:'department_name'},
    {data:'level',render:d=>`<span class="badge badge-info">${d}</span>`},
    {data:'status',render:d=>`<span class="badge badge-${d}">${d}</span>`},
    {data:'id',orderable:false,render:d=>`<div class="d-flex gap-2">
      <button class="btn btn-sm btn-secondary btn-icon" data-action="edit" data-id="${d}"><i class="fa-solid fa-pen-to-square"></i></button>
      <button class="btn btn-sm btn-danger btn-icon" data-action="delete" data-id="${d}"><i class="fa-solid fa-trash"></i></button>
    </div>`}
  ]
}).init();
document.getElementById('addDesigBtn').addEventListener('click',()=>desigRes.openCreate());
</script>
