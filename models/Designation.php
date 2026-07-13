<?php
declare(strict_types=1);

class Designation extends BaseModel
{
    protected string $table = 'designations';

    public function options(int $deptId = 0): array
    {
        if ($deptId > 0) {
            return $this->fetchAll(
                'SELECT id, title FROM designations WHERE department_id = :d AND status = \'active\' ORDER BY title',
                ['d' => $deptId]
            );
        }
        return $this->fetchAll('SELECT id, title, department_id FROM designations WHERE status = \'active\' ORDER BY title');
    }

    public function tableData(array $req): array
    {
        $draw   = (int) ($req['draw'] ?? 1);
        $start  = max(0, (int) ($req['start'] ?? 0));
        $length = min(100, max(10, (int) ($req['length'] ?? 10)));
        $search = trim((string) ($req['search']['value'] ?? ''));

        $where  = '1=1';
        $params = [];
        if ($search !== '') {
            $where       = '(des.title LIKE :s OR d.name LIKE :s)';
            $params['s'] = "%{$search}%";
        }

        $total = $this->countAll();
        $fr    = $this->fetchOne("SELECT COUNT(*) AS total FROM designations des INNER JOIN departments d ON d.id = des.department_id WHERE {$where}", $params);
        $filtered = (int) ($fr['total'] ?? 0);

        $params['start']  = $start;
        $params['length'] = $length;

        $rows = $this->fetchAll(
            "SELECT des.*, d.name AS department_name
             FROM designations des
             INNER JOIN departments d ON d.id = des.department_id
             WHERE {$where}
             ORDER BY des.id DESC
             LIMIT :start, :length",
            $params
        );

        return $this->datatableResponse($rows, $filtered, $total, $draw);
    }
}
