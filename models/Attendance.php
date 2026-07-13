<?php
declare(strict_types=1);

class Attendance extends BaseModel
{
    protected string $table = 'attendance';

    public function tableData(array $req): array
    {
        $draw   = (int) ($req['draw'] ?? 1);
        $start  = max(0, (int) ($req['start'] ?? 0));
        $length = min(100, max(10, (int) ($req['length'] ?? 10)));
        $search = trim((string) ($req['search']['value'] ?? ''));
        $date   = trim((string) ($req['att_date'] ?? ''));

        $conditions = [];
        $params     = [];
        if ($search !== '') {
            $conditions[]  = '(e.first_name LIKE :s OR e.last_name LIKE :s OR e.emp_code LIKE :s)';
            $params['s']   = "%{$search}%";
        }
        if ($date !== '') {
            $conditions[]     = 'a.date = :dt';
            $params['dt']     = $date;
        }
        $where = $conditions ? implode(' AND ', $conditions) : '1=1';

        $total = $this->countAll();
        $fr    = $this->fetchOne(
            "SELECT COUNT(*) AS total FROM attendance a
             INNER JOIN employees e ON e.id = a.employee_id
             WHERE {$where}",
            $params
        );
        $filtered = (int) ($fr['total'] ?? 0);

        $params['start']  = $start;
        $params['length'] = $length;

        $rows = $this->fetchAll(
            "SELECT a.*, CONCAT(e.first_name, ' ', e.last_name) AS emp_name,
                    e.emp_code, d.name AS department_name
             FROM attendance a
             INNER JOIN employees e ON e.id = a.employee_id
             INNER JOIN departments d ON d.id = e.department_id
             WHERE {$where}
             ORDER BY a.date DESC, e.first_name ASC
             LIMIT :start, :length",
            $params
        );

        return $this->datatableResponse($rows, $filtered, $total, $draw);
    }

    public function todaySummary(): array
    {
        $row = $this->fetchOne(
            'SELECT
               SUM(CASE WHEN status = \'present\' THEN 1 ELSE 0 END) AS present,
               SUM(CASE WHEN status = \'absent\' THEN 1 ELSE 0 END) AS absent,
               SUM(CASE WHEN status = \'half-day\' THEN 1 ELSE 0 END) AS halfday,
               SUM(CASE WHEN status = \'leave\' THEN 1 ELSE 0 END) AS on_leave,
               COUNT(*) AS total
             FROM attendance
             WHERE date = CURDATE()'
        );
        return $row ?? [];
    }

    public function weeklyTrend(): array
    {
        return $this->fetchAll(
            'SELECT date,
                    SUM(CASE WHEN status = \'present\' THEN 1 ELSE 0 END) AS present,
                    SUM(CASE WHEN status = \'absent\' THEN 1 ELSE 0 END) AS absent
             FROM attendance
             WHERE date >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
             GROUP BY date
             ORDER BY date'
        );
    }

    public function presentToday(): int
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS total FROM attendance WHERE date = CURDATE() AND status = \'present\''
        );
        return (int) ($row['total'] ?? 0);
    }
}
