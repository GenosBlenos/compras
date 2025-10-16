<?php
require_once __DIR__ . '/../src/includes/auth.php';
require_once __DIR__ . '/../src/includes/helpers.php';

$_GET['module'] = 'energia';
require_once __DIR__ . '/../src/includes/header.php';
require_once __DIR__ . '/../src/controllers/EnergiaController.php';

$controller = new EnergiaController();

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
    $pageTitle = 'Contas de Energia Elétrica';
    $data = $controller->index();
    extract($data);
    require_once __DIR__ . '/../src/views/energia/index.php';
}

