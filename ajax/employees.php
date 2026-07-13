<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAuth();

header('Content-Type: application/json');

if (!csrf_verify()) {
    jsonResponse(['success' => false, 'message' => 'CSRF validation failed.'], 403);
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$ctrl   = new EmployeeController();

switch ($action) {
    case 'list':
        jsonResponse($ctrl->list($_POST));
    case 'get':
        jsonResponse($ctrl->get((int) ($_GET['id'] ?? 0)));
    case 'save':
        jsonResponse($ctrl->save($_POST, $_FILES));
    case 'delete':
        jsonResponse($ctrl->delete((int) ($_POST['id'] ?? 0)));
    case 'bulk_delete':
        jsonResponse($ctrl->bulkDelete((array) ($_POST['ids'] ?? [])));
    case 'toggle':
        jsonResponse($ctrl->toggle((int) ($_POST['id'] ?? 0)));
    case 'options':
        $dept = new Department();
        $desig = new Designation();
        jsonResponse(['success' => true, 'departments' => $dept->options(), 'designations' => $desig->options()]);
    case 'export':
        $ctrl->export($_GET['format'] ?? 'csv');
        break;
    default:
        jsonResponse(['success' => false, 'message' => 'Unknown action.'], 400);
}
