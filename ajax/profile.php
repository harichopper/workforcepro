<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAuth();
header('Content-Type: application/json');
if (!csrf_verify()) jsonResponse(['success'=>false,'message'=>'CSRF failed.'],403);

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$ctrl   = new ProfileController();

switch ($action) {
    case 'get':             jsonResponse($ctrl->get());
    case 'update':          jsonResponse($ctrl->update($_POST, $_FILES));
    case 'change_password': jsonResponse($ctrl->changePassword($_POST));
    default: jsonResponse(['success'=>false,'message'=>'Unknown action.'],400);
}
