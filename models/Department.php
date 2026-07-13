<?php
declare(strict_types=1);

class Department extends BaseModel
{
    protected string $table = 'departments';

    public function options(): array
    {
        return $this->fetchAll(
            'SELECT id, name FROM departments WHERE status = \'active\' ORDER BY name'
        );
    }

    public function tableData(array $req): array
    {
        $draw   = (int) ($req['draw'] ?? 1);
        $start  = max(0, (int) ($req['start'] ?? 0));
        $length = min(100, max(10, (int) ($req['length'] ?? 10)));
        $search = trim((string) ($req['search']['value'] ?? ''));

        $cols     = ['d.id', 'd.name', 'd.code', 'd.manager_name', 'd.status', 'd.created_at'];
        $orderIdx = (int) ($req['order'][0]['column'] ?? 0);
        $orderDir = strtolower((string) ($req['order'][0]['dir'] ?? 'asc')) === 'asc' ? 'ASC' : 'DESC';
        $orderBy  = $cols[$orderIdx] ?? 'd.id';

        $where  = '1=1';
        $params = [];
        if ($search !== '') {
            $where            = '(d.name LIKE :s OR d.code LIKE :s OR d.manager_name LIKE :s)';
            $params['s']      = "%{$search}%";
        }

        $total    = $this->countAll();
        $filtered = $this->countWhere(str_replace('d.', '', $where === '1=1' ? '1=1' : $where), $params);

        // Run join-based filtered count
        $filteredRow = $this->fetchOne(
            "SELECT COUNT(*) AS total FROM departments d WHERE {$where}",
            $params
        );
        $filtered = (int) ($filteredRow['total'] ?? 0);

        $params['start']  = $start;
        $params['length'] = $length;

        $rows = $this->fetchAll(
            "SELECT d.*, (SELECT COUNT(*) FROM employees e WHERE e.department_id = d.id AND e.status = 'active') AS emp_count
             FROM departments d
             WHERE {$where}
             ORDER BY {$orderBy} {$orderDir}
             LIMIT :start, :length",
            $params
        );

        return $this->datatableResponse($rows, $filtered, $total, $draw);
    }

    public function stats(): array
    {
        return $this->fetchAll(
            'SELECT d.name, COUNT(e.id) AS emp_count
             FROM departments d
             LEFT JOIN employees e ON e.department_id = d.id AND e.status = \'active\'
             GROUP BY d.id
             ORDER BY emp_count DESC'
        );
    }
}
