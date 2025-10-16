<?php
require_once __DIR__ . '/src/includes/auth.php';
require_once __DIR__ . '/src/includes/header.php';
$pageTitle = 'Contas';

require_once __DIR__ . '/app/conexao.php'; // Garante que $pdo esteja disponível

function getContasFromDatabase($pdo, $moduleFiltro = 'todos') {
    
    $modulos = [
        'agua' => 'SELECT *, "agua" as modulo FROM agua',
        'energia' => 'SELECT *, "energia" as modulo FROM energia',
        'internet' => 'SELECT *, "internet" as modulo FROM internet',
        'semparar' => 'SELECT *, "semparar" as modulo FROM semparar',
        'telefone' => 'SELECT *, "telefone" as modulo FROM telefone',
    ];

    $todasAsContas = [];

    // Se um módulo específico for selecionado, busca apenas dele.
    if ($moduleFiltro !== 'todos' && isset($modulos[$moduleFiltro])) {
        try {
            $stmt = $pdo->query($modulos[$moduleFiltro]);
            $todasAsContas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar contas do módulo '{$moduleFiltro}': " . $e->getMessage());
        }
    } else { // Se "todos" for selecionado, busca de todos os módulos.
        foreach ($modulos as $nomeModulo => $query) {
            try {
                // Colunas comuns para o relatório geral. Adicionamos as novas colunas de energia com COALESCE para preencher com NULL em outros módulos.
                $common_query = 'SELECT "'. $nomeModulo .'" as modulo, observacoes, valor, Conta_status, data_vencimento, local, secretaria, instalacao, consumo, IF(TABLE_NAME = \'energia\', pacote_contratado_kwh, NULL) as pacote_contratado_kwh FROM ' . $nomeModulo;
                $stmt = $pdo->query($common_query);
                $contas = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $todasAsContas = array_merge($todasAsContas, $contas);
            } catch (PDOException $e) {
                // Ignora erros se uma tabela ou coluna não existir em um dos módulos
                error_log("Erro ao buscar contas do módulo '{$nomeModulo}' para relatório geral: " . $e->getMessage());
            }
        }
    }

    return $todasAsContas;
}

/**
 * Calcula a variação percentual do valor de cada conta em relação ao mês anterior
 * para a mesma instalação.
 *
 * @param array $contas Array de contas a serem analisadas.
 * @return array O mesmo array de contas com uma nova chave 'variacao_mes_anterior'.
 */
function calcularVariacaoMensal(array $contas): array
{
    if (empty($contas)) {
        return [];
    }

    // 1. Agrupar contas por um identificador único (ex: 'instalacao')
    $contasPorInstalacao = [];
    foreach ($contas as $conta) {
        // Usamos 'instalacao' como chave. Se não existir, usamos um placeholder.
        $identificador = $conta['instalacao'] ?? 'nao_identificado_' . md5(json_encode($conta));
        $contasPorInstalacao[$identificador][] = $conta;
    }

    $contasComVariacao = [];
    foreach ($contasPorInstalacao as $grupoDeContas) {
        // 2. Ordenar as contas de cada grupo por data de vencimento
        usort($grupoDeContas, function ($a, $b) {
            return strtotime($a['data_vencimento'] ?? 'now') <=> strtotime($b['data_vencimento'] ?? 'now');
        });

        // 3. Calcular a variação em relação ao mês anterior
        $valorAnterior = null;
        foreach ($grupoDeContas as &$conta) { // Usar referência para modificar a conta
            $valorAtual = (float)($conta['valor'] ?? 0);
            if ($valorAnterior !== null && $valorAnterior > 0) {
                $variacao = (($valorAtual - $valorAnterior) / $valorAnterior) * 100;
                $conta['variacao_mes_anterior'] = sprintf('%+.2f%%', $variacao); // Formata com sinal (+ ou -) e 2 casas decimais
            } else {
                $conta['variacao_mes_anterior'] = 'N/A'; // Para a primeira conta do grupo
            }
            $valorAnterior = $valorAtual;
        }
        $contasComVariacao = array_merge($contasComVariacao, $grupoDeContas);
    }

    return $contasComVariacao;
}

function gerarCSVContas($contas, $statusFiltro = 'todas', $moduleFiltro = 'todos') {
    $nomeArquivo = 'contas';
    $contasFiltradas = $contas;
    $nomeArquivo .= ($moduleFiltro !== 'todos') ? '_' . $moduleFiltro : '';

    // Filtrar por Status
    if ($statusFiltro === 'pendentes') {
        $contasFiltradas = array_filter($contasFiltradas, function($conta) {
            return ($conta['Conta_status'] ?? 'pendente') === 'pendente';
        });
        $nomeArquivo .= '_pendentes';
    } elseif ($statusFiltro === 'pagas') {
        $contasFiltradas = array_filter($contasFiltradas, function($conta) {
            return ($conta['Conta_status'] ?? '') === 'pago';
        });
        $nomeArquivo .= '_pagas';
    }

    $nomeArquivo .= '.csv';

    // Adiciona a análise de variação antes de gerar o CSV
    $contasFiltradas = calcularVariacaoMensal($contasFiltradas);

    // Criar o arquivo CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');

    $output = fopen('php://output', 'w+');

    // Definir cabeçalhos e dados
    if (!empty($contasFiltradas)) {
        // Reordena as colunas para que a variação apareça perto do valor
        $primeiraLinha = $contasFiltradas[0];
        if (isset($primeiraLinha['variacao_mes_anterior'])) {
            $posValor = array_search('valor', array_keys($primeiraLinha));
            $primeiraLinha = array_slice($primeiraLinha, 0, $posValor + 1, true) +
                           ['variacao_mes_anterior' => $primeiraLinha['variacao_mes_anterior']] +
                           array_slice($primeiraLinha, $posValor + 1, null, true);
        }
        $headers = array_keys($contasFiltradas[0]);
        fputcsv($output, $headers);

        // Adiciona os dados
        foreach ($contasFiltradas as $conta) {
            // Garante que a ordem das colunas seja a mesma dos cabeçalhos
            $linha = [];
            foreach ($headers as $header) {
                $linha[$header] = $conta[$header] ?? ''; // Adiciona valor ou string vazia se a chave não existir
            }

            // Prevenção contra CSV Injection
            foreach ($linha as &$valor) {
                if (is_string($valor) && !empty($valor) && in_array($valor[0], ['=', '-', '+', '@'])) {
                    $valor = "\t" . $valor;
                }
            }
            unset($valor); // Limpa a referência

            fputcsv($output, $linha);
        }
    } else {
        // Se não houver dados, escreve apenas uma mensagem
        fputcsv($output, ['Nenhum dado encontrado para os filtros selecionados.']);
    }

    fclose($output);
}
?>