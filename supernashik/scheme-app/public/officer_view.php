<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/_helpers.php';

require_role('officer');

$appId = trim($_GET['app_id'] ?? '');
if ($appId === '') {
  set_flash('Application id missing.');
  header('Location: officer_list.php');
  exit;
}

$stmt = $pdo->prepare('SELECT a.*, u.name FROM applications a JOIN users u ON a.user_id = u.id WHERE a.application_id = ?');
$stmt->execute([$appId]);
$app = $stmt->fetch();

if (!$app) {
  set_flash('Application not found.');
  header('Location: officer_list.php');
  exit;
}

$config = require __DIR__ . '/../config/app.php';

$stmt = $pdo->prepare('SELECT * FROM documents WHERE application_id = ? ORDER BY uploaded_at DESC');
$stmt->execute([$app['id']]);
$docs = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT sh.*, u.name FROM status_history sh JOIN users u ON sh.changed_by = u.id WHERE sh.application_id = ? ORDER BY sh.changed_at DESC');
$stmt->execute([$app['id']]);
$history = $stmt->fetchAll();

require_once __DIR__ . '/partials/header.php';
?>
<div class="card">
  <h2>Application <?php echo h($app['application_id']); ?></h2>
  <p><strong>Student:</strong> <?php echo h($app['name']); ?></p>
  <p><strong>Status:</strong> <?php echo h($app['status']); ?></p>

  <h3>Form Data</h3>
  <div class="form-row">
    <div><strong>Full Name:</strong> <?php echo h($app['full_name']); ?></div>
    <div><strong>DOB:</strong> <?php echo h($app['dob']); ?></div>
  </div>
  <div class="form-row">
    <div><strong>Gender:</strong> <?php echo h($app['gender']); ?></div>
    <div><strong>Email:</strong> <?php echo h($app['email']); ?></div>
  </div>
  <div class="form-row">
    <div><strong>Mobile:</strong> <?php echo h($app['mobile']); ?></div>
    <div><strong>Alternate Mobile:</strong> <?php echo h($app['alt_mobile']); ?></div>
  </div>
  <p><strong>Address:</strong> <?php echo h($app['address']); ?></p>
  <div class="form-row">
    <div><strong>Village:</strong> <?php echo h($app['village']); ?></div>
    <div><strong>Taluka:</strong> <?php echo h($app['taluka']); ?></div>
  </div>
  <div class="form-row">
    <div><strong>Pin Code:</strong> <?php echo h($app['pin_code']); ?></div>
    <div><strong>Aadhaar:</strong> <?php echo h($app['aadhaar']); ?></div>
  </div>
  <div class="form-row">
    <div><strong>School:</strong> <?php echo h($app['school_name']); ?></div>
    <div><strong>Appearing 10th:</strong> <?php echo h($app['appearing_10th']); ?></div>
  </div>
  <p><strong>School Address:</strong> <?php echo h($app['school_address']); ?></p>
  <div class="form-row">
    <div><strong>Category:</strong> <?php echo h($app['category']); ?></div>
    <div><strong>Disability:</strong> <?php echo h($app['disability']); ?></div>
  </div>
  <div class="form-row">
    <div><strong>UDID:</strong> <?php echo h($app['udid_number']); ?></div>
    <div><strong>Income < 2L:</strong> <?php echo h($app['family_income_lt2l']); ?></div>
  </div>
  <div class="form-row">
    <div><strong>Course Interest:</strong> <?php echo h($app['course_interest']); ?></div>
    <div><strong>Exam City:</strong> <?php echo h($app['exam_city']); ?></div>
  </div>
</div>

<div class="card">
  <h3>Documents</h3>
  <?php if (!$docs): ?>
    <p>No documents uploaded.</p>
  <?php else: ?>
    <table class="table">
      <thead>
        <tr>
          <th>Type</th>
          <th>File</th>
          <th>Uploaded</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($docs as $doc): ?>
          <tr>
            <td><?php echo h($config['doc_labels'][$doc['doc_type']] ?? $doc['doc_type']); ?></td>
            <td><?php echo h($doc['file_name']); ?></td>
            <td><?php echo h($doc['uploaded_at']); ?></td>
            <td><a href="download_doc.php?id=<?php echo h($doc['id']); ?>">Download</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <?php if ($app['pdf_path']): ?>
    <p><a href="download_pdf.php?app_id=<?php echo h($app['application_id']); ?>">Download PDF</a></p>
  <?php endif; ?>
</div>

<div class="card">
  <h3>Officer Action</h3>
  <form method="post" action="officer_action.php">
    <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
    <input type="hidden" name="app_id" value="<?php echo h($app['application_id']); ?>">
    <label>Action</label>
    <select name="action" required>
      <option value="approved">Approve</option>
      <option value="rejected">Reject</option>
      <option value="sent_back">Send Back</option>
    </select>
    <label>Remarks</label>
    <textarea name="remarks" rows="3"></textarea>
    <br>
    <button type="submit">Submit Decision</button>
  </form>
</div>

<div class="card">
  <h3>Status History</h3>
  <?php if (!$history): ?>
    <p>No history yet.</p>
  <?php else: ?>
    <table class="table">
      <thead>
        <tr>
          <th>Status</th>
          <th>Remarks</th>
          <th>By</th>
          <th>Time</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($history as $row): ?>
          <tr>
            <td><?php echo h($row['status']); ?></td>
            <td><?php echo h($row['remarks']); ?></td>
            <td><?php echo h($row['name']); ?></td>
            <td><?php echo h($row['changed_at']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/partials/footer.php';
?>
