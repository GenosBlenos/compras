<?php
require_once __DIR__ . '/../src/includes/auth.php';
$_GET['module'] = 'semparar'; // Define o módulo atual

require_once __DIR__ . '/../src/includes/header.php';
require_once __DIR__ . '/../src/models/SemParar.php';
$semPararModel = new SemParar();
$pageTitle = 'Sem Parar';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['salvar'])) {
        $data = [
            'combustivel' => $_POST['combustivel'] ?? null,
            'marca' => $_POST['marca'] ?? null,
            'modelo' => $_POST['modelo'] ?? null,
            'departamento' => $_POST['departamento'] ?? null,
            'ficha' => $_POST['ficha'] ?? null,
            'secretaria' => $_POST['secretaria'] ?? null,
            'tag' => $_POST['tag'] ?? null,
            'mensalidade' => $_POST['mensalidade'] ?? 0,
            'passagens' => $_POST['passagens'] ?? 0,
            'estacionamento' => $_POST['estacionamento'] ?? 0,
            'estabelecimentos' => $_POST['estabelecimentos'] ?? 0,
            'credito' => $_POST['credito'] ?? 0,
            'isento' => $_POST['isento'] ?? 0,
            'mes' => $_POST['mes'] ?? null,
            'total' => $_POST['total'] ?? 0,
            'Conta_status' => $_POST['Conta_status'],
            'observacoes' => $_POST['observacoes'] ?? null,
            'criado_por' => $_SESSION['usuario_id']
        ];

        if ($semPararModel->create($data)) {
            $success = "Registro criado com sucesso!";
        } else {
            $error = "Erro ao criar registro.";
        }
    }

    if (isset($_POST['editar']) && isset($_POST['id'])) {
        $data = [
            'combustivel' => $_POST['combustivel'] ?? null,
            'marca' => $_POST['marca'] ?? null,
            'modelo' => $_POST['modelo'] ?? null,
            'departamento' => $_POST['departamento'] ?? null,
            'ficha' => $_POST['ficha'] ?? null,
            'secretaria' => $_POST['secretaria'] ?? null,
            'tag' => $_POST['tag'] ?? null,
            'mensalidade' => $_POST['mensalidade'] ?? 0,
            'passagens' => $_POST['passagens'] ?? 0,
            'estacionamento' => $_POST['estacionamento'] ?? 0,
            'estabelecimentos' => $_POST['estabelecimentos'] ?? 0,
            'credito' => $_POST['credito'] ?? 0,
            'isento' => $_POST['isento'] ?? 0,
            'mes' => $_POST['mes'] ?? null,
            'total' => $_POST['total'] ?? 0,
            'Conta_status' => $_POST['Conta_status'],
            'observacoes' => $_POST['observacoes'] ?? null
        ];

        if ($semPararModel->update($_POST['id'], $data)) {
            $success = "Registro atualizado com sucesso!";
        } else {
            $error = "Erro ao atualizar registro.";
        }
    }

    if (isset($_POST['excluir']) && isset($_POST['id'])) {
        if ($semPararModel->delete($_POST['id'])) {
            $success = "Registro excluído com sucesso!";
        } else {
            $error = "Erro ao excluir registro.";
        }
    }
}

// Buscar dados para a tabela
$registros = $semPararModel->all();
$totalDoMes = $semPararModel->getTotalDoMes();
$mediaPorFatura = $semPararModel->getMediaPorFatura();
$totalAnual = $semPararModel->getTotalAnual();

