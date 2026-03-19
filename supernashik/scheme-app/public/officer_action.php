<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/_helpers.php';

require_role('officer');
verify_csrf();

$appId = trim($_POST['app_id'] ?? '');
$action = trim($_POST['action'] ?? '');
$remarks = trim($_POST['remarks'] ?? '');

$allowed = ['approved', 'rejected', 'sent_back'];
if ($appId === '' || !in_array($action, $allowed, true)) {
  set_flash('Invalid action.');
  header('Location: officer_list.php');
  exit;
}

$stmt = $pdo->prepare('SELECT * FROM applications WHERE application_id = ?');
$stmt->execute([$appId]);
$app = $stmt->fetch();

if (!$app) {
  set_flash('Application not found.');
  header('Location: officer_list.php');
  exit;
}

$pdo->prepare('UPDATE applications SET status = ? WHERE id = ?')
    ->execute([$action, $app['id']]);

$pdo->prepare('INSERT INTO status_history (application_id, status, remarks, changed_by) VALUES (?, ?, ?, ?)')
    ->execute([$app['id'], $action, $remarks, $_SESSION['user_id']]);

set_flash('Decision saved.');
header('Location: officer_view.php?app_id=' . urlencode($appId));
exit;
