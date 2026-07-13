<?php declare(strict_types=1);
class ProfileController {
    private User $model;
    public function __construct() { $this->model = new User(); }
    public function get(): array {
        $user = $this->model->findById((int)$_SESSION['user_id']);
        return $user ? ['success'=>true,'data'=>$user] : ['success'=>false,'message'=>'Not found.'];
    }
    public function update(array $d, array $files=[]): array {
        $id = (int)$_SESSION['user_id'];
        if (empty($d['name'])) return ['success'=>false,'message'=>'Name required.'];
        $payload = ['name'=>trim($d['name']),'phone'=>trim($d['phone']??''),'bio'=>trim($d['bio']??'')];
        if (!empty($files['avatar']['name'])) {
            $allowed=['image/jpeg','image/png','image/webp'];
            if (!in_array($files['avatar']['type'],$allowed,true)||$files['avatar']['size']>2097152) return ['success'=>false,'message'=>'Invalid avatar.'];
            $ext=pathinfo($files['avatar']['name'],PATHINFO_EXTENSION);
            $name='user_'.bin2hex(random_bytes(8)).'.'.$ext;
            move_uploaded_file($files['avatar']['tmp_name'],dirname(__DIR__).'/uploads/'.$name);
            $payload['avatar']=$name;
        }
        $this->model->updateProfile($id,$payload);
        $_SESSION['user_name']=$payload['name'];
        return ['success'=>true,'message'=>'Profile updated.'];
    }
    public function changePassword(array $d): array {
        $ac = new AuthController();
        return $ac->changePassword($d);
    }
}
