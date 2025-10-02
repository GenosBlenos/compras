<?php
require_once __DIR__ . '/src/includes/auth.php';
require_once __DIR__ . '/src/includes/header.php';

$controller = new FaturasController();
$faturas = $controller->index();

$pageTitle = 'Faturas';
ob_start();
?>
    <table class="min-w-full bg-white border">
        <thead>
            <tr>
                <th class="py-2 px-4 border-b">Descrição</th>
                <th class="py-2 px-4 border-b">Valor</th>
                <th class="py-2 px-4 border-b">Vencimento</th>
                <th class="py-2 px-4 border-b">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($faturas as $fatura): ?>
            <tr>
                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($fatura['descricao']); ?></td>
                <td class="py-2 px-4 border-b">R$ <?php echo number_format($fatura['valor'], 2, ',', '.'); ?></td>
                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($fatura['vencimento']); ?></td>
                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($fatura['status']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php
$content = ob_get_clean();
require __DIR__ . '/src/includes/template.php';
