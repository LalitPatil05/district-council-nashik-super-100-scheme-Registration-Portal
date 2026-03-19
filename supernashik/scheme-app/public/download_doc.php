<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/_helpers.php';

require_login();

$docId = (int)($_GET['id'] ?? 0);
if ($docId <= 0) {
  http_response_code(400);
  echo 'Document id missing.';
  exit;
}

$stmt = $pdo->prepare('SELECT d.*, a.user_id FROM documents d JOIN applications a ON d.application_id = a.id WHERE d.id = ?');
$stmt->execute([$docId]);
$doc = $stmt->fetch();

if (!$doc) {
  http_response_code(404);
  echo 'Not found.';
  exit;
}

if (($_SESSION['role'] ?? '') !== 'officer' && $doc['user_id'] !== $_SESSION['user_id']) {
  http_response_code(403);
  echo 'Access denied.';
  exit;
}

if (!file_exists($doc['file_path'])) {
  http_response_code(404);
  echo 'File not available.';
  exit;
}

header('Content-Type: ' . $doc['file_type']);
header('Content-Disposition: attachment; filename="' . $doc['file_name'] . '"');
readfile($doc['file_path']);
exit;
