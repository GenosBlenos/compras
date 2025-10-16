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
                <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-300 pb-2 mb-2">Total Pendente</h3>
                <p class="text-2xl font-bold text-blue-600">R$ <?php echo number_format($totalPendente ?? 0, 2, ',', '.'); ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-green-500 border-b-2 border-gray-300">
                <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-300 pb-2 mb-2">Média de Consumo</h3>
                <p class="text-2xl font-bold text-green-600"><?php echo number_format($mediaConsumo ?? 0, 2, ',', '.'); ?> m³</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-purple-500 border-b-2 border-gray-300">
                <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-300 pb-2 mb-2">Últimos 6 Meses</h3>
                <div id="consumoChart" class="h-20"></div>
            </div>
        </div>

        <form action="index.php?page=agua" method="POST" class="space-y-4 bg-white p-6 rounded-lg shadow-lg">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(gerarCSRFToken()); ?>">
            <input type="hidden" name="action" value="store">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label for="mes" class="block text-sm font-medium text-gray-700">Mês</label>
                    <input type="number" name="mes" id="mes"
                           class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                </div>
                <div>
                    <label for="local" class="block text-sm font-medium text-gray-700">Local</label>
                    <input type="text" name="local" id="local"
                           class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                </div>
                <div>
                    <label for="consumo" class="block text-sm font-medium text-gray-700">Consumo (m³)</label>
                    <input type="number" name="consumo" id="consumo" step="0.01" required
                           class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                </div>
                <div>
                    <label for="multa" class="block text-sm font-medium text-gray-700">Multa</label>
                    <input type="number" name="multa" id="multa" step="0.01"
                           class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                </div>
                <div>
                    <label for="total" class="block text-sm font-medium text-gray-700">Total</label>
                    <input type="number" name="total" id="total" step="0.01"
                           class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                </div>
                <div>
                    <label for="valor" class="block text-sm font-medium text-gray-700">Valor</label>
                    <input type="number" name="valor" id="valor" step="0.01" required
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
                    <label for="classe_consumo" class="block text-sm font-medium text-gray-700">Classe de Consumo</label>
                    <input type="text" name="classe_consumo" id="classe_consumo"
                           class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                </div>
                <div>
                    <label for="instalacao" class="block text-sm font-medium text-gray-700">Instalação</label>
                    <input type="text" name="instalacao" id="instalacao"
                           class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status" required class="mt-1 block w-full rounded-md border-gray-400 border-2 shadow-sm focus:border-[#072a3a] focus:ring focus:ring-[#072a3a] focus:ring-opacity-50">
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
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Registros de Água</h2>
            
            <form action="index.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <input type="hidden" name="page" value="agua">
                <div>
                    <label for="filtro_secretaria" class="block text-sm font-medium text-gray-700">Secretaria</label>
                    <input type="text" name="filtro_secretaria" id="filtro_secretaria" value="<?= htmlspecialchars($_GET['filtro_secretaria'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="filtro_classe_consumo" class="block text-sm font-medium text-gray-700">Classe</label>
                    <input type="text" name="filtro_classe_consumo" id="filtro_classe_consumo" value="<?= htmlspecialchars($_GET['filtro_classe_consumo'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="filtro_instalacao" class="block text-sm font-medium text-gray-700">Instalação</label>
                    <input type="text" name="filtro_instalacao" id="filtro_instalacao" value="<?= htmlspecialchars($_GET['filtro_instalacao'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-md shadow-sm">
                        Filtrar
                    </button>
                    <a href="index.php?page=agua" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-md shadow-sm ml-2">
                        Limpar
                    </a>
                </div>
            </form>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mês</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Local</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Consumo (m³)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Multa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Secretaria</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classe de Consumo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Instalação</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observações</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($registros as $registro): ?>
                            <tr id="row-<?= htmlspecialchars($registro['id_agua']) ?>">
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registro['mes'] ?? '-'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registro['local'] ?? '-'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registro['consumo'] ?? '-'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registro['multa'] ?? '-'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registro['valor'] ?? '-'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registro['total'] ?? '-'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registro['secretaria'] ?? '-'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registro['classe_consumo'] ?? '-'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registro['instalacao'] ?? '-'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registro['data_vencimento'] ?? '-'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registro['observacoes'] ?? '-'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if (($registro['status'] ?? $registro['Conta_status'] ?? '') === 'pago'): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Pago</span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pendente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <button class="text-indigo-600 hover:text-indigo-900 mr-2">Editar</button>
                                    <button class="text-red-600 hover:text-red-900">Excluir</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php
$consumoMensalJSON = json_encode($consumoMensal);
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const consumoData = <?= $consumoMensalJSON ?>;
    const labels = consumoData.map(item => item.mes_ano);
    const dados = consumoData.map(item => item.consumo_total);

    const ctx = document.getElementById('consumoChart').getContext('2d');
    const consumoChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Consumo (m³)',
                data: dados,
                backgroundColor: 'rgba(99, 102, 241, 0.2)',
                borderColor: 'rgb(99, 102, 241)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Funções para edição e exclusão
    function editarRegistro(id) {
        if (confirm('Deseja editar este registro?')) {
            // Implementar lógica de edição
            document.querySelector('form').action = `?edit=${id}`;
            // Carregar dados do registro no formulário
        }
    }

    function excluirRegistro(id) {
        if (confirm('Tem certeza que deseja excluir este registro?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'index.php?page=agua';
            form.innerHTML = `
                <input type="hidden" name="id" value="${id}">
                <input type="hidden" name="action" value="destroy">
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
