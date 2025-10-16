<?php
session_start();
require_once __DIR__ . '/../src/includes/auth.php';
require_once __DIR__ . '/../src/includes/helpers.php';
require_once __DIR__ . '/../src/controllers/AguaController.php';
// Define variáveis globais necessárias
$pageTitle = 'Água Predial';
$_GET['module'] = 'agua';

require_once __DIR__ . '/../src/includes/header.php';

$controller = new AguaController();

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
    $pageTitle = 'Água Predial';
    $data = $controller->index();
    extract($data);
    require_once __DIR__ . '/../src/views/agua/index.php';
}