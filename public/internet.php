<?php
require_once __DIR__ . '/../src/includes/auth.php';
require_once __DIR__ . '/../src/includes/helpers.php';

$_GET['module'] = 'internet';
require_once __DIR__ . '/../src/includes/header.php';
require_once __DIR__ . '/../src/controllers/InternetController.php';

$controller = new InternetController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    switch ($action) {
        case 'store':
            $controller->store();
            break;
        case 'update':
            $controller->update();
            break;
        case 'destroy':
            $controller->destroy();
            break;
    }
} else {
    $pageTitle = 'Internet Predial';
    $data = $controller->index();
    extract($data);
    require_once __DIR__ . '/../src/views/internet/index.php';
}