<?php
require_once __DIR__ . '/../src/includes/auth.php';
require_once __DIR__ . '/../src/includes/header.php';
require_once __DIR__ . '/../src/includes/helpers.php'; // Adicionado para garantir que a função gerarCSRFToken esteja disponível
$pageTitle = 'Cadastro de Fatura PDF';

// Redireciona para a página de login se o usuário não estiver autenticado
if (!isset($_SESSION['logado']) || !$_SESSION['logado']) {
    header('Location: /compras/login.php');
    exit;
}

// Define o módulo atual para que o menu lateral possa destacá-lo, se aplicável.
$_GET['module'] = 'cad_fatura_pdf'; 

// Inicia o buffer de saída para capturar o conteúdo HTML
ob_start();
?>

<div class="space-y-6">
    <?php
    // Exibe a mensagem da sessão, se houver
    if (isset($_SESSION['msg'])) {
        // Você pode adicionar uma lógica aqui para diferenciar tipos de mensagem (erro, sucesso)
        // Por enquanto, usaremos um estilo de informação genérico.
        echo $_SESSION['msg'];
        unset($_SESSION['msg']);
    }
    ?>

    <div class="bg-white p-6 rounded-lg shadow-lg">
        <h2 class="text-xl font-semibold text-gray-800 mb-2">Cadastro de Conta por PDF</h2>
        <p class="text-gray-600 mb-6">Envie uma Nota Fiscal ou comprovante em PDF para cadastrar a conta automaticamente.</p>
        
        <form action="processa_pdf.php" method="post" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(gerarCSRFToken()); ?>">
            <div>
                <label for="pdfFile" class="block text-sm font-medium text-gray-700">Arquivo PDF</label>
                <input type="file" name="pdfFile" id="pdfFile" accept=".pdf" required 
                       class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-[#4a90e2] file:text-white hover:file:bg-[#2563eb]">
            </div>

            <div class="flex items-center justify-end space-x-2 pt-4">
                <a href="index.php" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-md shadow-sm">
                    Voltar
                </a>
                <button type="submit" name="salvar" class="bg-[#4a90e2] hover:bg-[#2563eb] text-white font-bold py-2 px-4 rounded-md shadow-sm">
                    Processar e Cadastrar
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Captura o conteúdo do buffer
$content = ob_get_clean();

// Inclui o arquivo de template principal que montará a página
require_once __DIR__ . '/../src/includes/template.php';
?>