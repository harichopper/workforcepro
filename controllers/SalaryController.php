<?php declare(strict_types=1);
class SalaryController {
    private Salary $model;
    private AuditLog $audit;
    public function __construct() { $this->model = new Salary(); $this->audit = new AuditLog(); }
    public function list(array $req): array { return $this->model->tableData($req); }
    public function get(int $id): array { $row=$this->model->findById($id); return $row?['success'=>true,'data'=>$row]:['success'=>false,'message'=>'Not found.']; }
    public function save(array $d): array {
        $id=(int)($d['id']??0);
        if (empty($d['employee_id'])) return ['success'=>false,'errors'=>['employee_id'=>'Employee required.']];
        if (empty($d['pay_month'])) return ['success'=>false,'errors'=>['pay_month'=>'Month required.']];
        $payload=['employee_id'=>(int)$d['employee_id'],'basic_salary'=>(float)($d['basic_salary']??0),'allowances'=>(float)($d['allowances']??0),'deductions'=>(float)($d['deductions']??0),'pay_month'=>$d['pay_month'].'-01','status'=>$d['status']??'pending','notes'=>trim($d['notes']??'')];
        if (($d['status']??'') === 'paid') $payload['paid_at'] = date('Y-m-d H:i:s');
        if ($id>0) { $this->model->update($id,$payload); $this->audit->log((int)$_SESSION['user_id'],'update','salaries',$id); return ['success'=>true,'message'=>'Salary updated.']; }
        $newId=$this->model->insert($payload); $this->audit->log((int)$_SESSION['user_id'],'create','salaries',$newId); return ['success'=>true,'message'=>'Salary record added.'];
    }
    public function delete(int $id): array { $this->model->delete($id); return ['success'=>true,'message'=>'Deleted.']; }
    public function bulkDelete(array $ids): array { $this->model->bulkDelete($ids); return ['success'=>true,'message'=>count($ids).' deleted.']; }
    public function markPaid(int $id): array { $this->model->update($id,['status'=>'paid','paid_at'=>date('Y-m-d H:i:s')]); return ['success'=>true,'message'=>'Marked as paid.']; }
}
