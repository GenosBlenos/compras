<?php
require_once __DIR__ . '/../models/Semparar.php';
require_once __DIR__ . '/BaseController.php';

class SempararController extends BaseController {
    public function __construct() {
        $model = new Semparar();
        parent::__construct($model, 'semparar');
    }

    protected function getFields(): array {
        return [
            'mes' => null,
            'unidade' => null,
            'valor_fatura' => 0,
            'multa' => 0,
            'total' => 0,
            'status' => 'pendente',
            'data_vencimento' => null,
            'secretaria' => null,
            'placa_veiculo' => null,
            'passagens' => 0,
            'observacao' => null
        ];
    }
}