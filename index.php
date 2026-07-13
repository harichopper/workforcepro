<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
requireAuth();

$allowed = ['dashboard','employees','departments','designations','salary','attendance','leaves','profile','settings','audit-logs'];
$page    = preg_replace('/[^a-z\-]/', '', $_GET['page'] ?? 'dashboard');
if (!in_array($page, $allowed, true)) $page = '404';

$titles = [
  'dashboard'   => 'Dashboard',
  'employees'   => 'Employees',
  'departments' => 'Departments',
  'designations'=> 'Designations',
  'salary'      => 'Salary & Payroll',
  'attendance'  => 'Attendance',
  'leaves'      => 'Leave Requests',
  'profile'     => 'My Profile',
  'settings'    => 'Settings',
  'audit-logs'  => 'Audit Logs',
];
$pageTitle = $titles[$page] ?? 'Page';

$user      = currentUser();
$notifModel= new Notification();
$unread    = $notifModel->unreadCount((int) $_SESSION['user_id']);

require __DIR__ . '/views/layout.php';
