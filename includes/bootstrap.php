<?php
declare(strict_types=1);

/**
 * bootstrap.php
 * Bootstraps the application: starts session, loads config and core helpers.
 */

// ── Session configuration ──────────────────────────────────────────────────
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Autoload core files ────────────────────────────────────────────────────
$base = dirname(__DIR__);
require_once $base . '/config/database.php';
require_once $base . '/models/BaseModel.php';
require_once $base . '/models/User.php';
require_once $base . '/models/Department.php';
require_once $base . '/models/Designation.php';
require_once $base . '/models/Employee.php';
require_once $base . '/models/Salary.php';
require_once $base . '/models/Attendance.php';
require_once $base . '/models/LeaveRequest.php';
require_once $base . '/models/Notification.php';
require_once $base . '/models/AuditLog.php';
require_once $base . '/models/Setting.php';
require_once $base . '/controllers/AuthController.php';
require_once $base . '/controllers/DashboardController.php';
require_once $base . '/controllers/EmployeeController.php';
require_once $base . '/controllers/DepartmentController.php';
require_once $base . '/controllers/DesignationController.php';
require_once $base . '/controllers/SalaryController.php';
require_once $base . '/controllers/AttendanceController.php';
require_once $base . '/controllers/LeaveController.php';
require_once $base . '/controllers/ProfileController.php';
require_once $base . '/controllers/SettingsController.php';

// ── Core helper functions ──────────────────────────────────────────────────

/** XSS-safe output escaping */
function e(mixed $v): string {
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Redirect and exit */
function redirect(string $url): never {
    header("Location: {$url}");
    exit;
}

/** Returns true if user is logged in */
function isAuthenticated(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/** Require login; redirect to login.php if not authenticated */
function requireAuth(): void {
    if (!isAuthenticated()) {
        redirect('/login.php');
    }
}

/** CSRF token generation */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** CSRF token validation */
function csrf_verify(): bool {
    $token = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/** Send a JSON response and exit */
function jsonResponse(array $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/** Append a line to the application log */
function appLog(string $message, string $level = 'INFO'): void {
    $logDir = dirname(__DIR__) . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    $line = sprintf("[%s] [%s] %s\n", date('Y-m-d H:i:s'), $level, $message);
    file_put_contents($logDir . '/app.log', $line, FILE_APPEND | LOCK_EX);
}

/** Format currency */
function currency(float $amount): string {
    $symbol = '₹';
    return $symbol . number_format($amount, 2);
}

/** Truncate text */
function truncate(string $text, int $length = 60): string {
    return mb_strlen($text) > $length ? mb_substr($text, 0, $length) . '…' : $text;
}

/** Get current user from session */
function currentUser(): ?array {
    if (!isAuthenticated()) return null;
    static $user = null;
    if ($user === null) {
        $model = new User();
        $user  = $model->findById((int) $_SESSION['user_id']);
    }
    return $user;
}
