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
  set_flash('Submit not allowed.');
  header('Location: student_form.php?app_id=' . urlencode($appId));
  exit;
}

$stmt = $pdo->prepare('SELECT doc_type FROM documents WHERE application_id = ?');
$stmt->execute([$app['id']]);
$uploadedTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
$missing = [];
foreach ($config['required_docs'] as $req) {
  if (!in_array($req, $uploadedTypes, true)) {
    $missing[] = $config['doc_labels'][$req] ?? $req;
  }
}
if ($missing) {
  set_flash('Please upload required documents: ' . implode(', ', $missing));
  header('Location: student_form.php?app_id=' . urlencode($appId));
  exit;
}

$pdo->prepare('UPDATE applications SET status = "submitted", submitted_at = NOW() WHERE id = ?')
    ->execute([$app['id']]);

$pdo->prepare('INSERT INTO status_history (application_id, status, remarks, changed_by) VALUES (?, ?, ?, ?)')
    ->execute([$app['id'], 'submitted', 'Submitted by student', $_SESSION['user_id']]);

$notice = 'Application submitted.';
$pdfPath = null;
$vendor = __DIR__ . '/../vendor/autoload.php';
if (file_exists($vendor)) {
  require_once $vendor;
  if (class_exists('FPDF')) {
    if (!is_dir($config['pdf_dir']) || !is_writable($config['pdf_dir'])) {
      $notice .= ' PDF folder is not writable. Check storage/pdfs permissions.';
    } else {
      $pdf = new FPDF();
      $pdf->AddPage();
      $pdf->SetFont('Arial', 'B', 14);
      $pdf->Cell(0, 10, 'Application ' . $appId, 0, 1);

      $pdf->SetFont('Arial', '', 11);
      $pdf->Cell(0, 8, 'Full Name: ' . $app['full_name'], 0, 1);
      $pdf->Cell(0, 8, 'Date of Birth: ' . $app['dob'], 0, 1);
      $pdf->Cell(0, 8, 'Gender: ' . $app['gender'], 0, 1);
      $pdf->Cell(0, 8, 'Mobile: ' . $app['mobile'], 0, 1);
      $pdf->Cell(0, 8, 'Email: ' . $app['email'], 0, 1);
      $pdf->MultiCell(0, 8, 'Address: ' . $app['address']);
      $pdf->Cell(0, 8, 'Taluka: ' . $app['taluka'], 0, 1);
      $pdf->Cell(0, 8, 'Aadhaar: ' . $app['aadhaar'], 0, 1);
      $pdf->Cell(0, 8, 'Course Interest: ' . $app['course_interest'], 0, 1);

      $pdfPath = rtrim($config['pdf_dir'], '/') . '/' . $appId . '.pdf';
      $pdf->Output('F', $pdfPath);

      $pdo->prepare('UPDATE applications SET pdf_path = ? WHERE id = ?')
          ->execute([$pdfPath, $app['id']]);
    }
  } else {
    $notice .= ' PDF generation not configured (FPDF missing).';
  }
} else {
  $notice .= ' PDF generation not configured. Run composer install.';
}

set_flash($notice);
header('Location: student_status.php');
exit;
