<?php declare(strict_types=1);
class LeaveController {
    private LeaveRequest $model;
    private AuditLog     $audit;
    private Notification $notif;
    public function __construct() { $this->model=new LeaveRequest(); $this->audit=new AuditLog(); $this->notif=new Notification(); }
    public function list(array $req): array { return $this->model->tableData($req); }
    public function get(int $id): array { $row=$this->model->findById($id); return $row?['success'=>true,'data'=>$row]:['success'=>false,'message'=>'Not found.']; }
    public function save(array $d): array {
        $id=(int)($d['id']??0);
        if (empty($d['employee_id'])) return ['success'=>false,'errors'=>['employee_id'=>'Employee required.']];
        if (empty($d['from_date'])||empty($d['to_date'])) return ['success'=>false,'errors'=>['from_date'=>'Dates required.']];
        $payload=['employee_id'=>(int)$d['employee_id'],'leave_type'=>$d['leave_type']??'annual','from_date'=>$d['from_date'],'to_date'=>$d['to_date'],'reason'=>trim($d['reason']??''),'status'=>'pending'];
        if ($id>0) { $this->model->update($id,$payload); return ['success'=>true,'message'=>'Leave updated.']; }
        $newId=$this->model->insert($payload); $this->audit->log((int)$_SESSION['user_id'],'create','leave_requests',$newId);
        $this->notif->push((int)$_SESSION['user_id'],'New Leave Request','A new leave request has been submitted.','info');
        return ['success'=>true,'message'=>'Leave request submitted.'];
    }
    public function updateStatus(int $id, string $status): array {
        $this->model->updateStatus($id,$status,(int)$_SESSION['user_id']);
        $this->audit->log((int)$_SESSION['user_id'],$status,'leave_requests',$id);
        return ['success'=>true,'message'=>'Leave '.ucfirst($status).'.'];
    }
    public function delete(int $id): array { $this->model->delete($id); return ['success'=>true,'message'=>'Deleted.']; }
    public function bulkDelete(array $ids): array { $this->model->bulkDelete($ids); return ['success'=>true,'message'=>count($ids).' deleted.']; }
}
