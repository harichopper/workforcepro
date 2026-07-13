<?php
declare(strict_types=1);

class Employee extends BaseModel
{
    protected string $table = 'employees';

    public function tableData(array $req): array
    {
        $draw   = (int) ($req['draw'] ?? 1);
        $start  = max(0, (int) ($req['start'] ?? 0));
        $length = min(100, max(10, (int) ($req['length'] ?? 10)));
        $search = trim((string) ($req['search']['value'] ?? ''));
        $deptFilter = (int) ($req['dept_id'] ?? 0);
        $statusFilter = trim((string) ($req['status'] ?? ''));

        $cols     = ['e.id', 'e.emp_code', 'full_name', 'd.name', 'des.title', 'e.hire_date', 'e.status'];
        $orderIdx = (int) ($req['order'][0]['column'] ?? 0);
        $orderDir = strtolower((string) ($req['order'][0]['dir'] ?? 'asc')) === 'asc' ? 'ASC' : 'DESC';
        $orderBy  = $cols[$orderIdx] ?? 'e.id';

        $conditions = [];
        $params     = [];
        if ($search !== '') {
            $conditions[]  = '(e.emp_code LIKE :s OR e.first_name LIKE :s OR e.last_name LIKE :s OR e.email LIKE :s)';
            $params['s']   = "%{$search}%";
        }
        if ($deptFilter > 0) {
            $conditions[]       = 'e.department_id = :dept';
            $params['dept']     = $deptFilter;
        }
        if ($statusFilter !== '') {
            $conditions[]         = 'e.status = :status';
            $params['status']     = $statusFilter;
        }
        $where = $conditions ? implode(' AND ', $conditions) : '1=1';

        $total = $this->countAll();
        $fr    = $this->fetchOne(
            "SELECT COUNT(*) AS total FROM employees e
             INNER JOIN departments d ON d.id = e.department_id
             INNER JOIN designations des ON des.id = e.designation_id
             WHERE {$where}",
            $params
        );
        $filtered = (int) ($fr['total'] ?? 0);

        $params['start']  = $start;
        $params['length'] = $length;

        $rows = $this->fetchAll(
            "SELECT e.id, e.emp_code, e.first_name, e.last_name,
                    CONCAT(e.first_name, ' ', e.last_name) AS full_name,
                    e.email, e.phone, e.status, e.hire_date, e.avatar,
                    d.name AS department_name, des.title AS designation_title
             FROM employees e
             INNER JOIN departments d ON d.id = e.department_id
             INNER JOIN designations des ON des.id = e.designation_id
             WHERE {$where}
             ORDER BY {$orderBy} {$orderDir}
             LIMIT :start, :length",
            $params
        );

        return $this->datatableResponse($rows, $filtered, $total, $draw);
    }

    public function getProfile(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT e.*, d.name AS department_name, des.title AS designation_title
             FROM employees e
             INNER JOIN departments d ON d.id = e.department_id
             INNER JOIN designations des ON des.id = e.designation_id
             WHERE e.id = :id',
            ['id' => $id]
        );
    }

    public function nextCode(): string
    {
        $row = $this->fetchOne('SELECT emp_code FROM employees ORDER BY id DESC LIMIT 1');
        if (!$row) return 'EMP-001';
        $n = (int) preg_replace('/\D/', '', $row['emp_code']) + 1;
        return 'EMP-' . str_pad((string) $n, 3, '0', STR_PAD_LEFT);
    }

    public function countByStatus(): array
    {
        return $this->fetchAll(
            'SELECT status, COUNT(*) AS total FROM employees GROUP BY status'
        );
    }

    public function recentHires(int $limit = 5): array
    {
        return $this->fetchAll(
            'SELECT e.emp_code, e.first_name, e.last_name, e.hire_date, d.name AS dept
             FROM employees e
             INNER JOIN departments d ON d.id = e.department_id
             ORDER BY e.hire_date DESC
             LIMIT :limit',
            ['limit' => $limit]
        );
    }

    public function exportAll(): array
    {
        return $this->fetchAll(
            'SELECT e.emp_code, e.first_name, e.last_name, e.email, e.phone,
                    e.gender, e.dob, e.hire_date, e.status,
                    d.name AS department, des.title AS designation
             FROM employees e
             INNER JOIN departments d ON d.id = e.department_id
             INNER JOIN designations des ON des.id = e.designation_id
             ORDER BY e.emp_code'
        );
    }
}
