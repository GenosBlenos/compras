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
            'local' => null,
            'consumo' => 0,
            'multa' => 0,
            'total' => 0,
            'Conta_status' => 'pendente',
            'valor' => 0,
            'data_vencimento' => null,
            'secretaria' => null,
            'classe_consumo' => null,
            'instalacao' => null,
            'observacoes' => null,
        ];
    }
}