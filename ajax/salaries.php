<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAuth();
header('Content-Type: application/json');
if (!csrf_verify()) jsonResponse(['success'=>false,'message'=>'CSRF failed.'],403);

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$ctrl   = new SalaryController();

switch ($action) {
    case 'list':      jsonResponse($ctrl->list($_POST));
    case 'get':       jsonResponse($ctrl->get((int)($_GET['id']??0)));
    case 'save':      jsonResponse($ctrl->save($_POST));
    case 'delete':    jsonResponse($ctrl->delete((int)($_POST['id']??0)));
    case 'mark_paid': jsonResponse($ctrl->markPaid((int)($_POST['id']??0)));
    case 'bulk_delete': jsonResponse($ctrl->bulkDelete((array)($_POST['ids']??[])));
    default: jsonResponse(['success'=>false,'message'=>'Unknown action.'],400);
}
