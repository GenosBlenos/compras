<?php
require_once __DIR__ . '/src/includes/auth.php';
require_once __DIR__ . '/src/includes/header.php';

$controller = new KpisController();
$kpis = $controller->index();

$pageTitle = 'KPIs';
ob_start();
?>
<div class="container mx-auto">
    <table class="min-w-full bg-white border">
        <thead>
            <tr>
                <th class="py-2 px-4 border-b">Nome</th>
                <th class="py-2 px-4 border-b">Valor</th>
                <th class="py-2 px-4 border-b">Referência</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($kpis as $kpi): ?>
            <tr>
                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($kpi['nome']); ?></td>
                <td class="py-2 px-4 border-b"><?php echo number_format($kpi['valor'], 2, ',', '.'); ?></td>
                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($kpi['referencia']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/src/includes/template.php';
