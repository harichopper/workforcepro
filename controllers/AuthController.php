<?php declare(strict_types=1);
class AuthController {
    private User $user;
    public function __construct() { $this->user = new User(); }

    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (!$email || !$password) { $_SESSION['auth_error'] = 'All fields required.'; return; }
        $user = $this->user->findByEmail($email);
        if (!$user || !password_verify($password, $user['password']) || !$user['is_active']) {
            $_SESSION['auth_error'] = 'Invalid credentials or account inactive.';
            return;
        }
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $this->user->updateLastLogin((int) $user['id']);
        (new AuditLog())->log((int) $user['id'], 'login', 'users', (int) $user['id']);
        redirect('/index.php');
    }

    public function logout(): void {
        (new AuditLog())->log((int) ($_SESSION['user_id'] ?? 0), 'logout', 'users');
        session_destroy();
        redirect('/login.php');
    }

    public function changePassword(array $data): array {
        $user = $this->user->findById((int) $_SESSION['user_id']);
        if (!$user) return ['success' => false, 'message' => 'User not found.'];
        if (!password_verify($data['current_password'] ?? '', $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect.'];
        }
        if (strlen($data['new_password'] ?? '') < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
        }
        $this->user->updatePassword((int) $user['id'], password_hash($data['new_password'], PASSWORD_DEFAULT));
        return ['success' => true, 'message' => 'Password updated successfully.'];
    }
}
