<?php declare(strict_types=1); ?>
<div class="page-header"><h1>Salary & Payroll</h1></div>
<div class="card">
  <div class="card-header"><h5><i class="fa-solid fa-wallet me-2 text-primary"></i>Salary Records</h5>
    <div class="d-flex gap-2">
      <button class="btn btn-primary btn-sm" id="addSalBtn"><i class="fa-solid fa-plus"></i>Add Record</button>
      <button class="btn btn-secondary btn-sm" data-bulk="salTable" onclick="salRes.bulkDeleteSelected()"><i class="fa-solid fa-trash"></i>Bulk Delete (0)</button>
    </div>
  </div>
  <div class="card-body">
    <div class="table-toolbar mb-3">
      <div class="search-wrap"><i class="fa-solid fa-search"></i><input type="text" class="form-control" placeholder="Search…" data-table="salTable-search"></div>
      <input type="month" class="form-control" style="width:160px" id="monthFilter" data-filter-table="salTable">
    </div>
    <div class="table-wrapper">
      <table id="salTable" class="wf-table w-100">
        <thead><tr><th><input type="checkbox" id="salTable_selectAll"></th><th>Employee</th><th>Department</th><th>Month</th><th>Basic</th><th>Allowance</th><th>Deductions</th><th>Net</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>
<div class="modal fade" id="salModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Salary Record</h5><button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1)"></button></div>
    <div class="modal-body">
      <form id="salForm">
        <input type="hidden" name="id">
        <div class="mb-3"><label class="form-label">Employee*</label><select name="employee_id" class="form-select" required><option value="">Select…</option></select></div>
        <div class="mb-3"><label class="form-label">Pay Month*</label><input type="month" name="pay_month" class="form-control" required></div>
        <div class="row g-3 mb-3">
          <div class="col"><label class="form-label">Basic Salary</label><input type="number" name="basic_salary" class="form-control" step="0.01" value="0"></div>
          <div class="col"><label class="form-label">Allowances</label><input type="number" name="allowances" class="form-control" step="0.01" value="0"></div>
          <div class="col"><label class="form-label">Deductions</label><input type="number" name="deductions" class="form-control" step="0.01" value="0"></div>
        </div>
        <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="pending">Pending</option><option value="paid">Paid</option><option value="cancelled">Cancelled</option></select></div>
        <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
      </form>
    </div>
    <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" form="salForm" type="submit"><i class="fa-solid fa-save"></i>Save</button></div>
  </div></div>
</div>
<script>
const fmt = n => '₹' + parseFloat(n||0).toLocaleString('en-IN', {minimumFractionDigits:2});
const salRes = new Resource({
  endpoint:'/ajax/salaries.php', tableId:'salTable', modalId:'salModal', formId:'salForm',
  extraData:()=>({pay_month:document.getElementById('monthFilter')?.value||''}),
  columns:[
    {data:'id',render:d=>`<input type="checkbox" data-id="${d}">`,orderable:false},
    {data:'emp_name',render:(d,t,r)=>`<strong>${d}</strong><br><small>${r.emp_code}</small>`},
    {data:'department_name'},
    {data:'pay_month',render:d=>d?d.substring(0,7):'—'},
    {data:'basic_salary',render:d=>fmt(d)},
    {data:'allowances',render:d=>fmt(d)},
    {data:'deductions',render:d=>fmt(d)},
    {data:'net_salary',render:d=>`<strong>${fmt(d)}</strong>`},
    {data:'status',render:d=>`<span class="badge badge-${d}">${d}</span>`},
    {data:'id',orderable:false,render:d=>`<div class="d-flex gap-2">
      <button class="btn btn-sm btn-success btn-icon" onclick="markPaid(${d})" title="Mark Paid"><i class="fa-solid fa-money-bill-wave"></i></button>
      <button class="btn btn-sm btn-secondary btn-icon" data-action="edit" data-id="${d}"><i class="fa-solid fa-pen-to-square"></i></button>
      <button class="btn btn-sm btn-danger btn-icon" data-action="delete" data-id="${d}"><i class="fa-solid fa-trash"></i></button>
    </div>`}
  ]
}).init();
document.getElementById('addSalBtn').addEventListener('click',()=>salRes.openCreate());
async function markPaid(id) { const r=await Api.post('/ajax/salaries.php',{action:'mark_paid',id}); r.success?Toast.success(r.message):Toast.error(r.message); salRes.reload(); }
</script>
