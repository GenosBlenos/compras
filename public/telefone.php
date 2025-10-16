<?php
session_start();
require_once __DIR__ . '/../src/includes/auth.php';
require_once __DIR__ . '/../src/includes/helpers.php';

require_once __DIR__ . '/../src/controllers/TelefoneController.php';

// Define variáveis globais necessárias
$pageTitle = 'Telefonia Fixa';
$_GET['module'] = 'telefone';

require_once __DIR__ . '/../src/includes/header.php';

$controller = new TelefoneController();

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
    $pageTitle = 'Telefonia Fixa';
    $data = $controller->index();
    extract($data);
    require_once __DIR__ . '/../src/views/telefone/index.php';
}