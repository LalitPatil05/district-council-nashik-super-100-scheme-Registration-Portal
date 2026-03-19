<?php
require_once __DIR__ . '/../_helpers.php';
$flash = get_flash();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Scheme Portal</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header">
  <div class="header-inner">
    <div class="brand">सुपर 50 मोफत प्रशिक्षण योजना २०२६-२८</div>
    <nav>
    <a href="index.php">Home</a>
    <?php if (is_logged_in()): ?>
      <?php if (($_SESSION['role'] ?? '') === 'student'): ?>
        <a href="student_form.php">Application</a>
        <a href="student_status.php">Status</a>
      <?php else: ?>
        <a href="officer_list.php">Applications</a>
      <?php endif; ?>
      <a href="logout.php">Logout</a>
    <?php else: ?>
      <a href="login.php">Login</a>
      <a href="register.php">Register</a>
    <?php endif; ?>
    </nav>
  </div>
</header>
<main class="container">
<?php if ($flash): ?>
  <div class="flash"><?php echo h($flash); ?></div>
<?php endif; ?>
