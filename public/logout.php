<?php
require_once __DIR__ . '/../src/includes/auth.php';
session_destroy();
header('Location: /compras/login.php');
exit;
