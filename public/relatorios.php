<?php
require_once __DIR__ . '/../src/includes/auth.php';
require_once __DIR__ . '/../src/includes/header.php';
require_once __DIR__ . '/../src/controllers/RelatorioController.php';
require_once __DIR__ . '/../contas.php';

$pageTitle = 'Relatórios';
$controller = new RelatorioController();

// --- LÓGICA DE EXIBIÇÃO DE RELATÓRIOS AGRUPADOS ---
// Pega os filtros da URL para a visualização na página
$selectedModule = $_GET['module_filter'] ?? 'todos'; // Usa um nome diferente para evitar conflito com o parâmetro 'module' do CSV
$selectedStatus = $_GET['status_filter'] ?? 'todas';

// Pega os filtros da URL para o formulário de CSV (mantém os nomes originais)
$csvModule = $_GET['module'] ?? 'todos';
$csvStatus = $_GET['status'] ?? 'todas';

// Busca todas as contas com base no módulo selecionado para a visualização
$allContas = getContasFromDatabase($pdo, $selectedModule);
$contasComVariacao = calcularVariacaoMensal($allContas);

// Filtra as contas pelo status selecionado para a visualização
$filteredContas = array_filter($contasComVariacao, function($conta) use ($selectedStatus) {
    if ($selectedStatus === 'todas') {
        return true;
    }
    $status = $conta['Conta_status'] ?? 'pendente'; // Padrão para 'pendente' se não definido
    return ($selectedStatus === 'pendentes' && $status === 'pendente') ||
           ($selectedStatus === 'pagas' && $status === 'pago');
});

// Agrupa as contas filtradas por 'instalacao'
$groupedContas = [];
foreach ($filteredContas as $conta) {
    $instalacao = $conta['instalacao'] ?? 'Não informada';
    $groupedContas[$instalacao][] = $conta;
}

ob_start();
?>

<div class="space-y-6">
    <div class="bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-xl font-semibold text-gray-800 mb-2">Exportar Contas para CSV</h2>
        <p class="text-gray-600 mb-6">Selecione o módulo e o status das contas para gerar um arquivo CSV.</p>
        
        <form action="gerar_csv.php" method="get" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="module" class="block text-sm font-medium text-gray-700">Módulo</label>
                <select name="module" id="module" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="todos" <?= ($csvModule === 'todos') ? 'selected' : '' ?>>Todos os Módulos</option>
                    <option value="agua" <?= ($csvModule === 'agua') ? 'selected' : '' ?>>Água</option>
                    <option value="energia" <?= ($csvModule === 'energia') ? 'selected' : '' ?>>Energia</option>
                    <option value="telefone" <?= ($csvModule === 'telefone') ? 'selected' : '' ?>>Telefone</option>
                    <option value="semparar" <?= ($csvModule === 'semparar') ? 'selected' : '' ?>>Sem Parar</option>
                    <option value="internet" <?= ($csvModule === 'internet') ? 'selected' : '' ?>>Internet</option>
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status das Contas</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="todas" <?= ($csvStatus === 'todas') ? 'selected' : '' ?>>Todas</option>
                    <option value="pendentes" <?= ($csvStatus === 'pendentes') ? 'selected' : '' ?>>Pendentes</option>
                    <option value="pagas" <?= ($csvStatus === 'pagas') ? 'selected' : '' ?>>Pagas</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="bg-[#4a90e2] hover:bg-[#2563eb] text-white font-bold py-2 px-4 rounded-md shadow-sm">
                    Gerar CSV
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../src/includes/template.php';
?>
