<?php
require_once __DIR__ . '/src/controllers/ConfiguracoesController.php';
require_once __DIR__ . '/src/includes/header.php';

// Obtém o módulo da URL, se existir.
$module = $_GET['module'] ?? null;

$controller = new ConfiguracoesController();
// Passa o módulo para o controlador para que ele possa filtrar os dados.
$configs = $controller->index($module);

$pageTitle = 'Configurações';
ob_start();
?>
<div class="container mx-auto">
    <table class="min-w-full bg-white border">
        <thead>
            <tr>
                <th class="py-2 px-4 border-b">Chave</th>
                <th class="py-2 px-4 border-b">Valor</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($configs as $cfg): ?>
            <tr>
                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($cfg['chave']); ?></td>
                <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($cfg['valor']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/src/includes/template.php';
