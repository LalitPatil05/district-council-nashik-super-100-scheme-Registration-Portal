<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/_helpers.php';

require_role('student');

$stmt = $pdo->prepare('SELECT application_id, status, submitted_at, pdf_path FROM applications WHERE user_id = ? ORDER BY id DESC');
$stmt->execute([$_SESSION['user_id']]);
$apps = $stmt->fetchAll();

require_once __DIR__ . '/partials/header.php';
?>
<div class="card">
  <h2>My Applications</h2>
  <?php if (!$apps): ?>
    <p>No applications yet. <a href="student_form.php">Create one</a>.</p>
  <?php else: ?>
    <table class="table">
      <thead>
        <tr>
          <th>Application ID</th>
          <th>Status</th>
          <th>Submitted</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($apps as $app): ?>
          <tr>
            <td><?php echo h($app['application_id']); ?></td>
            <td><?php echo h($app['status']); ?></td>
            <td><?php echo h($app['submitted_at']); ?></td>
            <td>
              <a href="student_form.php?app_id=<?php echo h($app['application_id']); ?>">View</a>
              <?php if ($app['pdf_path']): ?>
                | <a href="download_pdf.php?app_id=<?php echo h($app['application_id']); ?>">PDF</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
<?php
require_once __DIR__ . '/partials/footer.php';
?>
