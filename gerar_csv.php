<?php
require_once __DIR__ . '/src/includes/auth.php';
require_once __DIR__ . '/src/includes/header.php';
require_once __DIR__ . '/app/conexao.php';
require_once __DIR__ . '/contas.php';

$pageTitle = 'Gerar CSV';

$contas = getContasFromDatabase($pdo);
gerarCSVContas($contas);
exit;
?>