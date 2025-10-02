<?php

require_once __DIR__ . '/../includes/helpers.php';

abstract class BaseController {
    protected $model;
    protected $moduleName;

    public function __construct($model, string $moduleName) {
        $this->model = $model;
        $this->moduleName = $moduleName;
    }

    /**
     * Define os campos esperados do POST e seus valores padrão.
     * @return array
     */
    abstract protected function getFields(): array;

    public function index() {
        $data = [];
        $filtros = [];
        
        // Mapeamento de filtros GET para colunas do DB
        $filtroMap = [
            'filtro_secretaria' => 'secretaria',
            'filtro_classe_consumo' => 'classe_consumo',
            'filtro_instalacao' => 'instalacao',
            'filtro_data_vencimento' => 'data_vencimento'
        ];

        foreach ($filtroMap as $param => $coluna) {
            if (!empty($_GET[$param])) {
                $filtros[$coluna] = $_GET[$param];
            }
        }

        $data['registros'] = $this->model->buscarComFiltros($filtros);
        $data['totalPendente'] = $this->model->getTotalPendente();
        $data['mediaConsumo'] = $this->model->getMediaConsumo();
        $data['consumoMensal'] = $this->model->getConsumoMensal();
        
        return $data;
    }

    public function store() {
        if (!$this->validateCSRF()) {
            return;
        }

        $data = $this->prepareDataFromPost($_POST);
        $data['criado_por'] = $_SESSION['usuario_id'];

        if ($this->model->create($data)) {
            gerarLog(ucfirst($this->moduleName) . ' cadastrado(a)', json_encode($data));
            $_SESSION['success'] = "Registro criado com sucesso!";
        } else {
            gerarLog('Erro cadastro ' . $this->moduleName, json_encode($data));
            $_SESSION['error'] = "Erro ao criar registro.";
        }
        $this->redirect();
    }

    public function update() {
        if (!$this->validateCSRF()) {
            return;
        }

        $id = sanitizeInput($_POST['id']);
        $data = $this->prepareDataFromPost($_POST);

        if ($this->model->update($id, $data)) {
            gerarLog(ucfirst($this->moduleName) . ' atualizado(a)', json_encode($data));
            $_SESSION['success'] = "Registro atualizado com sucesso!";
        } else {
            gerarLog('Erro atualização ' . $this->moduleName, json_encode($data));
            $_SESSION['error'] = "Erro ao atualizar registro.";
        }
        $this->redirect();
    }

    public function destroy() {
        // Adicionar validação CSRF para destroy também é uma boa prática
        $id = sanitizeInput($_POST['id']);

        if ($this->model->delete($id)) {
            $_SESSION['success'] = "Registro excluído com sucesso!";
        } else {
            $_SESSION['error'] = "Erro ao excluir registro.";
        }
        $this->redirect();
    }

    protected function prepareDataFromPost(array $postData): array {
        $data = [];
        $fields = $this->getFields();
        foreach ($fields as $field => $defaultValue) {
            $data[$field] = sanitizeInput($postData[$field] ?? $defaultValue);
        }
        return $data;
    }

    protected function validateCSRF(): bool {
        if (!isset($_POST['csrf_token']) || !validarCSRFToken($_POST['csrf_token'])) {
            gerarLog('Falha CSRF', 'Tentativa de ação em ' . $this->moduleName . ' sem token CSRF válido.');
            $_SESSION['error'] = 'Erro de segurança. Tente novamente.';
            $this->redirect();
            return false;
        }
        return true;
    }

    protected function redirect(string $location = null): void {
        $location = $location ?? $this->moduleName . '.php';
        header("Location: {$location}");
        exit;
    }
}
