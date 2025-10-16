<?php
require_once __DIR__ . '/../models/Agua.php';
require_once __DIR__ . '/BaseController.php';

class AguaController extends BaseController {

    public function __construct() {
        $model = new Agua();
        parent::__construct($model, 'agua');
    }

    /**
     * {@inheritdoc}
     */
    protected function getFields(): array {
        return [
            'mes' => null,
            'unidade' => null,
            'consumo_m3' => 0,
            'valor_fatura' => 0,
            'multa' => 0,
            'total' => 0,
            'status' => 'pendente',
            'data_vencimento' => null,
            'secretaria' => null,
            'tipo_consumo' => null,
            'num_instalacao' => null,
            'observacao' => null,
        ];
    }
}