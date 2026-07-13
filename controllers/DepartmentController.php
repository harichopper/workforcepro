<?php declare(strict_types=1);
class DepartmentController {
    private Department $model;
    private AuditLog   $audit;
    public function __construct() { $this->model = new Department(); $this->audit = new AuditLog(); }
    public function list(array $req): array { return $this->model->tableData($req); }
    public function options(): array { return $this->model->options(); }
    public function get(int $id): array {
        $row = $this->model->findById($id);
        return $row ? ['success'=>true,'data'=>$row] : ['success'=>false,'message'=>'Not found.'];
    }
    public function save(array $d): array {
        $id = (int)($d['id'] ?? 0);
        if (empty($d['name'])) return ['success'=>false,'errors'=>['name'=>'Name required.']];
        if (empty($d['code'])) return ['success'=>false,'errors'=>['code'=>'Code required.']];
        $payload = ['name'=>trim($d['name']),'code'=>strtoupper(trim($d['code'])),'manager_name'=>trim($d['manager_name']??''),'description'=>trim($d['description']??''),'status'=>$d['status']??'active'];
        if ($id > 0) { $this->model->update($id,$payload); $this->audit->log((int)$_SESSION['user_id'],'update','departments',$id); return ['success'=>true,'message'=>'Department updated.']; }
        $newId = $this->model->insert($payload); $this->audit->log((int)$_SESSION['user_id'],'create','departments',$newId);
        return ['success'=>true,'message'=>'Department added.'];
    }
    public function delete(int $id): array { $this->model->delete($id); $this->audit->log((int)$_SESSION['user_id'],'delete','departments',$id); return ['success'=>true,'message'=>'Deleted.']; }
    public function toggle(int $id): array { $this->model->toggleStatus($id); return ['success'=>true,'message'=>'Status updated.']; }
    public function bulkDelete(array $ids): array { $this->model->bulkDelete($ids); return ['success'=>true,'message'=>count($ids).' deleted.']; }
}
