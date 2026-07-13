<?php declare(strict_types=1); ?>
<div class="page-header"><h1>Leave Requests</h1></div>
<div class="card">
  <div class="card-header"><h5><i class="fa-solid fa-umbrella-beach me-2 text-primary"></i>Leave Management</h5>
    <button class="btn btn-primary btn-sm" id="addLeaveBtn"><i class="fa-solid fa-plus"></i>New Request</button>
  </div>
  <div class="card-body">
    <div class="table-toolbar mb-3">
      <div class="search-wrap"><i class="fa-solid fa-search"></i><input type="text" class="form-control" placeholder="Search…" data-table="leaveTable-search"></div>
      <select class="form-select" style="width:150px" id="leaveStatusFilter" data-filter-table="leaveTable">
        <option value="">All Status</option><option value="pending">Pending</option><option value="approved">Approved</option><option value="rejected">Rejected</option>
      </select>
    </div>
    <div class="table-wrapper">
      <table id="leaveTable" class="wf-table w-100">
        <thead><tr><th><input type="checkbox" id="leaveTable_selectAll"></th><th>Employee</th><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>
<div class="modal fade" id="leaveModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Leave Request</h5><button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1)"></button></div>
    <div class="modal-body">
      <form id="leaveForm">
        <input type="hidden" name="id">
        <div class="mb-3"><label class="form-label">Employee*</label><select name="employee_id" id="leaveEmpSel" class="form-select" required><option value="">Select…</option></select></div>
        <div class="mb-3"><label class="form-label">Leave Type</label>
          <select name="leave_type" class="form-select">
            <option value="annual">Annual</option><option value="sick">Sick</option><option value="casual">Casual</option>
            <option value="maternity">Maternity</option><option value="paternity">Paternity</option><option value="unpaid">Unpaid</option>
          </select>
        </div>
        <div class="row g-3 mb-3">
          <div class="col"><label class="form-label">From*</label><input type="date" name="from_date" class="form-control" required></div>
          <div class="col"><label class="form-label">To*</label><input type="date" name="to_date" class="form-control" required></div>
        </div>
        <div class="mb-3"><label class="form-label">Reason</label><textarea name="reason" class="form-control" rows="3"></textarea></div>
      </form>
    </div>
    <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" form="leaveForm" type="submit"><i class="fa-solid fa-save"></i>Submit</button></div>
  </div></div>
</div>
<script>
const leaveRes = new Resource({
  endpoint:'/ajax/leaves.php', tableId:'leaveTable', modalId:'leaveModal', formId:'leaveForm',
  extraData:()=>({leave_status:document.getElementById('leaveStatusFilter')?.value||''}),
  columns:[
    {data:'id',render:d=>`<input type="checkbox" data-id="${d}">`,orderable:false},
    {data:'emp_name',render:(d,t,r)=>`<strong>${d}</strong><br><small>${r.emp_code}</small>`},
    {data:'leave_type',render:d=>`<span class="badge badge-info">${d}</span>`},
    {data:'from_date'},{data:'to_date'},{data:'days'},
    {data:'status',render:d=>`<span class="badge badge-${d}">${d}</span>`},
    {data:'id',orderable:false,render:d=>`<div class="d-flex gap-2">
      <button class="btn btn-sm btn-success btn-icon" onclick="approveLeave(${d})"><i class="fa-solid fa-check"></i></button>
      <button class="btn btn-sm btn-danger btn-icon" onclick="rejectLeave(${d})"><i class="fa-solid fa-times"></i></button>
      <button class="btn btn-sm btn-secondary btn-icon" data-action="edit" data-id="${d}"><i class="fa-solid fa-pen-to-square"></i></button>
      <button class="btn btn-sm btn-danger btn-icon" data-action="delete" data-id="${d}"><i class="fa-solid fa-trash"></i></button>
    </div>`}
  ]
}).init();
document.getElementById('addLeaveBtn').addEventListener('click',()=>leaveRes.openCreate());
async function approveLeave(id) { const r=await Api.post('/ajax/leaves.php',{action:'approve',id}); r.success?Toast.success(r.message):Toast.error(r.message); leaveRes.reload(); }
async function rejectLeave(id)  { const r=await Api.post('/ajax/leaves.php',{action:'reject',id});  r.success?Toast.success(r.message):Toast.error(r.message); leaveRes.reload(); }
</script>
