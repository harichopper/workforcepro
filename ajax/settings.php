<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAuth();
header('Content-Type: application/json');
if (!csrf_verify()) jsonResponse(['success'=>false,'message'=>'CSRF failed.'],403);

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$ctrl   = new SettingsController();

switch ($action) {
    case 'get':     jsonResponse($ctrl->get());
    case 'save':    jsonResponse($ctrl->save($_POST));
    case 'backup':  jsonResponse($ctrl->backup());
    case 'logs':    jsonResponse($ctrl->logs());
    case 'notifications': jsonResponse($ctrl->notifications((int)$_SESSION['user_id']));
    case 'mark_notification_read': jsonResponse($ctrl->markNotificationRead((int)($_POST['id']??0)));
    default: jsonResponse(['success'=>false,'message'=>'Unknown action.'],400);
}
