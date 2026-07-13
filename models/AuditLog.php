<?php
declare(strict_types=1);

class AuditLog extends BaseModel
{
    protected string $table = 'audit_logs';

    public function log(
        ?int $userId,
        string $action,
        string $entity,
        ?int $entityId = null,
        array $metadata = []
    ): void {
        $this->insert([
            'user_id'    => $userId,
            'action'     => $action,
            'entity'     => $entity,
            'entity_id'  => $entityId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'metadata'   => json_encode($metadata) ?: null,
        ]);
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
            $where       = '(al.action LIKE :s OR al.entity LIKE :s OR u.name LIKE :s)';
            $params['s'] = "%{$search}%";
        }

        $total = $this->countAll();
        $fr    = $this->fetchOne(
            "SELECT COUNT(*) AS total FROM audit_logs al LEFT JOIN users u ON u.id = al.user_id WHERE {$where}",
            $params
        );
        $filtered = (int) ($fr['total'] ?? 0);

        $params['start']  = $start;
        $params['length'] = $length;

        $rows = $this->fetchAll(
            "SELECT al.*, COALESCE(u.name, 'System') AS user_name
             FROM audit_logs al
             LEFT JOIN users u ON u.id = al.user_id
             WHERE {$where}
             ORDER BY al.created_at DESC
             LIMIT :start, :length",
            $params
        );

        return $this->datatableResponse($rows, $filtered, $total, $draw);
    }
}
