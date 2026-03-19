<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/_helpers.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  verify_csrf();
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $role = trim($_POST['role'] ?? 'student');

  if ($name === '') { $errors[] = 'Name is required.'; }
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Valid email is required.'; }
  if (strlen($password) < 6) { $errors[] = 'Password must be at least 6 characters.'; }
  if ($role !== 'student') { $errors[] = 'Only student registration is allowed.'; }

  if (!$errors) {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
      $errors[] = 'Email already registered.';
    }
  }

  if (!$errors) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
    $stmt->execute([$name, $email, $hash, $role]);
    set_flash('Registration successful. Please login.');
    header('Location: login.php');
    exit;
  }
}

require_once __DIR__ . '/partials/header.php';
?>
<div class="card">
  <h2>Student Registration</h2>
  <?php if ($errors): ?>
    <div class="notice">
      <?php foreach ($errors as $err): ?>
        <div><?php echo h($err); ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?php echo h(csrf_token()); ?>">
    <label>Name</label>
    <input type="text" name="name" required>
    <label>Email</label>
    <input type="email" name="email" required>
    <label>Password</label>
    <input type="password" name="password" required>
    <br><br>
    <button type="submit">Register</button>
  </form>
</div>
<?php
require_once __DIR__ . '/partials/footer.php';
?>
