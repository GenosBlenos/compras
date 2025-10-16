<?php
require_once __DIR__ . '/../src/includes/config.php';
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
        
        <form id="pdfUploadForm" action="processa_pdf.php" method="post" enctype="multipart/form-data" class="space-y-4">
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
                <button type="submit" name="salvar" id="submitBtn" class="bg-[#4a90e2] hover:bg-[#2563eb] text-white font-bold py-2 px-4 rounded-md shadow-sm">
                    Processar e Cadastrar
                </button>
            </div>
        </form>
        <!-- Div para exibir o status do processamento -->
        <div id="status" class="mt-4"></div>
    </div>
</div>

<script>
document.getElementById('pdfUploadForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Impede o envio padrão do formulário

    const form = e.target;
    const formData = new FormData(form);
    const statusDiv = document.getElementById('status');
    const submitButton = document.getElementById('submitBtn');

    // Desabilita o botão e mostra mensagem de processamento
    submitButton.disabled = true;
    submitButton.textContent = 'Processando...';
    statusDiv.innerHTML = `<div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4" role="alert"><p>Enviando e processando o arquivo. Isso pode levar alguns instantes...</p></div>`;

    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json()) // Espera uma resposta JSON do servidor
    .then(data => {
        if (data.success) {
            statusDiv.innerHTML = `<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert"><p>${data.message}</p></div>`;
            form.reset(); // Limpa o formulário em caso de sucesso
        } else {
            // Exibe a mensagem de erro retornada pelo servidor
            statusDiv.innerHTML = `<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert"><p><b>Erro:</b> ${data.message || 'Ocorreu um erro desconhecido.'}</p></div>`;
        }
    })
    .catch(error => {
        statusDiv.innerHTML = `<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert"><p><b>Erro Crítico:</b> Não foi possível se comunicar com o servidor ou o servidor retornou uma resposta inválida (página em branco). Verifique o log de erros do servidor para mais detalhes.</p></div>`;
        console.error('Fetch Error:', error);
    })
    .finally(() => {
        // Reabilita o botão ao final do processo
        submitButton.disabled = false;
        submitButton.textContent = 'Processar e Cadastrar';
    });
});
</script>

<?php
// Captura o conteúdo do buffer
$content = ob_get_clean();

// Inclui o arquivo de template principal que montará a página
require_once __DIR__ . '/../src/includes/template.php';
?>