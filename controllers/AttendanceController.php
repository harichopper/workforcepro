<?php declare(strict_types=1);
class AttendanceController {
    private Attendance $model;
    private AuditLog   $audit;
    public function __construct() { $this->model = new Attendance(); $this->audit = new AuditLog(); }
    public function list(array $req): array { return $this->model->tableData($req); }
    public function get(int $id): array { $row=$this->model->findById($id); return $row?['success'=>true,'data'=>$row]:['success'=>false,'message'=>'Not found.']; }
    public function save(array $d): array {
        $id=(int)($d['id']??0);
        if (empty($d['employee_id'])) return ['success'=>false,'errors'=>['employee_id'=>'Employee required.']];
        if (empty($d['date'])) return ['success'=>false,'errors'=>['date'=>'Date required.']];
        $payload=['employee_id'=>(int)$d['employee_id'],'date'=>$d['date'],'check_in'=>$d['check_in']?:null,'check_out'=>$d['check_out']?:null,'status'=>$d['status']??'present','remarks'=>trim($d['remarks']??'')];
        if ($id>0) { $this->model->update($id,$payload); $this->audit->log((int)$_SESSION['user_id'],'update','attendance',$id); return ['success'=>true,'message'=>'Attendance updated.']; }
        $newId=$this->model->insert($payload); $this->audit->log((int)$_SESSION['user_id'],'create','attendance',$newId); return ['success'=>true,'message'=>'Attendance recorded.'];
    }
    public function delete(int $id): array { $this->model->delete($id); return ['success'=>true,'message'=>'Deleted.']; }
    public function bulkDelete(array $ids): array { $this->model->bulkDelete($ids); return ['success'=>true,'message'=>count($ids).' deleted.']; }
    public function todaySummary(): array { return $this->model->todaySummary(); }
}
