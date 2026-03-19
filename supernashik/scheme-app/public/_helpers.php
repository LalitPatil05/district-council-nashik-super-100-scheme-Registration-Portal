<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function h($value) {
  return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function is_logged_in() {
  return isset($_SESSION['user_id']);
}

function require_login() {
  if (!is_logged_in()) {
    header('Location: login.php');
    exit;
  }
}

function require_role($role) {
  if (!is_logged_in() || ($_SESSION['role'] ?? '') !== $role) {
    http_response_code(403);
    echo 'Access denied';
    exit;
  }
}

function generate_app_id() {
  return 'APP' . date('ymd') . rand(1000, 9999);
}

function csrf_token() {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
  }
  return $_SESSION['csrf_token'];
}

function verify_csrf() {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
      http_response_code(400);
      echo 'Invalid CSRF token';
      exit;
    }
  }
}

function set_flash($message) {
  $_SESSION['flash'] = $message;
}

function get_flash() {
  $message = $_SESSION['flash'] ?? '';
  unset($_SESSION['flash']);
  return $message;
}
