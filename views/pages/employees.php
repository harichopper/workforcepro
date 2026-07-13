<?php declare(strict_types=1); ?>
<div class="page-header">
  <h1>Employees</h1>
  <div class="breadcrumb"><a href="#">Home</a><i class="fa-solid fa-chevron-right" style="font-size:.6rem"></i>Employees</div>
</div>

<div class="card">
  <div class="card-header">
    <h5><i class="fa-solid fa-user-tie me-2 text-primary"></i>Employee List</h5>
    <div class="d-flex gap-2 flex-wrap">
      <button class="btn btn-primary btn-sm" id="addEmpBtn"><i class="fa-solid fa-plus"></i>Add Employee</button>
      <button class="btn btn-secondary btn-sm" data-bulk="empTable" onclick="empRes.bulkDeleteSelected()"><i class="fa-solid fa-trash"></i>Bulk Delete (0)</button>
      <button class="btn btn-secondary btn-sm" onclick="exportTable('/ajax/employees.php','csv')"><i class="fa-solid fa-file-csv"></i>CSV</button>
      <button class="btn btn-secondary btn-sm" onclick="window.print()"><i class="fa-solid fa-print"></i>Print</button>
    </div>
  </div>
  <div class="card-body">
    <div class="table-toolbar mb-3">
      <div class="search-wrap"><i class="fa-solid fa-search"></i><input type="text" class="form-control" placeholder="Search employees…" data-table="empTable-search"></div>
      <select class="form-select" style="width:160px" data-filter-table="empTable" id="deptFilter"><option value="">All Departments</option></select>
      <select class="form-select" style="width:140px" data-filter-table="empTable" id="statusFilter">
        <option value="">All Status</option><option value="active">Active</option><option value="inactive">Inactive</option><option value="terminated">Terminated</option>
      </select>
    </div>
    <div class="table-wrapper">
      <table id="empTable" class="wf-table w-100">
        <thead><tr>
          <th><input type="checkbox" id="empTable_selectAll"></th>
          <th>Code</th><th>Employee</th><th>Department</th><th>Designation</th><th>Hire Date</th><th>Status</th><th>Actions</th>
        </tr></thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Employee Modal -->
<div class="modal fade" id="empModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Add Employee</h5><button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1)"></button></div>
      <div class="modal-body">
        <form id="empForm" enctype="multipart/form-data">
          <input type="hidden" name="id">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">First Name*</label><input type="text" name="first_name" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Last Name*</label><input type="text" name="last_name" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Email*</label><input type="email" name="email" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Department*</label>
              <select name="department_id" id="empDeptSel" class="form-select" required><option value="">Select…</option></select>
            </div>
            <div class="col-md-6"><label class="form-label">Designation*</label>
              <select name="designation_id" id="empDesigSel" class="form-select" required><option value="">Select Dept first…</option></select>
            </div>
            <div class="col-md-4"><label class="form-label">Gender</label>
              <select name="gender" class="form-select"><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select>
            </div>
            <div class="col-md-4"><label class="form-label">Date of Birth</label><input type="date" name="dob" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Hire Date*</label><input type="date" name="hire_date" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Status</label>
              <select name="status" class="form-select"><option value="active">Active</option><option value="inactive">Inactive</option><option value="terminated">Terminated</option></select>
            </div>
            <div class="col-md-6"><label class="form-label">Avatar</label><input type="file" name="avatar" id="avatarInput" class="form-control" accept="image/*"></div>
            <div class="col-12"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"></textarea></div>
            <div class="col-12 text-center"><img id="avatarPreview" class="avatar-preview" src="/assets/images/default-avatar.png" alt="preview"></div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" form="empForm" type="submit"><i class="fa-solid fa-save"></i>Save</button>
      </div>
    </div>
  </div>
</div>

<script>
let depts = [], desigs = [];

// Load dropdown options
(async () => {
  const res = await Api.get('/ajax/employees.php', { action: 'options' });
  if (!res.success) return;
  depts  = res.departments;
  desigs = res.designations;

  const deptSel = document.getElementById('empDeptSel');
  const filterSel = document.getElementById('deptFilter');
  depts.forEach(d => {
    deptSel.add(new Option(d.name, d.id));
    filterSel.add(new Option(d.name, d.id));
  });
})();

// Cascade designation on dept change
document.getElementById('empDeptSel').addEventListener('change', function() {
  const deptId = parseInt(this.value);
  const sel = document.getElementById('empDesigSel');
  sel.innerHTML = '<option value="">Select…</option>';
  desigs.filter(d => d.department_id == deptId).forEach(d => sel.add(new Option(d.title, d.id)));
});

initAvatarPreview('avatarInput', 'avatarPreview');

// Resource
const empRes = new Resource({
  endpoint: '/ajax/employees.php',
  tableId: 'empTable',
  modalId: 'empModal',
  formId: 'empForm',
  extraData: () => ({
    dept_id: document.getElementById('deptFilter')?.value || '',
    status:  document.getElementById('statusFilter')?.value || ''
  }),
  columns: [
    { data: 'id',           render: d => `<input type="checkbox" data-id="${d}">`, orderable: false },
    { data: 'emp_code',     render: d => `<code>${d}</code>` },
    { data: 'full_name',    render: (d,t,r) => `<div class="d-flex align-center gap-2"><div class="emp-avatar">${d.charAt(0)}</div><div><div class="fw-600">${d}</div><div style="font-size:.75rem;color:var(--muted)">${r.email}</div></div></div>` },
    { data: 'department_name' },
    { data: 'designation_title' },
    { data: 'hire_date' },
    { data: 'status', render: d => `<span class="badge badge-${d}">${d}</span>` },
    { data: 'id', orderable:false, render: d => `
      <div class="d-flex gap-2">
        <button class="btn btn-sm btn-secondary btn-icon" data-action="edit" data-id="${d}" title="Edit"><i class="fa-solid fa-pen-to-square"></i></button>
        <button class="btn btn-sm btn-secondary btn-icon" data-action="toggle" data-id="${d}" title="Toggle Status"><i class="fa-solid fa-toggle-on"></i></button>
        <button class="btn btn-sm btn-danger btn-icon" data-action="delete" data-id="${d}" title="Delete"><i class="fa-solid fa-trash"></i></button>
      </div>` }
  ],
  onOpen: async (row) => {
    if (row && row.department_id) {
      document.getElementById('empDeptSel').value = row.department_id;
      document.getElementById('empDeptSel').dispatchEvent(new Event('change'));
      setTimeout(() => { document.getElementById('empDesigSel').value = row.designation_id; }, 100);
    }
  }
}).init();

document.getElementById('addEmpBtn').addEventListener('click', () => empRes.openCreate());
</script>
