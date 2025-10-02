<?php
// Verifica se o script está sendo acessado diretamente.
// Arquivos em 'includes' não devem ser acessíveis pela URL.
if (basename($_SERVER['SCRIPT_NAME']) === basename(__FILE__)) {
    // Se for acesso direto, redireciona para a página de aviso.
    header('Location: ../includes/important.php');
    exit;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redireciona para login se não estiver logado
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header('Location: /login.php');
    exit;
}
