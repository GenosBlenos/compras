<?php
session_start();
require_once __DIR__ . '/../src/includes/auth.php';
require_once __DIR__ . '/../src/includes/helpers.php';

require_once __DIR__ . '/../src/controllers/SempararController.php';

// Define variáveis globais necessárias
$pageTitle = 'Sem Parar';
$_GET['module'] = 'semparar';

require_once __DIR__ . '/../src/includes/header.php';

$controller = new SempararController();

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
    $pageTitle = 'Sem Parar';
    $data = $controller->index();
    extract($data);
    require_once __DIR__ . '/../src/views/semparar/index.php';
}