ob_start();
?>
<div class="space-y-6">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-blue-500 border-b-2 border-gray-300">
            <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-300 pb-2 mb-2">Total do Mês (Pago)</h3>
            <p class="text-2xl font-bold text-blue-600">R$ <?php echo number_format($totalDoMes ?? 0, 2, ',', '.'); ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-green-500 border-b-2 border-gray-300">
            <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-300 pb-2 mb-2">Média por Fatura</h3>
            <p class="text-2xl font-bold text-green-600">R$ <?php echo number_format($mediaPorFatura ?? 0, 2, ',', '.'); ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-purple-500 border-b-2 border-gray-300">
            <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-300 pb-2 mb-2">Total Anual (Pago)</h3>
            <div id="consumoChart" class="h-20"></div>
            <p class="text-2xl font-bold text-purple-600 text-center mt-2">R$ <?php echo number_format($totalAnual ?? 0, 2, ',', '.'); ?></p>
        </div>
    </div>

    <form action="" method="POST" class="space-y-4 bg-white p-6 rounded-lg shadow-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label for="data_org" class="block text-sm font-medium text-gray-700">Data da Despesa</label>
                <input type="date" name="data_org" id="data_org" required
                    style="border: 2px solid gray;" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>

            <div>
                <label for="combustivel" class="block text-sm font-medium text-gray-700">Combustível</label>
                <input type="text" name="combustivel" id="combustivel" 
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>
            <div>
                <label for="marca" class="block text-sm font-medium text-gray-700">Marca</label>
                <input type="text" name="marca" id="marca" 
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>
            <div>
                <label for="modelo" class="block text-sm font-medium text-gray-700">Modelo</label>
                <input type="text" name="modelo" id="modelo" 
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>
            <div>
                <label for="departamento" class="block text-sm font-medium text-gray-700">Departamento</label>
                <input type="text" name="departamento" id="departamento" 
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>
            <div>
                <label for="ficha" class="block text-sm font-medium text-gray-700">Ficha</label>
                <input type="number" name="ficha" id="ficha" 
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>
            <div>
                <label for="secretaria" class="block text-sm font-medium text-gray-700">Secretaria</label>
                <input type="text" name="secretaria" id="secretaria" 
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>
            <div>
                <label for="tag" class="block text-sm font-medium text-gray-700">Tag</label>
                <input type="number" name="tag" id="tag" 
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>
            <div>
                <label for="mensalidade" class="block text-sm font-medium text-gray-700">Mensalidade</label>
                <input type="number" name="mensalidade" id="mensalidade" step="0.01"
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>
            <div>
                <label for="passagens" class="block text-sm font-medium text-gray-700">Passagens</label>
                <input type="number" name="passagens" id="passagens" step="0.01"
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>
            <div>
                <label for="estacionamento" class="block text-sm font-medium text-gray-700">Estacionamento</label>
                <input type="number" name="estacionamento" id="estacionamento" step="0.01"
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>
            <div>
                <label for="estabelecimentos" class="block text-sm font-medium text-gray-700">Estabelecimentos</label>
                <input type="number" name="estabelecimentos" id="estabelecimentos" step="0.01"
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>
            <div>
                <label for="credito" class="block text-sm font-medium text-gray-700">Crédito</label>
                <input type="number" name="credito" id="credito" step="0.01"
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>
            <div>
                <label for="isento" class="block text-sm font-medium text-gray-700">Isento</label>
                <input type="checkbox" name="isento" id="isento" value="1" class="mt-1 h-5 w-5 rounded border-gray-400 border-2 text-indigo-600 focus:ring-indigo-500">
            </div>
            <div>
                <label for="mes" class="block text-sm font-medium text-gray-700">Mês</label>
                <input type="number" name="mes" id="mes" 
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>
            <div>
                <label for="total" class="block text-sm font-medium text-gray-700">Total</label>
                <input type="number" name="total" id="total" step="0.01"
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>
            <div>
                <label for="Conta_status" class="block text-sm font-medium text-gray-700">Status da Conta</label>
                <select name="Conta_status" id="Conta_status" required class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                    <option value="">Selecione...</option>
                    <option value="pendente">Pendente</option>
                    <option value="pago">Pago</option>
                </select>
            </div>
            <div>
                <label for="observacoes" class="block text-sm font-medium text-gray-700">Observações</label>
                <textarea name="observacoes" id="observacoes" rows="1" class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50"></textarea>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-2">
            <button type="submit" name="salvar" class="bg-[#072a3a] hover:bg-[#051e2b] text-white font-bold py-2 px-4 rounded-md shadow-sm">
                Salvar
            </button>
        </div>
    </form>

    <div class="bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Registros de Sem Parar</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observações</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($registros as $registro): ?>
                        <tr id="row-<?= htmlspecialchars($registro['id_semparar']) ?>">
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registro['valor'] ?? '-'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registro['data_vencimento'] ?? '-'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if (($registro['status'] ?? $registro['Conta_status'] ?? '') === 'pago'): ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Pago</span>
                                <?php else: ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pendente</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registro['observacoes'] ?? '-'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="editarRegistro(<?= $registro['id_semparar'] ?>)" class="text-indigo-600 hover:text-indigo-900 mr-2">Editar</button>
                                <button onclick="excluirRegistro(<?= $registro['id_semparar'] ?>)" class="text-red-600 hover:text-red-900">Excluir</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Funções para edição e exclusão
    function editarRegistro(id) {
        // Esta função pode ser expandida para carregar dados via AJAX e preencher o formulário
        if (confirm('Deseja editar este registro?')) {
            // A lógica de carregamento dos dados no formulário precisaria ser implementada.
            alert('Funcionalidade de edição a ser implementada.');
        }
    }

    function excluirRegistro(id) {
        if (confirm('Tem certeza que deseja excluir este registro?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'semparar.php';
            form.innerHTML = `
                <input type="hidden" name="id" value="${id}">
                <input type="hidden" name="excluir" value="1">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../src/includes/template.php';
?>