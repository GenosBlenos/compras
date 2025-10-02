<?php
// recomendacoes.php (versão final standalone)

require_once __DIR__ . '/../src/includes/auth.php';
require_once __DIR__ . '/../src/includes/header.php';
require_once __DIR__ . '/../app/conexao.php';
require_once __DIR__ . '/../contas.php';

$pageTitle = 'Recomendações';

if (!isset($_SESSION['logado']) || !$_SESSION['logado']) {
    header('Location: /compras/login.php');
    exit;
}

$_GET['module'] = 'recomendacoes'; // Define o módulo para o header/nav

// 1. OBTER VALORES DOS FILTROS DA URL (LÓGICA DO ANTIGO CONTROLLER)
$filtroModulo = $_GET['modulo'] ?? 'energia'; 
$filtroInstalacao = $_GET['instalacao'] ?? 'todas';
$filtroMesAno = $_GET['mes_ano'] ?? 'todos';

// 2. OBTER DADOS PARA POPULAR OS FILTROS
$modulosDisponiveis = ['agua', 'energia', 'internet', 'semparar', 'telefone'];
$instalacoesDisponiveis = [];
if ($filtroModulo !== 'todos') {
    try {
        $stmt = $pdo->query("SELECT DISTINCT instalacao FROM {$filtroModulo} WHERE instalacao IS NOT NULL AND instalacao != '' ORDER BY instalacao");
        $instalacoesDisponiveis = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        // Ignora o erro se a tabela não existir.
    }
}
$mesesAnosDisponiveis = [];
if ($filtroModulo !== 'todos') {
    try {
        $stmt = $pdo->query("SELECT DISTINCT DATE_FORMAT(data_vencimento, '%Y-%m') as mes_ano FROM {$filtroModulo} WHERE data_vencimento IS NOT NULL ORDER BY mes_ano DESC");
        $mesesAnosDisponiveis = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        // Ignora o erro se a tabela não existir.
    }
}

// 3. OBTER E PROCESSAR OS DADOS PRINCIPAIS COM BASE NOS FILTROS
$contas = getContasFromDatabase($pdo, $filtroModulo); // Passa a conexão $pdo

// Aplicar filtros de instalação e mês/ano
$contasFiltradas = array_filter($contas, function($conta) use ($filtroInstalacao, $filtroMesAno) {
    $passaInstalacao = ($filtroInstalacao === 'todas' || ($conta['instalacao'] ?? null) == $filtroInstalacao);
    $passaMesAno = ($filtroMesAno === 'todos' || date('Y-m', strtotime($conta['data_vencimento'])) == $filtroMesAno);
    return $passaInstalacao && $passaMesAno;
});

$contasComVariacao = calcularVariacaoMensal($contasFiltradas);

// 4. LÓGICA DE ANÁLISE PARA GERAR RECOMENDAÇÕES
$recomendacoes = [];
$limiteVariacao = 20.0; // 20%

if ($filtroModulo === 'energia') {
    foreach ($contasComVariacao as $conta) {
        $instalacao = $conta['instalacao'] ?? 'Não informada';
        $mesReferencia = !empty($conta['data_vencimento']) ? date('m/Y', strtotime($conta['data_vencimento'])) : 'N/A';
        $variacaoNum = (float) str_replace(['%', '+'], '', $conta['variacao_mes_anterior'] ?? '0');
        if (abs($variacaoNum) > $limiteVariacao) {
            $tipo = $variacaoNum > 0 ? 'Aumento' : 'Redução';
            $recomendacoes[$instalacao][] = ['tipo' => 'Alerta de Variação', 'severidade' => 'alta', 'mensagem' => "<b>{$tipo} acentuado no consumo</b> ({$conta['variacao_mes_anterior']}) no mês de {$mesReferencia}. Valor: R$ " . number_format($conta['valor'], 2, ',', '.') . ". Investigar a causa."];
        }
        $pacote = (float)($conta['pacote_contratado_kwh'] ?? 0);
        $consumo = (float)($conta['consumo'] ?? 0);
        if ($pacote > 0 && $consumo > 0) {
            if ($consumo < ($pacote * 0.7)) {
                $percentualUso = ($consumo / $pacote) * 100;
                $desperdicio = $pacote - $consumo;
                $recomendacoes[$instalacao][] = ['tipo' => 'Otimização de Contrato', 'severidade' => 'media', 'mensagem' => "<b>Pacote superdimensionado</b> no mês {$mesReferencia}. Apenas " . number_format($percentualUso, 1) . "% do pacote de {$pacote} kWh foi utilizado. Potencial de economia de {$desperdicio} kWh. Avaliar redução do contrato."];
            } elseif ($consumo > $pacote) {
                $excesso = $consumo - $pacote;
                $recomendacoes[$instalacao][] = ['tipo' => 'Alerta de Excesso', 'severidade' => 'alta', 'mensagem' => "<b>Consumo excedeu o pacote</b> em {$excesso} kWh no mês {$mesReferencia}. Isso pode gerar multas ou tarifas mais altas."];
            }
        }
    }
} else if ($filtroModulo !== 'todos' && $filtroModulo !== '') {
    $recomendacoes['Geral'][] = ['tipo' => 'Em Desenvolvimento', 'severidade' => 'info', 'mensagem' => "A análise de recomendações para o módulo '{$filtroModulo}' ainda está em desenvolvimento."];
}

