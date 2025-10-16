<?php
// test_processa_pdf.php - script de teste para executar o processamento usando um arquivo já em public/uploads
// WARNING: For local testing only. Bypasses CSRF and file upload checks.

require_once __DIR__ . '/../src/includes/config.php';
require_once __DIR__ . '/../src/includes/helpers.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/includes/Database.php';

// Escolha de arquivo já existente em uploads
$filename = 'upload_68da70767a1c83.40061922.pdf';
$uploadPath = __DIR__ . '/uploads/' . $filename;

if (!file_exists($uploadPath)) {
    echo "Arquivo de teste não encontrado: $uploadPath\n";
    exit(1);
}

// Simula os passos principais do processa_pdf.php (sem validação CSRF e upload)
$nomeUnico = $filename; // já único
$caminhoDestino = $uploadPath;

// Chamar a API de ML
$apiUrl = ML_API_URL . '/classify_pdf';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
$cfile = new CURLFile($caminhoDestino, 'application/pdf', $nomeUnico);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => $cfile]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "ML API HTTP code: $httpCode\n";
if ($response === false) {
    echo "Erro cURL: $curlError\n";
    exit(2);
}

echo "ML response:\n" . $response . "\n";

$result = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Erro ao decodar JSON da ML: " . json_last_error_msg() . "\n";
    exit(3);
}

$category = $result['category'] ?? 'desconhecido';
$dados = $result['details'] ?? [];

if (empty($dados['valor']) || empty($dados['data_vencimento'])) {
    echo "Dados essenciais não extraídos pela ML.\n";
    print_r($dados);
    exit(4);
}

// Persistir no banco usando Database singleton
$pdo = Database::getInstance()->getConnection();
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT id FROM categorias WHERE nome = :categoria");
    $stmt->execute([':categoria' => $category]);
    $categoria_id = $stmt->fetchColumn();

    if (!$categoria_id) {
        $stmtIns = $pdo->prepare("INSERT INTO categorias (nome) VALUES (:nome)");
        $stmtIns->execute([':nome' => $category]);
        $categoria_id = $pdo->lastInsertId();
    }

    $unidade_id = 1;
    $sqlFatura = "INSERT INTO faturas (unidade_id, categoria_id, data_emissao, data_vencimento, valor_total, arquivo_pdf, observacoes) VALUES (:unidade_id, :categoria_id, :data_emissao, :data_vencimento, :valor_total, :arquivo_pdf, :observacoes)";
    $stmtFatura = $pdo->prepare($sqlFatura);

    $paramsFatura = [
        ':unidade_id' => $unidade_id,
        ':categoria_id' => $categoria_id,
        ':data_emissao' => $dados['data_emissao'] ?? null,
        ':data_vencimento' => $dados['data_vencimento'] ?? null,
        ':valor_total' => (float)($dados['valor'] ?? 0.0),
        ':arquivo_pdf' => $nomeUnico,
        ':observacoes' => "Teste CLI: " . basename($filename)
    ];

    $stmtFatura->execute($paramsFatura);
    $fatura_id = $pdo->lastInsertId();

    // Lógica de inserção de detalhes, espelhando processa_pdf.php
    // Sanitiza o nome da categoria para uso seguro em uma consulta que não suporta placeholders (SHOW TABLES).
    $safe_category = preg_replace('/[^a-zA-Z0-9_]/', '', $category);
    $tabela_detalhes_especifica = $safe_category . '_detalhes';
    $tabela_existe = false;

    // SHOW TABLES LIKE não suporta placeholders. A string é construída diretamente após sanitização.
    $stmtCheckTable = $pdo->query("SHOW TABLES LIKE '{$tabela_detalhes_especifica}'");
    if ($stmtCheckTable) {
        $tabela_existe = $stmtCheckTable->rowCount() > 0;
    }

    unset($dados['data_vencimento'], $dados['valor'], $dados['data_emissao']);

    if ($tabela_existe) {
        // Lógica para inserir na tabela específica (ex: agua_detalhes)
        $colunas = array_keys($dados);
        if (!empty($colunas)) {
            $placeholders = array_map(fn($c) => ":$c", $colunas);
            $sqlDetalhes = "INSERT INTO {$tabela_detalhes_especifica} (fatura_id, " . implode(', ', $colunas) . ") VALUES (:fatura_id, " . implode(', ', $placeholders) . ")";
            
            $paramsDetalhes = $dados;
            $paramsDetalhes['fatura_id'] = $fatura_id;

            $stmtDetalhes = $pdo->prepare($sqlDetalhes);
            $stmtDetalhes->execute($paramsDetalhes);
        }
    } else {
        // Lógica para inserir na tabela genérica fatura_detalhes
        $sqlDetalhes = "INSERT INTO fatura_detalhes (fatura_id, chave, valor) VALUES (:fatura_id, :chave, :valor)";
        $stmtDetalhes = $pdo->prepare($sqlDetalhes);
        foreach ($dados as $chave => $valor) {
            if ($valor !== null) {
                $stmtDetalhes->execute([':fatura_id' => $fatura_id, ':chave' => $chave, ':valor' => $valor]);
            }
        }
    }

    $pdo->commit();
    echo "Fatura inserida com ID: $fatura_id\n";
    exit(0);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Erro ao inserir fatura: " . $e->getMessage() . "\n";
    exit(5);
}

?>