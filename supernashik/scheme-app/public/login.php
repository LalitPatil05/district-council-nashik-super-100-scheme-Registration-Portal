<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/_helpers.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $role = trim($_POST['role'] ?? '');

  if ($email === '' || $password === '' || $role === '') {
    $errors[] = 'Email, password, and role are required.';
  } else {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
      if ($user['role'] !== $role) {
        $errors[] = 'Selected role does not match this account.';
      } else {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['role'] = $user['role'];
      $_SESSION['name'] = $user['name'];
      set_flash('Login successful.');
      header('Location: index.php');
      exit;
      }
    }

    $errors[] = 'Invalid credentials.';
  }
}

require_once __DIR__ . '/partials/header.php';
?>
<div class="card">
  <h2>Login</h2>
  <?php if ($errors): ?>
    <div class="notice">
      <?php foreach ($errors as $err): ?>
        <div><?php echo h($err); ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
    <label>Role</label>
    <select name="role" required>
      <option value="">Select role</option>
      <option value="student">Student</option>
      <option value="officer">Officer</option>
    </select>
    <label>Email</label>
    <input type="email" name="email" required>
    <label>Password</label>
    <input type="password" name="password" required>
    <br><br>
    <button type="submit">Login</button>
  </form>
</div>
<?php
require_once __DIR__ . '/partials/footer.php';
?>
