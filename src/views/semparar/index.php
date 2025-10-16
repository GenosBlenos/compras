<?php
ob_start();

// Exibir mensagens de sucesso ou erro da sessão
if (isset($_SESSION['success'])) {
    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">' . htmlspecialchars($_SESSION['success']) . '</div>';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">' . htmlspecialchars($_SESSION['error']) . '</div>';
    unset($_SESSION['error']);
}
?>
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-blue-500 border-b-2 border-gray-300">
            <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-300 pb-2 mb-2">Total do Mês (Pendente)</h3>
            <p class="text-2xl font-bold text-blue-600">R$ <?php echo number_format($totalPendente ?? 0, 2, ',', '.'); ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-green-500 border-b-2 border-gray-300">
            <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-300 pb-2 mb-2">Média de Passagens</h3>
            <p class="text-2xl font-bold text-green-600"><?php echo number_format($mediaTransacoes ?? 0, 0); ?> passagens</p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-purple-500 border-b-2 border-gray-300">
            <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-300 pb-2 mb-2">Total Anual</h3>
            <p class="text-2xl font-bold text-purple-600">R$ <?php echo number_format($totalAnual ?? 0, 2, ',', '.'); ?></p>
        </div>
    </div>

    <form action="index.php?page=semparar" method="POST" class="space-y-4 bg-white p-6 rounded-lg shadow-lg">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(gerarCSRFToken()); ?>">
        <input type="hidden" name="action" value="store">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label for="mes" class="block text-sm font-medium text-gray-700">Mês</label>
                <input type="month" name="mes" id="mes" required 
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>

            <div>
                <label for="unidade" class="block text-sm font-medium text-gray-700">Unidade</label>
                <input type="text" name="unidade" id="unidade" required 
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>

            <div>
                <label for="placa_veiculo" class="block text-sm font-medium text-gray-700">Placa do Veículo</label>
                <input type="text" name="placa_veiculo" id="placa_veiculo" required 
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>

            <div>
                <label for="valor_fatura" class="block text-sm font-medium text-gray-700">Valor da Fatura</label>
                <input type="number" step="0.01" name="valor_fatura" id="valor_fatura" required 
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>

            <div>
                <label for="multa" class="block text-sm font-medium text-gray-700">Multa</label>
                <input type="number" step="0.01" name="multa" id="multa" 
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>

            <div>
                <label for="data_vencimento" class="block text-sm font-medium text-gray-700">Data de Vencimento</label>
                <input type="date" name="data_vencimento" id="data_vencimento" required 
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>

            <div>
                <label for="secretaria" class="block text-sm font-medium text-gray-700">Secretaria</label>
                <input type="text" name="secretaria" id="secretaria" 
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>

            <div>
                <label for="num_tag" class="block text-sm font-medium text-gray-700">Número da TAG</label>
                <input type="text" name="num_tag" id="num_tag" 
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>

            <div>
                <label for="passagens" class="block text-sm font-medium text-gray-700">Número de Passagens</label>
                <input type="number" name="passagens" id="passagens" 
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" required 
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                    <option value="pendente">Pendente</option>
                    <option value="pago">Pago</option>
                </select>
            </div>

            <div>
                <label for="observacao" class="block text-sm font-medium text-gray-700">Observação</label>
                <textarea name="observacao" id="observacao" rows="1" 
                    class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50"></textarea>
            </div>
        </div>

        <div class="flex justify-end space-x-2">
            <button type="submit" class="bg-[#072a3a] hover:bg-[#051e2b] text-white font-bold py-2 px-4 rounded-md">
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mês</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unidade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Placa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Passagens</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($registros as $registro): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registro['mes']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registro['unidade']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registro['placa_veiculo']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">R$ <?php echo number_format($registro['valor_fatura'], 2, ',', '.'); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo date('d/m/Y', strtotime($registro['data_vencimento'])); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $registro['status'] === 'pago' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                <?php echo ucfirst($registro['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registro['passagens']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="editarRegistro(<?php echo $registro['id_semparar']; ?>)" class="text-indigo-600 hover:text-indigo-900 mr-2">Editar</button>
                            <button onclick="excluirRegistro(<?php echo $registro['id_semparar']; ?>)" class="text-red-600 hover:text-red-900">Excluir</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function editarRegistro(id) {
    // Implementar lógica de edição
    if (confirm('Deseja editar este registro?')) {
        // TODO: Carregar dados do registro no formulário
    }
}

function excluirRegistro(id) {
    if (confirm('Tem certeza que deseja excluir este registro?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'index.php?page=semparar';
        form.innerHTML = `
            <input type="hidden" name="action" value="destroy">
            <input type="hidden" name="id" value="${id}">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(gerarCSRFToken()); ?>">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Calcular total automaticamente
document.addEventListener('DOMContentLoaded', function() {
    const valorFatura = document.getElementById('valor_fatura');
    const multa = document.getElementById('multa');
    const total = document.getElementById('total');

    function calcularTotal() {
        const valor = parseFloat(valorFatura.value) || 0;
        const multaValor = parseFloat(multa.value) || 0;
        total.value = (valor + multaValor).toFixed(2);
    }

    valorFatura.addEventListener('input', calcularTotal);
    multa.addEventListener('input', calcularTotal);
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../../includes/template.php';
?>