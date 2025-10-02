<?php
require_once __DIR__ . '/../src/includes/auth.php';
require_once __DIR__ . '/../src/includes/header.php';
require_once __DIR__ . '/../src/includes/config.php';

$pageTitle = 'Processar PDF';

require_once __DIR__ . '/../src/includes/helpers.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/includes/Database.php';

function redirectWithMessage($message, $location = 'cad_fatura_pdf.php') {
    $_SESSION['msg'] = $message;
    header("Location: {$location}");
    exit();
}

// 1. Validação CSRF e de Upload
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['pdfFile']) || !isset($_POST['csrf_token']) || !validarCSRFToken($_POST['csrf_token'])) {
    gerarLog('Falha CSRF ou upload', 'Tentativa de upload sem token CSRF válido ou sem arquivo.');
    redirectWithMessage('<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert"><p>Erro: Requisição inválida.</p></div>');
}

$validacaoUpload = validarUploadArquivo($_FILES['pdfFile']);
if ($validacaoUpload !== true) {
    gerarLog('Falha upload', $validacaoUpload);
    redirectWithMessage('<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert"><p>' . htmlspecialchars($validacaoUpload) . '</p></div>');
}

// 2. Salvar arquivo
$pdfFileName = sanitizeInput($_FILES['pdfFile']['name']);
$nomeUnico = gerarNomeUnicoArquivo($pdfFileName);
$caminhoDestino = __DIR__ . '/uploads/' . $nomeUnico;
if (!move_uploaded_file($_FILES['pdfFile']['tmp_name'], $caminhoDestino)) {
    gerarLog('Falha upload', 'Erro ao mover o arquivo para uploads/');
    redirectWithMessage('<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert"><p>Erro ao salvar o arquivo enviado.</p></div>');
}

// 3. Chamar a API de Machine Learning
$apiUrl = ML_API_URL . '/classify_pdf';
gerarLog('Envio para classificação', 'Arquivo: ' . $pdfFileName);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
$cfile = new CURLFile($caminhoDestino, 'application/pdf', $pdfFileName);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => $cfile]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// 4. Tratar a resposta da API
if ($response === false) {
    gerarLog('Erro cURL', $curlError);
    redirectWithMessage('<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert"><p>Erro de comunicação com o serviço de IA: ' . htmlspecialchars($curlError) . '</p></div>');
}

if ($httpCode != 200) {
    gerarLog('Erro API', 'HTTP: ' . $httpCode . ' | Resposta: ' . $response);
    redirectWithMessage('<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert"><p>O serviço de IA retornou um erro.</p></div>');
}

$result = json_decode($response, true);
$category = $result['category'] ?? 'desconhecido';
$dados = $result['details'] ?? [];

// 5. Salvar os dados no banco de dados
try {
    $tabela = '';
    switch ($category) {
        case 'agua': $tabela = 'agua'; break;
        case 'energia': $tabela = 'energia'; break;
        case 'telefone': $tabela = 'telefone'; break;
        case 'internet': $tabela = 'internet'; break;
        case 'semparar': $tabela = 'semparar'; break;
        default:
            redirectWithMessage('<div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert"><p>Categoria de fatura ' . htmlspecialchars($category) . ' não reconhecida.</p></div>');
            break;
    }

    // Validar se os dados essenciais foram extraídos pela API
    if (empty($dados['valor']) || empty($dados['data_vencimento'])) {
        $missing_data = [];
        if(empty($dados['valor'])) $missing_data[] = 'valor';
        if(empty($dados['data_vencimento'])) $missing_data[] = 'data de vencimento';
        $errorMessage = 'A API não conseguiu extrair os seguintes dados: ' . implode(', ', $missing_data) . '. Verifique o PDF.';
        gerarLog('Falha Extração API', $errorMessage . ' Arquivo: ' . $pdfFileName);
        redirectWithMessage('<div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert"><p>' . $errorMessage . '</p></div>');
    }

    // Adicionar campos comuns para inserção no banco
    $dados['criado_por'] = $_SESSION['usuario_id'] ?? null;
    $dados['observacoes'] = "Cadastrado via PDF: " . basename($pdfFileName);

    // Remover quaisquer chaves nulas para evitar erros de SQL com colunas que não podem ser nulas
    $dados = array_filter($dados, function($value) { return $value !== null; });

    $colunas = implode(', ', array_keys($dados));
    $placeholders = ':' . implode(', :', array_keys($dados));

    $pdo = Database::getInstance()->getConnection();
    $sql = "INSERT INTO {$tabela} ({$colunas}) VALUES ({$placeholders})";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute($dados)) {
        gerarLog('Sucesso', "Fatura de {$category} inserida com ID: " . $pdo->lastInsertId());
        redirectWithMessage('<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert"><p>Fatura de ' . htmlspecialchars($category) . ' processada e salva com sucesso!</p></div>');
    } else {
        $errorInfo = $stmt->errorInfo();
        gerarLog('Erro DB', "Erro ao inserir no banco de dados: " . $errorInfo[2]);
        redirectWithMessage('<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert"><p>Erro ao salvar os dados no banco de dados.</p></div>');
    }

} catch (Exception $e) {
    gerarLog('Erro Exceção', 'Exceção geral: ' . $e->getMessage());
    redirectWithMessage('<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert"><p>Ocorreu um erro inesperado durante o salvamento dos dados.</p></div>');
}