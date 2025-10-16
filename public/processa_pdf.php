<?php
// Inicia a sessão se ainda não estiver iniciada. Crucial para auth.php e CSRF.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../src/includes/config.php';
require_once __DIR__ . '/../src/includes/auth.php';
require_once __DIR__ . '/../src/includes/helpers.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/includes/Database.php';

// --- Manipuladores de Erro Globais ---
// Estes manipuladores garantem que, mesmo em caso de erro fatal ou exceção não capturada,
// o script tente enviar uma resposta JSON válida para o frontend.

// Manipulador para exceções não capturadas
set_exception_handler(function ($exception) {
    gerarLog('Fatal Exception', $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine());
    if (!headers_sent()) { // Verifica se os cabeçalhos já foram enviados
        sendJsonResponse(false, 'Erro crítico inesperado no servidor: ' . htmlspecialchars($exception->getMessage()), 500);
    } else {
        error_log('Fatal exception occurred, but headers already sent. Could not send JSON response. Message: ' . $exception->getMessage());
        exit();
    }
});

// Manipulador para erros fatais (E_ERROR, E_PARSE, etc.)
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING])) {
        gerarLog('Fatal Error (Shutdown)', $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']);
        if (!headers_sent()) { // Verifica se os cabeçalhos já foram enviados
            sendJsonResponse(false, 'Erro fatal inesperado no servidor. Por favor, verifique os logs.', 500);
        } else {
            error_log('Fatal error occurred, but headers already sent. Could not send JSON response. Message: ' . $error['message']);
            exit();
        }
    }
});

function sendJsonResponse($success, $message, $httpCode = 200) {
    header('Content-Type: application/json');
    http_response_code($httpCode);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit();
}

// O arquivo processa_pdf.php é um endpoint AJAX e não deve gerar HTML.
// A variável $pageTitle não é mais necessária aqui.

// 1. Validação CSRF e de Upload
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['pdfFile']) || !isset($_POST['csrf_token']) || !validarCSRFToken($_POST['csrf_token'])) {
    gerarLog('Falha CSRF ou upload', 'Tentativa de upload sem token CSRF válido ou sem arquivo.');
    sendJsonResponse(false, 'Erro: Requisição inválida.', 400);
}

$validacaoUpload = validarUploadArquivo($_FILES['pdfFile']);
if ($validacaoUpload !== true) {
    gerarLog('Falha upload', $validacaoUpload);
    sendJsonResponse(false, htmlspecialchars($validacaoUpload), 400);
}

// 2. Salvar arquivo
$pdfFileName = sanitizeInput($_FILES['pdfFile']['name']);
$nomeUnico = gerarNomeUnicoArquivo($pdfFileName);
$caminhoDestino = __DIR__ . '/uploads/' . $nomeUnico;
if (!move_uploaded_file($_FILES['pdfFile']['tmp_name'], $caminhoDestino)) {
    gerarLog('Falha upload', 'Erro ao mover o arquivo para uploads/');
    sendJsonResponse(false, 'Erro ao salvar o arquivo enviado.', 500);
}

// 3. Chamar a API de Machine Learning
$apiUrl = ML_API_URL . '/classify_pdf';
gerarLog('Envio para classificação', 'Arquivo: ' . $pdfFileName);
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

// 4. Tratar a resposta da API
if ($response === false) {
    gerarLog('Erro cURL', $curlError);
    sendJsonResponse(false, 'Erro de comunicação com o serviço de IA: ' . htmlspecialchars($curlError), 500);
}

if ($httpCode != 200) {
    gerarLog('Erro API', 'HTTP: ' . $httpCode . ' | Resposta: ' . $response);
    sendJsonResponse(false, 'O serviço de IA retornou um erro (HTTP ' . $httpCode . ').', 500);
}

$result = json_decode($response, true);
$category = $result['category'] ?? 'desconhecido';
$dados = $result['details'] ?? [];

