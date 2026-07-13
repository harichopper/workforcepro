<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAuth();
header('Content-Type: application/json');
if (!csrf_verify()) jsonResponse(['success'=>false,'message'=>'CSRF failed.'],403);

$ctrl = new \AuditLog();
$action = $_POST['action'] ?? 'list';
if ($action === 'list') jsonResponse($ctrl->tableData($_POST));
jsonResponse(['success'=>false,'message'=>'Unknown action.'],400);
