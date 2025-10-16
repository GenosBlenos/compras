<?php
ob_start();
// Mensagens de sucesso/erro
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
            <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-300 pb-2 mb-2">Média de Velocidade</h3>
            <p class="text-2xl font-bold text-green-600"><?php echo htmlspecialchars($mediaVelocidade ?? '0 Mbps'); ?></p>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-purple-500 border-b-2 border-gray-300">
            <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-300 pb-2 mb-2">Total Anual (Pago)</h3>
            <p class="text-2xl font-bold text-purple-600 text-center mt-2">R$ <?php echo number_format($totalAnual ?? 0, 2, ',', '.'); ?></p>
        </div>
    </div>
    <form action="" method="POST" class="space-y-4 bg-white p-6 rounded-lg shadow-lg">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label for="provedor" class="block text-sm font-medium text-gray-700">Provedor</label>
                <input type="text" name="provedor" id="provedor" class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>
            <div>
                <label for="valor" class="block text-sm font-medium text-gray-700">Valor</label>
                <input type="number" name="valor" id="valor" step="0.01" class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>
            <div>
                <label for="data_vencimento" class="block text-sm font-medium text-gray-700">Data Vencimento</label>
                <input type="date" name="data_vencimento" id="data_vencimento" class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
            </div>
            <div>
                <label for="velocidade" class="block text-sm font-medium text-gray-700">Velocidade</label>
                <input type="text" name="velocidade" id="velocidade" class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
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
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Registros de Internet</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provedor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Velocidade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observações</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($registros as $registro): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registro['provedor'] ?? '-'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">R$ <?php echo number_format($registro['valor'] ?? 0, 2, ',', '.'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registro['data_vencimento'] ?? '-'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registro['velocidade'] ?? '-'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registro['observacoes'] ?? '-'); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="editarRegistro(<?= $registro['id_internet'] ?>)" class="text-indigo-600 hover:text-indigo-900 mr-2">Editar</button>
                                <button onclick="excluirRegistro(<?= $registro['id_internet'] ?>)" class="text-red-600 hover:text-red-900">Excluir</button>
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
        alert('Funcionalidade de edição a ser implementada.');
    }
    function excluirRegistro(id) {
        if (confirm('Tem certeza que deseja excluir este registro?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'internet.php';
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
require_once __DIR__ . '/../../includes/template.php';
?>
