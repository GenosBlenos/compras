<?php
require_once __DIR__ . '/../includes/Model.php';

class Agua extends Model {
    protected $table = 'agua';
    protected $orderBy = 'id_agua';
    protected $fillable = [
        'mes',
        'local',
        'faturado',
        'tarifa',
        'afastamento',
        'esgoto',
        'desconto',
        'outros',
        'multa',
        'total',
        'Conta_status',
        'valor',
        'consumo',
        'data_vencimento',
        'secretaria',
        'classe_consumo',
        'instalacao',
        'observacoes',
        'criado_por',
        'atualizado_por'
    ];
    public function buscarComFiltros($filtros = []) {
        $where = [];
        $params = [];
        if (!empty($filtros['secretaria'])) {
            $where[] = 'secretaria LIKE ?';
            $params[] = '%' . $filtros['secretaria'] . '%';
        }
        if (!empty($filtros['classe_consumo'])) {
            $where[] = 'classe_consumo LIKE ?';
            $params[] = '%' . $filtros['classe_consumo'] . '%';
        }
        if (!empty($filtros['instalacao'])) {
            $where[] = 'instalacao LIKE ?';
            $params[] = '%' . $filtros['instalacao'] . '%';
        }
        if (!empty($filtros['data_vencimento'])) {
            $where[] = 'data_vencimento = ?';
            $params[] = $filtros['data_vencimento'];
        }
        $sql = "SELECT * FROM {$this->table}";
        if ($where) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $sql .= " ORDER BY {$this->orderBy} DESC";
        $stmt = $this->pdo->prepare($sql);
        if ($stmt) {
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

    public function getConsumoMensal() {
        $sql = "SELECT
                    DATE_FORMAT(data_vencimento, '%Y-%m') as mes,
                    SUM(consumo) as total_consumo,
                    SUM(valor) as total_valor
                FROM {$this->table}
                GROUP BY mes
                ORDER BY mes DESC
                LIMIT 12";

        $stmt = $this->pdo->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getMediaConsumo() {
        $sql = "SELECT AVG(consumo) as media_consumo
                FROM {$this->table}
                WHERE data_vencimento >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)";

        $stmt = $this->pdo->query($sql);
        $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
        return $result ? $result['media_consumo'] : 0;
    }

    public function getTotalPendente() {
        $sql = "SELECT SUM(valor) as total FROM {$this->table} WHERE Conta_status = 'pendente'";
        $stmt = $this->pdo->query($sql);
        $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
        return $result ? $result['total'] : 0;
    }
}