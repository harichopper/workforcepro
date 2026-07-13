<?php
declare(strict_types=1);

class Salary extends BaseModel
{
    protected string $table = 'salaries';

    public function tableData(array $req): array
    {
        $draw   = (int) ($req['draw'] ?? 1);
        $start  = max(0, (int) ($req['start'] ?? 0));
        $length = min(100, max(10, (int) ($req['length'] ?? 10)));
        $search = trim((string) ($req['search']['value'] ?? ''));
        $month  = trim((string) ($req['pay_month'] ?? ''));

        $conditions = [];
        $params     = [];
        if ($search !== '') {
            $conditions[]  = '(e.first_name LIKE :s OR e.last_name LIKE :s OR e.emp_code LIKE :s)';
            $params['s']   = "%{$search}%";
        }
        if ($month !== '') {
            $conditions[]       = 'DATE_FORMAT(s.pay_month, \'%Y-%m\') = :month';
            $params['month']    = $month;
        }
        $where = $conditions ? implode(' AND ', $conditions) : '1=1';

        $total = $this->countAll();
        $fr    = $this->fetchOne(
            "SELECT COUNT(*) AS total FROM salaries s
             INNER JOIN employees e ON e.id = s.employee_id
             WHERE {$where}",
            $params
        );
        $filtered = (int) ($fr['total'] ?? 0);

        $params['start']  = $start;
        $params['length'] = $length;

        $rows = $this->fetchAll(
            "SELECT s.*, CONCAT(e.first_name, ' ', e.last_name) AS emp_name,
                    e.emp_code, d.name AS department_name
             FROM salaries s
             INNER JOIN employees e ON e.id = s.employee_id
             INNER JOIN departments d ON d.id = e.department_id
             WHERE {$where}
             ORDER BY s.pay_month DESC, e.first_name ASC
             LIMIT :start, :length",
            $params
        );

        return $this->datatableResponse($rows, $filtered, $total, $draw);
    }

    public function totalPaid(): float
    {
        $row = $this->fetchOne(
            'SELECT SUM(net_salary) AS total FROM salaries WHERE status = \'paid\''
        );
        return (float) ($row['total'] ?? 0);
    }

    public function pendingCount(): int
    {
        $row = $this->fetchOne('SELECT COUNT(*) AS total FROM salaries WHERE status = \'pending\'');
        return (int) ($row['total'] ?? 0);
    }

    public function monthlyPayroll(int $months = 6): array
    {
        return $this->fetchAll(
            'SELECT DATE_FORMAT(pay_month, \'%Y-%m\') AS month, SUM(net_salary) AS total
             FROM salaries
             WHERE pay_month >= DATE_SUB(CURDATE(), INTERVAL :m MONTH)
             GROUP BY month
             ORDER BY month',
            ['m' => $months]
        );
    }
}