// Inicia o buffer de saída para o conteúdo principal
ob_start();
?>

<div class="space-y-8">
    <div class="bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Filtrar Análises</h2>
        <form action="recomendacoes.php" method="get" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="modulo" class="block text-sm font-medium text-gray-700">Módulo</label>
                <select name="modulo" id="modulo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <?php foreach ($modulosDisponiveis as $modulo): ?>
                        <option value="<?= $modulo ?>" <?= ($filtroModulo === $modulo) ? 'selected' : '' ?>><?= ucfirst($modulo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="instalacao" class="block text-sm font-medium text-gray-700">Instalação</label>
                <select name="instalacao" id="instalacao" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" <?= empty($instalacoesDisponiveis) ? 'disabled' : '' ?>>
                    <option value="todas">Todas as Instalações</option>
                    <?php foreach ($instalacoesDisponiveis as $inst): ?>
                        <option value="<?= htmlspecialchars($inst) ?>" <?= ($filtroInstalacao === $inst) ? 'selected' : '' ?>><?= htmlspecialchars($inst) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="mes_ano" class="block text-sm font-medium text-gray-700">Mês/Ano</label>
                <select name="mes_ano" id="mes_ano" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" <?= empty($mesesAnosDisponiveis) ? 'disabled' : '' ?>>
                    <option value="todos">Todos os Períodos</option>
                    <?php foreach ($mesesAnosDisponiveis as $mesAno): ?>
                        <option value="<?= $mesAno ?>" <?= ($filtroMesAno === $mesAno) ? 'selected' : '' ?>><?= date('m/Y', strtotime($mesAno . '-01')) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-[#4a90e2] hover:bg-[#2563eb] text-white font-bold py-2 px-4 rounded-md shadow-sm w-full">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <?php if (empty($recomendacoes)): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
            <p class="font-bold">Tudo Certo!</p>
            <p>Nenhum ponto de atenção crítico foi identificado com base nos filtros selecionados.</p>
        </div>
    <?php else: ?>
        <?php foreach ($recomendacoes as $instalacao => $listaRecomendacoes): ?>
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">
                    <img src="./assets/pin.png" alt="Local" class="w-5 h-5 inline-block mr-2">
                    Instalação: <?= htmlspecialchars($instalacao) ?>
                </h3>
                <ul class="space-y-3">
                    <?php foreach ($listaRecomendacoes as $rec): ?>
                        <?php
                            $cores = ['alta' => 'border-red-500 bg-red-50', 'media' => 'border-yellow-500 bg-yellow-50', 'info' => 'border-blue-500 bg-blue-50'];
                            $cor = $cores[$rec['severidade']] ?? 'border-gray-300';
                        ?>
                        <li class="p-4 rounded-lg border-l-4 <?= $cor ?>">
                            <p class="font-bold text-gray-700"><?= htmlspecialchars($rec['tipo']) ?></p>
                            <p class="text-gray-600"><?= $rec['mensagem'] ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
// Finaliza o buffer e carrega o template principal
$content = ob_get_clean();
require_once __DIR__ . '/../src/includes/template.php';
?>
