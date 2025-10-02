<?php
require_once __DIR__ . '/../includes/Model.php';

class Internet extends Model
{
    protected $table = 'internet';
    protected $fillable = ['secretaria', 'provedor', 'instalacao', 'data_vencimento', 'valor', 'Conta_status', 'velocidade'];

    // create, update, delete, find are inherited from Model

    public function buscarComFiltros(array $filtros): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $where = [];
        $params = [];

        if (!empty($filtros['secretaria'])) {
            $where[] = "secretaria LIKE :secretaria";
            $params['secretaria'] = '%' . $filtros['secretaria'] . '%';
        }
        if (!empty($filtros['provedor'])) {
            $where[] = "provedor LIKE :provedor";
            $params['provedor'] = '%' . $filtros['provedor'] . '%';
        }
        if (!empty($filtros['instalacao'])) {
            $where[] = "instalacao LIKE :instalacao";
            $params['instalacao'] = '%' . $filtros['instalacao'] . '%';
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY data_vencimento DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStats(): array
    {
        $stats = [];
        $stmt = $this->pdo->prepare("SELECT SUM(valor) as total FROM {$this->table} WHERE Conta_status = 'pendente'");
        $stmt->execute();
        $stats['totalPendente'] = $stmt->fetchColumn() ?: 0;
        
        $sqlMedia = "SELECT ROUND(AVG(
            CASE
                WHEN velocidade LIKE '%Gbps%' OR velocidade LIKE '%Giga%' THEN CAST(velocidade AS UNSIGNED) * 1000
                ELSE CAST(velocidade AS UNSIGNED)
            END
        )) as media_velocidade FROM {$this->table}";
        $stmt = $this->pdo->prepare($sqlMedia);
        $stmt->execute();
        $stats['mediaVelocidade'] = $stmt->fetchColumn() ?: 0;

        $stmt = $this->pdo->prepare("SELECT DATE_FORMAT(data_vencimento, '%Y-%m') as mes_ano, SUM(valor) as valor_total FROM {$this->table} WHERE data_vencimento >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY mes_ano ORDER BY mes_ano ASC");
        $stmt->execute();
        $stats['valorMensal'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }
}