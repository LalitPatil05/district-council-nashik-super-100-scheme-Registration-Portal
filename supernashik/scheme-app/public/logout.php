<?php
require_once __DIR__ . '/_helpers.php';

session_destroy();
header('Location: login.php');
exit;
