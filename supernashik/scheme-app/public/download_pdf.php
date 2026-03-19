<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/_helpers.php';

require_login();

$appId = trim($_GET['app_id'] ?? '');
if ($appId === '') {
  http_response_code(400);
  echo 'Application id missing.';
  exit;
}

$stmt = $pdo->prepare('SELECT * FROM applications WHERE application_id = ?');
$stmt->execute([$appId]);
$app = $stmt->fetch();

if (!$app) {
  http_response_code(404);
  echo 'Not found.';
  exit;
}

if (($_SESSION['role'] ?? '') !== 'officer' && $app['user_id'] !== $_SESSION['user_id']) {
  http_response_code(403);
  echo 'Access denied.';
  exit;
}

if (!$app['pdf_path'] || !file_exists($app['pdf_path'])) {
  http_response_code(404);
  echo 'PDF not available.';
  exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $appId . '.pdf"');
readfile($app['pdf_path']);
exit;
