<?php declare(strict_types=1);
class DesignationController {
    private Designation $model;
    private AuditLog    $audit;
    public function __construct() { $this->model = new Designation(); $this->audit = new AuditLog(); }
    public function list(array $req): array { return $this->model->tableData($req); }
    public function options(int $deptId = 0): array { return $this->model->options($deptId); }
    public function get(int $id): array {
        $row = $this->model->findById($id);
        return $row ? ['success'=>true,'data'=>$row] : ['success'=>false,'message'=>'Not found.'];
    }
    public function save(array $d): array {
        $id = (int)($d['id']??0);
        if (empty($d['title'])) return ['success'=>false,'errors'=>['title'=>'Title required.']];
        if (empty($d['department_id'])) return ['success'=>false,'errors'=>['department_id'=>'Dept required.']];
        $payload = ['department_id'=>(int)$d['department_id'],'title'=>trim($d['title']),'level'=>$d['level']??'mid','status'=>$d['status']??'active'];
        if ($id > 0) { $this->model->update($id,$payload); $this->audit->log((int)$_SESSION['user_id'],'update','designations',$id); return ['success'=>true,'message'=>'Updated.']; }
        $newId = $this->model->insert($payload); $this->audit->log((int)$_SESSION['user_id'],'create','designations',$newId);
        return ['success'=>true,'message'=>'Added.'];
    }
    public function delete(int $id): array { $this->model->delete($id); return ['success'=>true,'message'=>'Deleted.']; }
    public function toggle(int $id): array { $this->model->toggleStatus($id); return ['success'=>true,'message'=>'Status updated.']; }
    public function bulkDelete(array $ids): array { $this->model->bulkDelete($ids); return ['success'=>true,'message'=>count($ids).' deleted.']; }
}
