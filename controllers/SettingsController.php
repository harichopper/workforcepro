<?php declare(strict_types=1);
class SettingsController {
    private Setting  $model;
    private AuditLog $audit;
    public function __construct() { $this->model=new Setting(); $this->audit=new AuditLog(); }
    public function get(): array { return ['success'=>true,'data'=>$this->model->getAll()]; }
    public function save(array $d): array {
        $allowed=['company_name','company_email','timezone','records_per_page','currency_symbol','leave_annual_quota','leave_sick_quota','leave_casual_quota'];
        $save=[];
        foreach($allowed as $k) { if(isset($d[$k])) $save[$k]=trim($d[$k]); }
        $this->model->saveMany($save);
        $this->audit->log((int)$_SESSION['user_id'],'update','settings');
        return ['success'=>true,'message'=>'Settings saved.'];
    }
    public function backup(): array {
        $db = Database::getConnection();
        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $sql = "-- WorkForce Pro Backup: ".date('Y-m-d H:i:s')."\n\n";
        foreach($tables as $table) {
            $createStmt = $db->query("SHOW CREATE TABLE `{$table}`")->fetch();
            $sql .= "\n-- Table: {$table}\n{$createStmt['Create Table']};\n\n";
            $rows = $db->query("SELECT * FROM `{$table}`")->fetchAll();
            foreach($rows as $row) {
                $vals = array_map(fn($v) => $v===null?'NULL':$db->quote((string)$v), array_values($row));
                $sql .= "INSERT INTO `{$table}` VALUES(".implode(',',$vals).");\n";
            }
        }
        $file = dirname(__DIR__).'/database/backups/backup_'.date('Y-m-d_His').'.sql';
        file_put_contents($file,$sql);
        return ['success'=>true,'message'=>'Backup created.','file'=>basename($file)];
    }
    public function logs(int $lines=100): array {
        $logFile = dirname(__DIR__).'/logs/app.log';
        if(!file_exists($logFile)) return ['success'=>true,'data'=>[]];
        $all = file($logFile,FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        return ['success'=>true,'data'=>array_slice(array_reverse($all),0,$lines)];
    }
    public function notifications(int $userId): array {
        $notif = new Notification();
        return ['success'=>true,'data'=>$notif->forUser($userId,20),'unread'=>$notif->unreadCount($userId)];
    }
    public function markNotificationRead(int $id): array {
        (new Notification())->markRead($id);
        return ['success'=>true,'message'=>'Marked read.'];
    }
}
