/**
 * WorkForce Pro – Reusable Resource Manager
 * Wires up a DataTable, modal form, and full CRUD for any module.
 *
 * Usage: new Resource({ ... }).init();
 */
class Resource {
  constructor(config) {
    this.endpoint   = config.endpoint;
    this.tableId    = config.tableId;
    this.modalId    = config.modalId;
    this.formId     = config.formId;
    this.columns    = config.columns;       // DataTables column definitions
    this.extraData  = config.extraData || (() => ({})); // extra POST params
    this.onLoad     = config.onLoad || null;
    this.onOpen     = config.onOpen || null;
    this.dt         = null;
    this.selectedIds = new Set();
  }

  init() {
    this._initTable();
    this._initModal();
    this._initBulk();
    if (this.onLoad) this.onLoad();
    return this;
  }

  _initTable() {
    const self = this;
    this.dt = $(`#${this.tableId}`).DataTable({
      processing: true,
      serverSide: true,
      responsive: true,
      dom: '<"d-none">rt<"row align-items-center mt-3"<"col-sm-6"i><"col-sm-6"p>>',
      ajax: {
        url: this.endpoint + '?action=list',
        type: 'POST',
        data: d => ({ ...d, csrf_token: CSRF(), ...self.extraData() })
      },
      columns: this.columns,
      language: { processing: '<div class="spinner-border spinner-border-sm text-primary"></div>' },
      drawCallback: () => self._rebindActions()
    });

    // External search
    const searchInput = document.querySelector(`[data-table="${this.tableId}-search"]`);
    if (searchInput) {
      searchInput.addEventListener('input', () => this.dt.search(searchInput.value).draw());
    }

    // External filters
    document.querySelectorAll(`[data-filter-table="${this.tableId}"]`).forEach(sel => {
      sel.addEventListener('change', () => { this.dt.ajax.reload(); });
    });
  }

  _initModal() {
    const form = document.getElementById(this.formId);
    if (!form) return;
    const self = this;

    form.addEventListener('submit', async e => {
      e.preventDefault();
      clearErrors(form);
      const fd = new FormData(form);
      fd.append('action', 'save');
      Loader.show();
      try {
        const res = await fetch(this.endpoint, { method: 'POST', body: fd });
        const data = await res.json();
        Loader.hide();
        if (data.success) {
          Toast.success(data.message);
          bootstrap.Modal.getInstance(document.getElementById(this.modalId))?.hide();
          this.dt.ajax.reload(null, false);
        } else if (data.errors) {
          displayErrors(form, data.errors);
        } else {
          Toast.error(data.message || 'Something went wrong.');
        }
      } catch (err) {
        Loader.hide();
        Toast.error('Request failed.');
      }
    });

    // Reset form on modal close
    document.getElementById(this.modalId)?.addEventListener('hidden.bs.modal', () => {
      form.reset();
      form.querySelector('[name="id"]').value = '';
      clearErrors(form);
    });
  }

  openCreate() {
    const form = document.getElementById(this.formId);
    if (form) { form.reset(); form.querySelector('[name="id"]').value = ''; clearErrors(form); }
    document.querySelector(`#${this.modalId} .modal-title`).textContent = 'Add New';
    if (this.onOpen) this.onOpen(null);
    new bootstrap.Modal(document.getElementById(this.modalId)).show();
  }

  async openEdit(id) {
    Loader.show();
    const data = await Api.get(this.endpoint, { action: 'get', id });
    Loader.hide();
    if (!data.success) { Toast.error(data.message); return; }
    const form = document.getElementById(this.formId);
    clearErrors(form);
    // Populate form fields
    for (const [k, v] of Object.entries(data.data)) {
      const el = form.querySelector(`[name="${k}"]`);
      if (el) el.value = v ?? '';
    }
    document.querySelector(`#${this.modalId} .modal-title`).textContent = 'Edit Record';
    if (this.onOpen) this.onOpen(data.data);
    new bootstrap.Modal(document.getElementById(this.modalId)).show();
  }

  async deleteRow(id) {
    const confirmed = await confirmDelete();
    if (!confirmed) return;
    Loader.show();
    const res = await Api.post(this.endpoint, { action: 'delete', id });
    Loader.hide();
    if (res.success) { Toast.success(res.message); this.dt.ajax.reload(null, false); }
    else Toast.error(res.message);
  }

  async toggleStatus(id) {
    const res = await Api.post(this.endpoint, { action: 'toggle', id });
    if (res.success) { Toast.success(res.message); this.dt.ajax.reload(null, false); }
    else Toast.error(res.message);
  }

  async bulkDeleteSelected() {
    if (!this.selectedIds.size) { Toast.warning('Select at least one row.'); return; }
    const confirmed = await confirmDelete(`Delete ${this.selectedIds.size} records?`);
    if (!confirmed) return;
    const res = await Api.post(this.endpoint, { action: 'bulk_delete', ids: [...this.selectedIds] });
    if (res.success) { Toast.success(res.message); this.selectedIds.clear(); this.dt.ajax.reload(null, false); }
    else Toast.error(res.message);
    this._updateBulkBtn();
  }

  _initBulk() {
    const self = this;
    // Select all checkbox
    document.getElementById(`${this.tableId}_selectAll`)?.addEventListener('change', function() {
      self.dt.$('input[type=checkbox]').prop('checked', this.checked);
      self.dt.rows().data().each(row => {
        if (this.checked) self.selectedIds.add(row.id ?? row[0]);
        else self.selectedIds.clear();
      });
      self._updateBulkBtn();
    });
  }

  _rebindActions() {
    const tbl = document.getElementById(this.tableId);
    if (!tbl) return;
    const self = this;
    // Row checkboxes
    tbl.querySelectorAll('input[type=checkbox][data-id]').forEach(cb => {
      cb.addEventListener('change', () => {
        const id = parseInt(cb.dataset.id);
        cb.checked ? self.selectedIds.add(id) : self.selectedIds.delete(id);
        self._updateBulkBtn();
      });
    });
    // Edit buttons
    tbl.querySelectorAll('[data-action="edit"]').forEach(btn => {
      btn.addEventListener('click', () => self.openEdit(parseInt(btn.dataset.id)));
    });
    // Delete buttons
    tbl.querySelectorAll('[data-action="delete"]').forEach(btn => {
      btn.addEventListener('click', () => self.deleteRow(parseInt(btn.dataset.id)));
    });
    // Toggle buttons
    tbl.querySelectorAll('[data-action="toggle"]').forEach(btn => {
      btn.addEventListener('click', () => self.toggleStatus(parseInt(btn.dataset.id)));
    });
  }

  _updateBulkBtn() {
    const btn = document.querySelector(`[data-bulk="${this.tableId}"]`);
    if (btn) btn.textContent = `Delete Selected (${this.selectedIds.size})`;
  }

  reload() { this.dt.ajax.reload(null, false); }
}
