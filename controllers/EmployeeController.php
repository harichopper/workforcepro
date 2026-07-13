<?php declare(strict_types=1);
class EmployeeController {
    private Employee $model;
    private AuditLog $audit;
    public function __construct() { $this->model = new Employee(); $this->audit = new AuditLog(); }

    public function list(array $req): array { return $this->model->tableData($req); }

    public function get(int $id): array {
        $row = $this->model->getProfile($id);
        if (!$row) return ['success' => false, 'message' => 'Not found.'];
        return ['success' => true, 'data' => $row];
    }

    public function save(array $data, array $files = []): array {
        $id      = (int) ($data['id'] ?? 0);
        $errors  = $this->validate($data, $id);
        if ($errors) return ['success' => false, 'errors' => $errors];

        $payload = [
            'first_name'     => trim($data['first_name']),
            'last_name'      => trim($data['last_name']),
            'email'          => trim($data['email']),
            'phone'          => trim($data['phone'] ?? ''),
            'gender'         => $data['gender'] ?? 'male',
            'dob'            => $data['dob'] ?: null,
            'department_id'  => (int) $data['department_id'],
            'designation_id' => (int) $data['designation_id'],
            'hire_date'      => $data['hire_date'],
            'address'        => trim($data['address'] ?? ''),
            'status'         => $data['status'] ?? 'active',
        ];

        // Handle avatar upload
        if (!empty($files['avatar']['name'])) {
            $avatar = $this->uploadAvatar($files['avatar']);
            if ($avatar === null) return ['success' => false, 'message' => 'Invalid avatar file.'];
            $payload['avatar'] = $avatar;
        }

        if ($id > 0) {
            $this->model->update($id, $payload);
            $this->audit->log((int) $_SESSION['user_id'], 'update', 'employees', $id);
            return ['success' => true, 'message' => 'Employee updated successfully.'];
        } else {
            $payload['emp_code'] = $this->model->nextCode();
            $newId = $this->model->insert($payload);
            $this->audit->log((int) $_SESSION['user_id'], 'create', 'employees', $newId);
            return ['success' => true, 'message' => 'Employee added successfully.', 'id' => $newId];
        }
    }

    public function delete(int $id): array {
        if (!$this->model->findById($id)) return ['success' => false, 'message' => 'Not found.'];
        $this->model->delete($id);
        $this->audit->log((int) $_SESSION['user_id'], 'delete', 'employees', $id);
        return ['success' => true, 'message' => 'Employee deleted.'];
    }

    public function bulkDelete(array $ids): array {
        $this->model->bulkDelete($ids);
        $this->audit->log((int) $_SESSION['user_id'], 'bulk_delete', 'employees', null, ['ids' => $ids]);
        return ['success' => true, 'message' => count($ids) . ' employees deleted.'];
    }

    public function toggle(int $id): array {
        $this->model->toggleStatus($id);
        return ['success' => true, 'message' => 'Status updated.'];
    }

    public function export(string $format): void {
        $rows = $this->model->exportAll();
        $headers = ['EMP Code','First Name','Last Name','Email','Phone','Gender','DOB','Hire Date','Status','Department','Designation'];
        if ($format === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="employees_' . date('Y-m-d') . '.csv"');
            $f = fopen('php://output', 'w');
            fputcsv($f, $headers);
            foreach ($rows as $row) fputcsv($f, array_values($row));
            fclose($f);
            exit;
        }
    }

    private function validate(array $d, int $id): array {
        $errors = [];
        if (empty($d['first_name'])) $errors['first_name'] = 'First name is required.';
        if (empty($d['last_name']))  $errors['last_name']  = 'Last name is required.';
        if (empty($d['email']) || !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email required.';
        if (empty($d['department_id'])) $errors['department_id'] = 'Department is required.';
        if (empty($d['designation_id'])) $errors['designation_id'] = 'Designation is required.';
        if (empty($d['hire_date'])) $errors['hire_date'] = 'Hire date is required.';
        return $errors;
    }

    private function uploadAvatar(array $file): ?string {
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        if (!in_array($file['type'], $allowed, true)) return null;
        if ($file['size'] > 2 * 1024 * 1024) return null;
        $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name = 'emp_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = dirname(__DIR__) . '/uploads/' . $name;
        move_uploaded_file($file['tmp_name'], $dest);
        return $name;
    }
}
