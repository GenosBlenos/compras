<?php
require_once __DIR__ . '/src/includes/auth.php';
require_once __DIR__ . '/src/includes/header.php';
require_once __DIR__ . '/src/controllers/DocumentosController.php';
require_once __DIR__ . '/src/includes/helpers.php'; // Para consistência e futuras funções

$pageTitle = 'Documentos';

// Obtém o módulo da URL, se existir.
$module = $_GET['module'] ?? null;

$controller = new DocumentosController();

// Lógica para exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir'])) {
    $id = $_POST['id'];
    if ($controller->destroy($id)) {
        flashMessage('success', 'Documento excluído com sucesso.');
    } else {
        flashMessage('error', 'Erro ao excluir o documento.');
    }
    header("Location: documentos.php?module=" . urlencode($module ?? ''));
    exit;
}

// Passa o módulo para o controlador para que ele possa filtrar os dados.
$documentos = $controller->index($module);

ob_start();
?>
<div class="container mx-auto">
    <div class="flex justify-end mb-4">
        <a href="documento_form.php?module=<?= htmlspecialchars($module ?? '') ?>" class="bg-[#147cac] hover:bg-[#106191] text-white font-bold py-2 px-4 rounded">
            + Novo Documento
        </a>
    </div>

    <?php if (empty($documentos)): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
            <p>Nenhum documento encontrado.</p>
        </div>
    <?php else: ?>
        <table class="min-w-full bg-white border shadow-md rounded-lg">
            <thead>
                <tr class="bg-gray-100">
                    <th class="py-2 px-4 border-b text-left">Título</th>
                    <th class="py-2 px-4 border-b text-left">Arquivo</th>
                    <th class="py-2 px-4 border-b text-left">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documentos as $documento): ?>
                <tr>
                    <td class="py-2 px-4 border-b"><?= htmlspecialchars($documento['titulo']); ?></td>
                    <td class="py-2 px-4 border-b">
                        <a href="uploads/<?= htmlspecialchars($documento['arquivo']); ?>" target="_blank" class="text-blue-500 hover:underline"><?= htmlspecialchars($documento['arquivo']); ?></a>
                    </td>
                    <td class="py-2 px-4 border-b">
                        <a href="documento_form.php?id=<?= $documento['id'] ?>&module=<?= htmlspecialchars($module ?? '') ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</a>
                        <button onclick="excluirRegistro(<?= $documento['id'] ?>)" class="text-red-600 hover:text-red-900">Excluir</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
function excluirRegistro(id) {
    if (confirm('Tem certeza que deseja excluir este documento? O arquivo também será removido permanentemente.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'documentos.php?module=<?= htmlspecialchars($module ?? '') ?>';
        form.innerHTML = `<input type="hidden" name="id" value="${id}"><input type="hidden" name="excluir" value="1">`;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/src/includes/template.php';