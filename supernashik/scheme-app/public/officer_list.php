<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/_helpers.php';

require_role('officer');

$stmt = $pdo->query('SELECT a.application_id, a.status, a.submitted_at, u.name FROM applications a JOIN users u ON a.user_id = u.id ORDER BY a.id DESC');
$apps = $stmt->fetchAll();

require_once __DIR__ . '/partials/header.php';
?>
<div class="card">
  <h2>Applications</h2>
  <table class="table">
    <thead>
      <tr>
        <th>Application ID</th>
        <th>Student</th>
        <th>Status</th>
        <th>Submitted</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($apps as $app): ?>
        <tr>
          <td><?php echo h($app['application_id']); ?></td>
          <td><?php echo h($app['name']); ?></td>
          <td><?php echo h($app['status']); ?></td>
          <td><?php echo h($app['submitted_at']); ?></td>
          <td><a href="officer_view.php?app_id=<?php echo h($app['application_id']); ?>">View</a></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php
require_once __DIR__ . '/partials/footer.php';
?>
