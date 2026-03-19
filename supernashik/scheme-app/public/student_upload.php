<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/_helpers.php';

require_role('student');
verify_csrf();
$config = require __DIR__ . '/../config/app.php';

$appId = trim($_POST['app_id'] ?? '');
if ($appId === '') {
  set_flash('Application id missing.');
  header('Location: student_form.php');
  exit;
}

$stmt = $pdo->prepare('SELECT * FROM applications WHERE application_id = ? AND user_id = ?');
$stmt->execute([$appId, $_SESSION['user_id']]);
$app = $stmt->fetch();

if (!$app || !in_array($app['status'], ['draft', 'sent_back'], true)) {
  set_flash('Upload not allowed.');
  header('Location: student_form.php?app_id=' . urlencode($appId));
  exit;
}

if (!isset($_FILES['doc']) || $_FILES['doc']['error'] !== UPLOAD_ERR_OK) {
  set_flash('File upload failed.');
  header('Location: student_form.php?app_id=' . urlencode($appId));
  exit;
}

if ($_FILES['doc']['size'] > $config['max_upload_size']) {
  set_flash('File too large.');
  header('Location: student_form.php?app_id=' . urlencode($appId));
  exit;
}

if (!is_dir($config['upload_dir']) || !is_writable($config['upload_dir'])) {
  set_flash('Upload folder is not writable. Please check permissions.');
  header('Location: student_form.php?app_id=' . urlencode($appId));
  exit;
}

$mime = null;
if (function_exists('finfo_open')) {
  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime = $finfo->file($_FILES['doc']['tmp_name']);
  if (!in_array($mime, $config['allowed_mime'], true)) {
    set_flash('Invalid file type.');
    header('Location: student_form.php?app_id=' . urlencode($appId));
    exit;
  }
} else {
  $ext = strtolower(pathinfo($_FILES['doc']['name'], PATHINFO_EXTENSION));
  $allowedExt = ['pdf', 'jpg', 'jpeg', 'png'];
  if (!in_array($ext, $allowedExt, true)) {
    set_flash('Invalid file type.');
    header('Location: student_form.php?app_id=' . urlencode($appId));
    exit;
  }
  $mime = $_FILES['doc']['type'] ?? 'application/octet-stream';
}

$docType = trim($_POST['doc_type'] ?? 'other');
if (!array_key_exists($docType, $config['doc_labels'])) {
  $docType = 'other';
}

$original = $_FILES['doc']['name'];
$safeOriginal = preg_replace('/[^A-Za-z0-9._-]/', '_', $original);
$target = rtrim($config['upload_dir'], '/') . '/' . uniqid('doc_', true) . '_' . $safeOriginal;

if (!move_uploaded_file($_FILES['doc']['tmp_name'], $target)) {
  set_flash('Failed to save file.');
  header('Location: student_form.php?app_id=' . urlencode($appId));
  exit;
}

$stmt = $pdo->prepare('SELECT * FROM documents WHERE application_id = ? AND doc_type = ? LIMIT 1');
$stmt->execute([$app['id'], $docType]);
$existing = $stmt->fetch();

if ($existing && file_exists($existing['file_path'])) {
  @unlink($existing['file_path']);
}

if ($existing) {
  $stmt = $pdo->prepare('UPDATE documents SET file_name = ?, file_path = ?, file_type = ?, file_size = ?, uploaded_at = NOW() WHERE id = ?');
  $stmt->execute([$original, $target, $mime, (int)$_FILES['doc']['size'], $existing['id']]);
} else {
  $stmt = $pdo->prepare('INSERT INTO documents (application_id, doc_type, file_name, file_path, file_type, file_size) VALUES (?, ?, ?, ?, ?, ?)');
  $stmt->execute([$app['id'], $docType, $original, $target, $mime, (int)$_FILES['doc']['size']]);
}

set_flash('Document uploaded.');
header('Location: student_form.php?app_id=' . urlencode($appId));
exit;
