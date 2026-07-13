<?php declare(strict_types=1); ?>
<div class="page-header"><h1>Attendance</h1><div class="breadcrumb"><a href="#">Home</a><i class="fa-solid fa-chevron-right" style="font-size:.6rem"></i>Attendance</div></div>
<div class="card">
  <div class="card-header"><h5><i class="fa-solid fa-calendar-check me-2 text-primary"></i>Attendance Records</h5>
    <div class="d-flex gap-2">
      <button class="btn btn-primary btn-sm" id="addAttBtn"><i class="fa-solid fa-plus"></i>Mark Attendance</button>
    </div>
  </div>
  <div class="card-body">
    <div class="table-toolbar mb-3">
      <div class="search-wrap"><i class="fa-solid fa-search"></i><input type="text" class="form-control" placeholder="Search…" data-table="attTable-search"></div>
      <input type="date" class="form-control" style="width:180px" id="dateFilter" data-filter-table="attTable" value="<?= date('Y-m-d') ?>">
    </div>
    <div class="table-wrapper">
      <table id="attTable" class="wf-table w-100">
        <thead><tr><th><input type="checkbox" id="attTable_selectAll"></th><th>Employee</th><th>Department</th><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>
<div class="modal fade" id="attModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Mark Attendance</h5><button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1)"></button></div>
    <div class="modal-body">
      <form id="attForm">
        <input type="hidden" name="id">
        <div class="mb-3"><label class="form-label">Employee*</label><select name="employee_id" id="attEmpSel" class="form-select" required><option value="">Select…</option></select></div>
        <div class="mb-3"><label class="form-label">Date*</label><input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
        <div class="row g-3 mb-3">
          <div class="col"><label class="form-label">Check In</label><input type="time" name="check_in" class="form-control"></div>
          <div class="col"><label class="form-label">Check Out</label><input type="time" name="check_out" class="form-control"></div>
        </div>
        <div class="mb-3"><label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="present">Present</option><option value="absent">Absent</option><option value="half-day">Half Day</option><option value="leave">Leave</option><option value="holiday">Holiday</option>
          </select>
        </div>
        <div class="mb-3"><label class="form-label">Remarks</label><input type="text" name="remarks" class="form-control"></div>
      </form>
    </div>
    <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" form="attForm" type="submit"><i class="fa-solid fa-save"></i>Save</button></div>
  </div></div>
</div>
<script>
(async()=>{
  const res=await Api.get('/ajax/employees.php',{action:'options'});
  if(!res.success)return;
  const sel=document.getElementById('attEmpSel');
  res.departments.forEach(d=>{}); // preload
  const allEmps = await Api.get('/ajax/employees.php',{action:'list'});
})();

const attRes = new Resource({
  endpoint:'/ajax/attendance.php', tableId:'attTable', modalId:'attModal', formId:'attForm',
  extraData:()=>({att_date:document.getElementById('dateFilter')?.value||''}),
  columns:[
    {data:'id',render:d=>`<input type="checkbox" data-id="${d}">`,orderable:false},
    {data:'emp_name',render:(d,t,r)=>`<div class="fw-600">${d}</div><div style="font-size:.75rem;color:var(--muted)">${r.emp_code}</div>`},
    {data:'department_name'},
    {data:'date'},
    {data:'check_in',render:d=>d||'—'},
    {data:'check_out',render:d=>d||'—'},
    {data:'status',render:d=>`<span class="badge badge-${d}">${d}</span>`},
    {data:'id',orderable:false,render:d=>`<div class="d-flex gap-2">
      <button class="btn btn-sm btn-secondary btn-icon" data-action="edit" data-id="${d}"><i class="fa-solid fa-pen-to-square"></i></button>
      <button class="btn btn-sm btn-danger btn-icon" data-action="delete" data-id="${d}"><i class="fa-solid fa-trash"></i></button>
    </div>`}
  ]
}).init();
document.getElementById('addAttBtn').addEventListener('click',()=>attRes.openCreate());
</script>
