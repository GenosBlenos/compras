<div class="mt-8 bg-white p-6 rounded-lg shadow-lg">
    <h2 class="text-xl font-bold text-gray-800 mb-4">Visão Geral</h2>
    <p class="text-gray-600">
        Bem-vindo ao Sistema de Controle de Gastos. Utilize os cards acima para navegar entre os diferentes módulos de despesa.
        Cada módulo permite o cadastro, visualização e gerenciamento das faturas correspondentes.
    </p>
    <p class="mt-2 text-gray-600">
        A seção de <strong>Relatórios</strong> consolida os dados de todos os módulos para uma análise global, enquanto a seção de <strong>Recomendações</strong> utiliza inteligência para apontar possíveis economias e otimizações.
    </p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mt-5">
    
    <!-- Card Água -->
    <a href="agua.php" class="bg-white rounded-lg shadow-lg p-6 flex flex-col items-center justify-center hover:shadow-xl transition-shadow duration-300">
        <img src="../assets/water.png" alt="Água" class="w-16 h-16 mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Água Predial</h3>
    </a>

    <!-- Card Energia -->
    <a href="energia.php" class="bg-white rounded-lg shadow-lg p-6 flex flex-col items-center justify-center hover:shadow-xl transition-shadow duration-300">
        <img src="../assets/flash.png" alt="Energia" class="w-16 h-16 mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Energia Elétrica</h3>
    </a>

    <!-- Card Sem Parar -->
    <a href="semparar.php" class="bg-white rounded-lg shadow-lg p-6 flex flex-col items-center justify-center hover:shadow-xl transition-shadow duration-300">
        <img src="../assets/car.png" alt="Sem Parar" class="w-16 h-16 mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Sem Parar</h3>
    </a>

    <!-- Card Telefone -->
    <a href="telefone.php" class="bg-white rounded-lg shadow-lg p-6 flex flex-col items-center justify-center hover:shadow-xl transition-shadow duration-300">
        <img src="../assets/phone.png" alt="Telefone" class="w-16 h-16 mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Telefonia Fixa</h3>
    </a>

    <!-- Card Internet -->
    <a href="internet.php" class="bg-white rounded-lg shadow-lg p-6 flex flex-col items-center justify-center hover:shadow-xl transition-shadow duration-300">
        <img src="../assets/wifi.png" alt="Internet" class="w-16 h-16 mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Internet Predial</h3>
    </a>

    <!-- Card Relatórios -->
    <a href="relatorios.php" class="bg-white rounded-lg shadow-lg p-6 flex flex-col items-center justify-center hover:shadow-xl transition-shadow duration-300">
        <img src="../assets/report.png" alt="Relatórios" class="w-16 h-16 mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Relatórios</h3>
    </a>

    <!-- Card Recomendações -->
    <a href="recomendacoes.php" class="bg-white rounded-lg shadow-lg p-6 flex flex-col items-center justify-center hover:shadow-xl transition-shadow duration-300">
        <img src="../assets/recommendation.png" alt="Recomendações" class="w-16 h-16 mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Recomendações</h3>
    </a>

    <!-- Card Suporte -->
    <a href="support.php" class="bg-white rounded-lg shadow-lg p-6 flex flex-col items-center justify-center hover:shadow-xl transition-shadow duration-300">
        <img src="../assets/support.png" alt="Ajuda" class="w-16 h-16 mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Ajuda e Suporte</h3>
    </a>

    <!-- Card Cadastrar Fatura PDF -->
    <a href="cad_fatura_pdf.php" class="bg-blue-500 text-white rounded-lg shadow-lg p-6 flex flex-col items-center justify-center hover:bg-blue-600 transition-colors duration-300 col-span-1 sm:col-span-2 md:col-span-3 lg:col-span-4">
        <div class="flex items-center">
            <img src="../assets/conta.png" alt="Upload" class="w-12 h-12 mr-4">
            <div>
                <h3 class="text-xl font-bold">Cadastrar Fatura por PDF</h3>
                <p class="text-sm">Envie um arquivo PDF para extrair os dados da fatura automaticamente.</p>
            </div>
        </div>
    </a>
</div>
