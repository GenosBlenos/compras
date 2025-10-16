<?php
require_once __DIR__ . '/../models/Telefone.php';
require_once __DIR__ . '/BaseController.php';

class TelefoneController extends BaseController {
    public function __construct() {
        $model = new Telefone();
        parent::__construct($model, 'telefone');
    }

    protected function getFields(): array {
        return [
            'mes' => null,
            'unidade' => null,
            'numero_linha' => null,
            'valor_fatura' => 0,
            'multa' => 0,
            'total' => 0,
            'status' => 'pendente',
            'data_vencimento' => null,
            'secretaria' => null,
            'tipo_servico' => null,
            'plano' => null,
            'minutos_utilizados' => 0,
            'dados_utilizados' => 0,
            'observacao' => null
        ];
    }
}
