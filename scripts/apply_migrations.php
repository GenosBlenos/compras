<?php
// apply_migrations.php
// Run this script from PHP CLI to create minimal required tables.

// Use existing connection setup in app/conexao.php which defines $pdo (and $conn)
require_once __DIR__ . '/../app/conexao.php';

if (!isset($pdo) || !$pdo) {
    echo "PDO connection not available. Check src/includes/db_config.php\n";
    exit(1);
}

$statements = [
    // categorias
    "CREATE TABLE IF NOT EXISTS categorias (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL UNIQUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // faturas
    "CREATE TABLE IF NOT EXISTS faturas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        unidade_id INT,
        categoria_id INT,
        fornecedor_id INT,
        data_emissao DATE,
        data_vencimento DATE NOT NULL,
        valor_total DECIMAL(10,2) NOT NULL,
        numero_fatura VARCHAR(255),
        arquivo_pdf VARCHAR(255),
        observacoes TEXT,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // fatura_detalhes
    "CREATE TABLE IF NOT EXISTS fatura_detalhes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fatura_id INT NOT NULL,
        chave VARCHAR(255) NOT NULL,
        valor VARCHAR(255) NOT NULL,
        FOREIGN KEY (fatura_id) REFERENCES faturas(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // energia (minimal)
    "CREATE TABLE IF NOT EXISTS energia (
        id_energia INT AUTO_INCREMENT PRIMARY KEY,
        mes VARCHAR(7),
        unidade VARCHAR(255),
        consumo_kwh DECIMAL(10,2),
        valor_fatura DECIMAL(10,2),
        valor DECIMAL(10,2),
        multa DECIMAL(10,2) DEFAULT 0,
        total DECIMAL(10,2),
        status ENUM('pendente','pago') DEFAULT 'pendente',
        data_vencimento DATE,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // telefone (minimal)
    "CREATE TABLE IF NOT EXISTS telefone (
        id_telefone INT AUTO_INCREMENT PRIMARY KEY,
        mes VARCHAR(7),
        unidade VARCHAR(255),
        numero_linha VARCHAR(50),
        valor_fatura DECIMAL(10,2),
        valor DECIMAL(10,2),
        multa DECIMAL(10,2) DEFAULT 0,
        total DECIMAL(10,2),
        status ENUM('pendente','pago') DEFAULT 'pendente',
        data_vencimento DATE,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // internet (minimal)
    "CREATE TABLE IF NOT EXISTS internet (
        id_internet INT AUTO_INCREMENT PRIMARY KEY,
        mes VARCHAR(7),
        unidade VARCHAR(255),
        valor_fatura DECIMAL(10,2),
        valor DECIMAL(10,2),
        multa DECIMAL(10,2) DEFAULT 0,
        total DECIMAL(10,2),
        status ENUM('pendente','pago') DEFAULT 'pendente',
        data_vencimento DATE,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // semparar (minimal)
    "CREATE TABLE IF NOT EXISTS semparar (
        id_semparar INT AUTO_INCREMENT PRIMARY KEY,
        mes VARCHAR(7),
        unidade VARCHAR(255),
        valor_fatura DECIMAL(10,2),
        valor DECIMAL(10,2),
        multa DECIMAL(10,2) DEFAULT 0,
        total DECIMAL(10,2),
        status ENUM('pendente','pago') DEFAULT 'pendente',
        data_vencimento DATE,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // agua (minimal)
    "CREATE TABLE IF NOT EXISTS agua (
        id_agua INT AUTO_INCREMENT PRIMARY KEY,
        mes VARCHAR(7),
        unidade VARCHAR(255),
        consumo_m3 DECIMAL(10,2),
        valor DECIMAL(10,2),
        valor_fatura DECIMAL(10,2),
        multa DECIMAL(10,2) DEFAULT 0,
        total DECIMAL(10,2),
        status ENUM('pendente','pago') DEFAULT 'pendente',
        data_vencimento DATE,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

try {
    foreach ($statements as $sql) {
        echo "Executing...\n";
        $pdo->exec($sql);
        echo "OK\n";
    }

    // Popula categorias padrão
    $insert = $pdo->prepare("INSERT IGNORE INTO categorias (nome) VALUES (:n)");
    $defaults = ['agua','energia','telefone','internet','semparar'];
    foreach ($defaults as $d) {
        $insert->execute([':n' => $d]);
    }

    echo "Migrations applied successfully.\n";
    exit(0);
} catch (PDOException $e) {
    echo "PDOException: " . $e->getMessage() . "\n";
    exit(2);
}

?>