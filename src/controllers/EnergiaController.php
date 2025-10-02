<?php
require_once __DIR__ . '/../models/Energia.php';
require_once __DIR__ . '/BaseController.php';

class EnergiaController extends BaseController {

    public function __construct() {
        $model = new Energia();
        parent::__construct($model, 'energia');
    }

    /**
     * {@inheritdoc}
     */
    protected function getFields(): array {
        return [
            'mes' => null,
            'local' => null,
            'instalacao' => null,
            'consumo' => 0,
            'multa' => 0,
            'total' => 0,
            'Conta_status' => 'pendente',
            'valor' => 0,
            'data_vencimento' => null,
            'secretaria' => null,
            'classe_consumo' => null,
            'observacoes' => null,
        ];
    }
}