<?php
require_once __DIR__ . '/src/includes/auth.php';
require_once __DIR__ . '/src/includes/header.php';
$pageTitle = 'Cadastro de Unidade';

require_once __DIR__ . '/src/controllers/UnidadesController.php';
require_once __DIR__ . '/src/includes/helpers.php';

// Inicia a sessão e verifica a autenticação
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['logado']) || !$_SESSION['logado']) {
    header('Location: /compras/login.php');
    exit;
}

$controller = new UnidadesController();
$module = $_GET['module'] ?? $_POST['module'] ?? null;

// Processamento do formulário (Criação/Atualização)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nome' => $_POST['nome'] ?? '',
        'endereco' => $_POST['endereco'] ?? '',
        'responsavel' => $_POST['responsavel'] ?? '',
        // Se o filtro por módulo for implementado no DB, adicione aqui:
        // 'modulo' => $module,
    ];

    // Sanitiza os dados
    $data = sanitizeInput($data);

    $id = $_POST['id'] ?? null;

    if ($id) {
        // Atualização
        $success = $controller->update($id, $data);
    } else {
        // Criação
        $success = $controller->store($data);
    }

    if ($success) {
        flashMessage('success', 'Unidade salva com sucesso!');
        header('Location: unidades.php?module=' . urlencode($module ?? ''));
        exit;
    } else {
        $error_message = 'Ocorreu um erro ao salvar a unidade.';
    }
}

// Carregamento dos dados para edição ou formulário em branco para criação
$unidade = null;
$pageTitle = 'Nova Unidade';
if (isset($_GET['id'])) {
    $unidade = $controller->show($_GET['id']);
    $pageTitle = 'Editar Unidade';
}

ob_start();
?>

<?php if (isset($error_message)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
        <?= htmlspecialchars($error_message) ?>
    </div>
<?php endif; ?>

<form action="unidade_form.php" method="POST" class="space-y-4">
    <input type="hidden" name="id" value="<?= htmlspecialchars($unidade['id'] ?? '') ?>">
    <input type="hidden" name="module" value="<?= htmlspecialchars($module ?? '') ?>">

    <div>
        <label for="nome" class="block text-sm font-medium text-gray-700">Nome da Unidade</label>
        <input type="text" name="nome" id="nome" value="<?= htmlspecialchars($unidade['nome'] ?? '') ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div>
        <label for="endereco" class="block text-sm font-medium text-gray-700">Endereço</label>
        <input type="text" name="endereco" id="endereco" value="<?= htmlspecialchars($unidade['endereco'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div>
        <label for="responsavel" class="block text-sm font-medium text-gray-700">Responsável</label>
        <input type="text" name="responsavel" id="responsavel" value="<?= htmlspecialchars($unidade['responsavel'] ?? '') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
    <div class="flex justify-end space-x-2">
        <a href="unidades.php?module=<?= htmlspecialchars($module ?? '') ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded">Cancelar</a>
        <button type="submit" class="bg-[#147cac] hover:bg-[#106191] text-white font-bold py-2 px-4 rounded">Salvar</button>
    </div>
</form>

<?php
$content = ob_get_clean();
require __DIR__ . '/src/includes/template.php';
?>