<?php
ob_start();
// Exibir mensagens de sucesso ou erro da sessão
if (isset($_SESSION['success'])) {
    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">' . htmlspecialchars($_SESSION['success']) . '</div>';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">' . htmlspecialchars($_SESSION['error']) . '</div>';
    unset($_SESSION['error']);
}
?>
<div class="space-y-6">
    <h2 class="text-xl font-bold">Unidades</h2>
    <table class="min-w-full bg-white">
        <thead>
            <tr>
                <th class="py-2">Nome</th>
                <th class="py-2">Endereço</th>
                <th class="py-2">Responsável</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($registros)): foreach ($registros as $registro): ?>
            <tr>
                <td class="py-2"><?php echo htmlspecialchars($registro['nome'] ?? ''); ?></td>
                <td class="py-2"><?php echo htmlspecialchars($registro['endereco'] ?? ''); ?></td>
                <td class="py-2"><?php echo htmlspecialchars($registro['responsavel'] ?? ''); ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="3">Nenhum registro encontrado.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<form action="index.php?page=unidades" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(gerarCSRFToken()); ?>">
    <!-- Campos do formulário aqui -->
</form>
