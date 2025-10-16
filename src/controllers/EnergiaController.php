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
            'unidade' => null,
            'consumo_kwh' => 0,
            'valor_fatura' => 0,
            'multa' => 0,
            'total' => 0,
            'status' => 'pendente',
            'data_vencimento' => null,
            'secretaria' => null,
            'tipo_consumo' => null,
            'num_instalacao' => null,
            'observacao' => null,
            'demanda_contratada' => null,
            'demanda_registrada' => null,
            'demanda_faturada' => null,
            'fator_potencia' => null,
            'grupo_tarifario' => null
        ];
    }
}