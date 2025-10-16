<?php
require_once __DIR__ . '/../models/Unidade.php';
class UnidadesController {
    public function index($module = null) {
        $model = new Unidade();
        // A lógica de filtro por módulo pode ser adicionada aqui se a coluna 'modulo' existir no DB
        if ($module) {
            // return $model->where('modulo', $module);
        }
        return $model->all(); // Retorna todos por enquanto
    }

    public function show($id) {
        $model = new Unidade();
        return $model->find($id);
    }

    public function store($data) {
        $model = new Unidade();
        return $model->create($data);
    }

    public function update($id, $data) {
        $model = new Unidade();
        return $model->update($id, $data);
    }

    public function destroy($id) {
        $model = new Unidade();
        return $model->delete($id);
    }
}
