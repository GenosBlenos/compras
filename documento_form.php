<?php
require_once __DIR__ . '/src/includes/auth.php';
require_once __DIR__ . '/src/includes/header.php';
$pageTitle = 'Cadastro de Documento';

require_once __DIR__ . '/src/controllers/DocumentosController.php';
require_once __DIR__ . '/src/includes/helpers.php';

// Inicia a sessão e verifica a autenticação
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['logado']) || !$_SESSION['logado']) {
    header('Location: /compras/login.php');
    exit;
}

$controller = new DocumentosController();
$module = $_GET['module'] ?? $_POST['module'] ?? null;

// Processamento do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'titulo' => $_POST['titulo'] ?? '',
    ];
    $file = $_FILES['arquivo'] ?? null;
    $id = $_POST['id'] ?? null;

    if ($id) {
        // Atualização
        $success = $controller->update($id, $data, $file);
    } else {
        // Criação
        $success = $controller->store($data, $file);
    }

    if ($success) {
        flashMessage('success', 'Documento salvo com sucesso!');
        header('Location: documentos.php?module=' . urlencode($module ?? ''));
        exit;
    } else {
        flashMessage('error', 'Ocorreu um erro ao salvar o documento. Verifique se o arquivo foi enviado.');
    }
}

// Carregamento dos dados para edição ou formulário em branco
$documento = null;
$pageTitle = 'Novo Documento';
if (isset($_GET['id'])) {
    $documento = $controller->show($_GET['id']);
    $pageTitle = 'Editar Documento';
}

require_once __DIR__ . '/src/includes/header.php';
ob_start();
?>

<form action="documento_form.php" method="POST" enctype="multipart/form-data" class="space-y-4">
    <input type="hidden" name="id" value="<?= htmlspecialchars($documento['id'] ?? '') ?>">
    <input type="hidden" name="module" value="<?= htmlspecialchars($module ?? '') ?>">

    <div>
        <label for="titulo" class="block text-sm font-medium text-gray-700">Título do Documento</label>
        <input type="text" name="titulo" id="titulo" value="<?= htmlspecialchars($documento['titulo'] ?? '') ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label for="arquivo" class="block text-sm font-medium text-gray-700">Arquivo (PDF, JPG, PNG)</label>
        <input type="file" name="arquivo" id="arquivo" <?= !isset($documento) ? 'required' : '' ?> class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
        <?php if (isset($documento['arquivo'])): ?>
            <p class="text-sm text-gray-500 mt-2">Arquivo atual: <a href="uploads/<?= htmlspecialchars($documento['arquivo']) ?>" target="_blank" class="text-blue-500"><?= htmlspecialchars($documento['arquivo']) ?></a>. Envie um novo arquivo para substituí-lo.</p>
        <?php endif; ?>
    </div>

    <div class="flex justify-end space-x-2">
        <a href="documentos.php?module=<?= htmlspecialchars($module ?? '') ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded">Cancelar</a>
        <button type="submit" class="bg-[#147cac] hover:bg-[#106191] text-white font-bold py-2 px-4 rounded">Salvar</button>
    </div>
</form>

<?php
$content = ob_get_clean();
require __DIR__ . '/src/includes/template.php';
?>