// 5. Salvar os dados no banco de dados (lógica refatorada)
try {
    $pdo = Database::getInstance()->getConnection();
    $pdo->beginTransaction();

    // 1. Obter o ID da categoria
    $stmt = $pdo->prepare("SELECT id FROM categorias WHERE nome = :categoria");
    $stmt->execute([':categoria' => $category]);
    $categoria_id = $stmt->fetchColumn();

    if (!$categoria_id) {
        // Tentativa de criar a categoria automaticamente (se a tabela existir)
        try {
            $stmtIns = $pdo->prepare("INSERT INTO categorias (nome) VALUES (:nome)");
            $stmtIns->execute([':nome' => $category]);
            $categoria_id = $pdo->lastInsertId();
        } catch (Exception $e) {
            // Lança exceção clara para logs e retorno ao usuário
            throw new Exception("Categoria '" . $category . "' não encontrada e não foi possível criar automaticamente: " . $e->getMessage());
        }
    }

    // Validar se os dados essenciais foram extraídos APÓS a classificação
    if (empty($dados['valor']) || empty($dados['data_vencimento'])) {
        $missing_data = [];
        if (empty($dados['valor'])) $missing_data[] = 'valor';
        if (empty($dados['data_vencimento'])) $missing_data[] = 'data de vencimento';
        
        $errorMessage = 'A API classificou o PDF como "' . htmlspecialchars($category) . '", mas não conseguiu extrair os seguintes dados obrigatórios: ' . implode(', ', $missing_data) . '. Por favor, verifique o conteúdo do PDF ou cadastre manualmente.';
        gerarLog('Falha Extração API', $errorMessage . ' Arquivo: ' . $pdfFileName);
        sendJsonResponse(false, $errorMessage, 422); // 422 Unprocessable Entity
    }

    // TODO: Implementar seleção de unidade_id no formulário. Por enquanto, usando valor fixo.
    $unidade_id = 1; 

    // 2. Inserir na tabela `faturas`
    $sqlFatura = "INSERT INTO faturas (unidade_id, categoria_id, data_emissao, data_vencimento, valor_total, arquivo_pdf, observacoes) VALUES (:unidade_id, :categoria_id, :data_emissao, :data_vencimento, :valor_total, :arquivo_pdf, :observacoes)";
    $stmtFatura = $pdo->prepare($sqlFatura);

    $paramsFatura = [
        ':unidade_id' => $unidade_id,
        ':categoria_id' => $categoria_id,
        ':data_emissao' => $dados['data_emissao'] ?? null, // Já seguro
        ':data_vencimento' => $dados['data_vencimento'] ?? null, // Adicionada verificação
        ':valor_total' => (float)($dados['valor'] ?? 0.0),
        ':arquivo_pdf' => $nomeUnico,
        ':observacoes' => "Cadastrado via PDF: " . basename($pdfFileName)
    ];

    if (!$stmtFatura->execute($paramsFatura)) {
        throw new Exception("Erro ao inserir na tabela de faturas.");
    }

    $fatura_id = $pdo->lastInsertId();

    // 3. Inserir detalhes na tabela específica da categoria (se existir) ou na genérica
    // Sanitiza o nome da categoria para uso seguro em uma consulta que não suporta placeholders (SHOW TABLES).
    $safe_category = preg_replace('/[^a-zA-Z0-9_]/', '', $category);
    $tabela_detalhes_especifica = $safe_category . '_detalhes';
    $tabela_existe = false;

    // SHOW TABLES LIKE não suporta placeholders. A string é construída diretamente após sanitização.
    $stmtCheckTable = $pdo->query("SHOW TABLES LIKE '{$tabela_detalhes_especifica}'");
    if ($stmtCheckTable) {
        $tabela_existe = $stmtCheckTable->rowCount() > 0;
    }

    // Remover dados já inseridos na tabela principal para não duplicar
    unset($dados['data_vencimento'], $dados['valor'], $dados['data_emissao']);

    if ($tabela_existe) {
        $colunas = array_keys($dados);
        // Apenas tenta inserir se houver colunas/dados restantes
        if (!empty($colunas)) {
            // Lógica para inserir na tabela específica (ex: agua_detalhes)
            $placeholders = array_map(fn($c) => ":$c", $colunas);
            $sqlDetalhes = "INSERT INTO {$tabela_detalhes_especifica} (fatura_id, " . implode(', ', $colunas) . ") VALUES (:fatura_id, " . implode(', ', $placeholders) . ")";
            
            $paramsDetalhes = $dados;
            $paramsDetalhes['fatura_id'] = $fatura_id;

            $stmtDetalhes = $pdo->prepare($sqlDetalhes);
            if (!$stmtDetalhes->execute($paramsDetalhes)) {
                throw new Exception("Erro ao inserir na tabela de detalhes '{$tabela_detalhes_especifica}'.");
            }
        }
    } else {
        // Lógica para inserir na tabela genérica fatura_detalhes
        $sqlDetalhes = "INSERT INTO fatura_detalhes (fatura_id, chave, valor) VALUES (:fatura_id, :chave, :valor)";
        $stmtDetalhes = $pdo->prepare($sqlDetalhes);
        foreach ($dados as $chave => $valor) {
            if ($valor !== null) {
                $paramsDetalhes = [':fatura_id' => $fatura_id, ':chave' => $chave, ':valor' => $valor];
                if (!$stmtDetalhes->execute($paramsDetalhes)) {
                    throw new Exception("Erro ao inserir detalhes da fatura para a chave '{$chave}'.");
                }
            }
        }
    }
    
    $pdo->commit();
    gerarLog('Sucesso', "Fatura de {$category} inserida com ID: " . $fatura_id);
    sendJsonResponse(true, 'Fatura de ' . htmlspecialchars($category) . ' processada e salva com sucesso!');

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $error_message = $e->getMessage();
    gerarLog('Erro Exceção DB', 'Exceção: ' . $error_message);
    sendJsonResponse(false, 'Ocorreu um erro inesperado: ' . htmlspecialchars($error_message), 500);
}