<?php

$script_name = basename($_SERVER['SCRIPT_NAME'], '.php');
// Usamos $_GET['module'] diretamente, pois ele pode ser definido programaticamente
// dentro de um script (ex: internet.php), o que filter_input(INPUT_GET,...) não captura.
$module_param = isset($_GET['module']) ? htmlspecialchars($_GET['module'], ENT_QUOTES, 'UTF-8') : null;

// Mapeamento dos módulos
$modulos = [
    'agua' => ['label' => 'Água Predial', 'href' => 'agua.php', 'id' => 'agua'],
    'energia' => ['label' => 'Energia Elétrica', 'href' => 'energia.php', 'id' => 'energia'],
    'semparar' => ['label' => 'Sem Parar', 'href' => 'semparar.php', 'id' => 'semparar'],
    'telefone' => ['label' => 'Telefonia Fixa', 'href' => 'telefone.php', 'id' => 'telefone'],
    'internet' => ['label' => 'Internet Predial', 'href' => 'internet.php', 'id' => 'internet'],
];

// Define os itens do menu com seus links. Tornamos o menu robusto caso
// $module_param não exista (ex.: header incluído antes de definir $_GET['module']).
$menu_items = [];
$effective_module = $module_param ?: $script_name;
if ($module_param && isset($modulos[$module_param])) {
    // Módulo principal (exibe o nome do módulo no menu)
    $menu_items[] = $modulos[$module_param];
}

// Items padrão do submenu (sempre gerados usando o módulo efetivo)
$menu_items = array_merge($menu_items, [
    ["label" => "Faturas", "href" => "faturas.php?module={$effective_module}", "id" => "faturas"],
    ["label" => "Unidades", "href" => "unidades.php?module={$effective_module}", "id" => "unidades"],
    ["label" => "KPIs", "href" => "kpis.php?module={$effective_module}", "id" => "kpis"],
    ["label" => "Documentos", "href" => "documentos.php?module={$effective_module}", "id" => "documentos"],
    ["label" => "Configurações", "href" => "configuracoes.php?module={$effective_module}", "id" => "configuracoes"],
]);

// O módulo "real" pode vir do parâmetro GET ou do nome do script.
$current_page_for_menu = $module_param ?: $script_name;

// O dashboard é acessado via index.php.
if ($script_name === 'index') {
    $current_page_for_menu = 'dashboard';
}

// Exibe o menu apenas se não estivermos no dashboard ou em outras páginas específicas.
$no_menu_pages = ['dashboard', 'support', 'cad_fatura_pdf', 'gerar_csv'];
$shouldShowMenu = !in_array($current_page_for_menu, $no_menu_pages) && !empty($menu_items);

if ($shouldShowMenu):
?>
<nav class="bg-[#147cac] text-white shadow">
    <div class="mx-auto px-4 py-2">
        <ul class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 sm:gap-x-8">
            <?php foreach (array_merge($menu_items, [["label" => "Gerar CSV", "href" => "gerar_csv.php", "id" => "gerar_csv"]]) as $item):
                $is_active = ($item['id'] === basename($_SERVER['SCRIPT_NAME'], '.php'));

                $link_class = 'font-semibold transition px-3 py-2 rounded ';
                $link_class .= $is_active ? 'bg-white text-[#147cac] pointer-events-none border-b-4 border-[#147cac] shadow-lg' : 'text-white hover:bg-[#106191]';
            ?>
            <li>
                <a href="<?= htmlspecialchars($item['href']) ?>"
                   class="<?= htmlspecialchars($link_class) ?>"
                >
                    <?= htmlspecialchars($item['label']) ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>
<?php endif; ?>