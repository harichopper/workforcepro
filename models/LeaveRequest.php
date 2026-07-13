<?php
declare(strict_types=1);

class LeaveRequest extends BaseModel
{
    protected string $table = 'leave_requests';

    public function tableData(array $req): array
    {
        $draw   = (int) ($req['draw'] ?? 1);
        $start  = max(0, (int) ($req['start'] ?? 0));
        $length = min(100, max(10, (int) ($req['length'] ?? 10)));
        $search = trim((string) ($req['search']['value'] ?? ''));
        $status = trim((string) ($req['leave_status'] ?? ''));

        $conditions = [];
        $params     = [];
        if ($search !== '') {
            $conditions[]  = '(e.first_name LIKE :s OR e.last_name LIKE :s OR lr.leave_type LIKE :s)';
            $params['s']   = "%{$search}%";
        }
        if ($status !== '') {
            $conditions[]       = 'lr.status = :st';
            $params['st']       = $status;
        }
        $where = $conditions ? implode(' AND ', $conditions) : '1=1';

        $total = $this->countAll();
        $fr    = $this->fetchOne(
            "SELECT COUNT(*) AS total FROM leave_requests lr
             INNER JOIN employees e ON e.id = lr.employee_id
             WHERE {$where}",
            $params
        );
        $filtered = (int) ($fr['total'] ?? 0);

        $params['start']  = $start;
        $params['length'] = $length;

        $rows = $this->fetchAll(
            "SELECT lr.*, CONCAT(e.first_name, ' ', e.last_name) AS emp_name,
                    e.emp_code, d.name AS department_name
             FROM leave_requests lr
             INNER JOIN employees e ON e.id = lr.employee_id
             INNER JOIN departments d ON d.id = e.department_id
             WHERE {$where}
             ORDER BY lr.created_at DESC
             LIMIT :start, :length",
            $params
        );

        return $this->datatableResponse($rows, $filtered, $total, $draw);
    }

    public function pendingCount(): int
    {
        $row = $this->fetchOne('SELECT COUNT(*) AS total FROM leave_requests WHERE status = \'pending\'');
        return (int) ($row['total'] ?? 0);
    }

    public function updateStatus(int $id, string $status, int $reviewerId): bool
    {
        return $this->execute(
            'UPDATE leave_requests SET status = :status, reviewed_by = :reviewer, reviewed_at = NOW() WHERE id = :id',
            ['status' => $status, 'reviewer' => $reviewerId, 'id' => $id]
        );
    }
}
