<?php
require_once __DIR__ . '/../includes/Model.php';
class Semparar extends Model {
    protected $table = 'semparar';
    protected $orderBy = 'data_org';
    protected $fillable = [
        'data_org',
        'combustivel',
        'marca',
        'modelo',
        'departamento',
        'ficha',
        'secretaria',
        'tag',
        'mensalidade',
        'passagens',
        'estacionamento',
        'estabelecimentos',
        'credito',
        'isento',
        'mes',
        'total',
        'valor',
        'consumo',
        'Conta_status',
        'data_vencimento',
        'observacoes',
        'criado_por',
        'atualizado_por'
    ];

    /**
     * Calcula o total de gastos do mês atual.
     * @return float
     */
    public function getTotalDoMes() {
        // Corrigido para usar Conta_status = 'pago'
        $sql = "SELECT SUM(valor) as total FROM {$this->table}
        WHERE YEAR(data_org) = YEAR(CURRENT_DATE()) AND MONTH(data_org) = MONTH(CURRENT_DATE()) AND Conta_status = 'pago'";
        $stmt = $this->pdo->query($sql);
        $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
        return $result ? $result['total'] : 0;
    }

    /**
     * Calcula a média de gastos por fatura.
     * @return float
     */
    public function getMediaPorFatura() {
        $sql = "SELECT AVG(valor) as media FROM {$this->table}";
        $stmt = $this->pdo->query($sql);
        $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
        return $result ? $result['media'] : 0;
    }

    /**
     * Calcula o total de gastos do ano atual.
     * @return float
     */
    public function getTotalAnual() {
        // Corrigido para usar Conta_status = 'pago'
        $sql = "SELECT SUM(valor) as total FROM {$this->table} WHERE YEAR(data_org) = YEAR(CURRENT_DATE()) AND Conta_status = 'pago'";
        $stmt = $this->pdo->query($sql);
        $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
        return $result ? $result['total'] : 0;
    }
}