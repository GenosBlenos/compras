<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../models/Internet.php';

class InternetController {
    public function index() {
        $internetModel = new Internet();
        $data = [];
        $data['registros'] = $internetModel->buscarComFiltros([]);
        $stats = $internetModel->getStats();
        $data = array_merge($data, $stats);
        return $data;
    }

    public function store() {
        // Validação CSRF
        if (!isset($_POST['csrf_token']) || !validarCSRFToken($_POST['csrf_token'])) {
            gerarLog('Falha CSRF', 'Tentativa de cadastro de internet sem token CSRF válido.');
            $_SESSION['error'] = 'Erro de segurança. Tente novamente.';
            header('Location: internet.php');
            exit;
        }
        $internetModel = new Internet();
        $data = [
            'provedor' => sanitizeInput($_POST['provedor'] ?? null),
            'valor' => sanitizeInput($_POST['valor'] ?? 0),
            'data_vencimento' => sanitizeInput($_POST['data_vencimento'] ?? null),
            'velocidade' => sanitizeInput($_POST['velocidade'] ?? null),
            'observacoes' => sanitizeInput($_POST['observacoes'] ?? null),
            'criado_por' => $_SESSION['usuario_id']
        ];
        if ($internetModel->create($data)) {
            gerarLog('Internet cadastrada', json_encode($data));
            $_SESSION['success'] = "Registro criado com sucesso!";
        } else {
            gerarLog('Erro cadastro internet', json_encode($data));
            $_SESSION['error'] = "Erro ao criar registro.";
        }
        header('Location: internet.php');
        exit;
    }

    public function update() {
        // Validação CSRF
        if (!isset($_POST['csrf_token']) || !validarCSRFToken($_POST['csrf_token'])) {
            gerarLog('Falha CSRF', 'Tentativa de atualização de internet sem token CSRF válido.');
            $_SESSION['error'] = 'Erro de segurança. Tente novamente.';
            header('Location: internet.php');
            exit;
        }
        $internetModel = new Internet();
        $id = sanitizeInput($_POST['id']);
        $data = [
            'provedor' => sanitizeInput($_POST['provedor'] ?? null),
            'valor' => sanitizeInput($_POST['valor'] ?? 0),
            'data_vencimento' => sanitizeInput($_POST['data_vencimento'] ?? null),
            'velocidade' => sanitizeInput($_POST['velocidade'] ?? null),
            'observacoes' => sanitizeInput($_POST['observacoes'] ?? null)
        ];
        if ($internetModel->update($id, $data)) {
            gerarLog('Internet atualizada', json_encode($data));
            $_SESSION['success'] = "Registro atualizado com sucesso!";
        } else {
            gerarLog('Erro atualização internet', json_encode($data));
            $_SESSION['error'] = "Erro ao atualizar registro.";
        }
        header('Location: internet.php');
        exit;
    }

    public function destroy() {
        // Validação CSRF
        if (!isset($_POST['csrf_token']) || !validarCSRFToken($_POST['csrf_token'])) {
            gerarLog('Falha CSRF', 'Tentativa de exclusão de internet sem token CSRF válido.');
            $_SESSION['error'] = 'Erro de segurança. Tente novamente.';
            header('Location: internet.php');
            exit;
        }
        $internetModel = new Internet();
        $id = sanitizeInput($_POST['id']);
        if ($internetModel->delete($id)) {
            gerarLog('Internet excluída', $id);
            $_SESSION['success'] = "Registro excluído com sucesso!";
        } else {
            gerarLog('Erro exclusão internet', $id);
            $_SESSION['error'] = "Erro ao excluir registro.";
        }
        header('Location: internet.php');
        exit;
    }
}
