<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../models/Telefone.php';

class TelefoneController {
    public function index() {
        $telefoneModel = new Telefone();
        $data = [];
        $data['registros'] = $telefoneModel->all();
        // Adicione outros dados agregados se necessário
        return $data;
    }

    public function store() {
        // Validação CSRF
        if (!isset($_POST['csrf_token']) || !validarCSRFToken($_POST['csrf_token'])) {
            gerarLog('Falha CSRF', 'Tentativa de cadastro de telefone sem token CSRF válido.');
            $_SESSION['error'] = 'Erro de segurança. Tente novamente.';
            header('Location: telefone.php');
            exit;
        }
        $telefoneModel = new Telefone();
        $data = [
            'numero' => sanitizeInput($_POST['numero'] ?? null),
            'valor' => sanitizeInput($_POST['valor'] ?? 0),
            'data_vencimento' => sanitizeInput($_POST['data_vencimento'] ?? null),
            'provedor' => sanitizeInput($_POST['provedor'] ?? null),
            'observacoes' => sanitizeInput($_POST['observacoes'] ?? null),
            'criado_por' => $_SESSION['usuario_id']
        ];
        if ($telefoneModel->create($data)) {
            gerarLog('Telefone cadastrado', json_encode($data));
            $_SESSION['success'] = "Registro criado com sucesso!";
        } else {
            gerarLog('Erro cadastro telefone', json_encode($data));
            $_SESSION['error'] = "Erro ao criar registro.";
        }
        header('Location: telefone.php');
        exit;
    }

    public function update() {
        // Validação CSRF
        if (!isset($_POST['csrf_token']) || !validarCSRFToken($_POST['csrf_token'])) {
            gerarLog('Falha CSRF', 'Tentativa de atualização de telefone sem token CSRF válido.');
            $_SESSION['error'] = 'Erro de segurança. Tente novamente.';
            header('Location: telefone.php');
            exit;
        }
        $telefoneModel = new Telefone();
        $id = sanitizeInput($_POST['id']);
        $data = [
            'numero' => sanitizeInput($_POST['numero'] ?? null),
            'valor' => sanitizeInput($_POST['valor'] ?? 0),
            'data_vencimento' => sanitizeInput($_POST['data_vencimento'] ?? null),
            'provedor' => sanitizeInput($_POST['provedor'] ?? null),
            'observacoes' => sanitizeInput($_POST['observacoes'] ?? null)
        ];
        if ($telefoneModel->update($id, $data)) {
            gerarLog('Telefone atualizado', json_encode($data));
            $_SESSION['success'] = "Registro atualizado com sucesso!";
        } else {
            gerarLog('Erro atualização telefone', json_encode($data));
            $_SESSION['error'] = "Erro ao atualizar registro.";
        }
        header('Location: telefone.php');
        exit;
    }

    public function destroy() {
        // Validação CSRF
        if (!isset($_POST['csrf_token']) || !validarCSRFToken($_POST['csrf_token'])) {
            gerarLog('Falha CSRF', 'Tentativa de exclusão de telefone sem token CSRF válido.');
            $_SESSION['error'] = 'Erro de segurança. Tente novamente.';
            header('Location: telefone.php');
            exit;
        }
        $telefoneModel = new Telefone();
        $id = sanitizeInput($_POST['id']);
        if ($telefoneModel->delete($id)) {
            gerarLog('Telefone excluído', $id);
            $_SESSION['success'] = "Registro excluído com sucesso!";
        } else {
            gerarLog('Erro exclusão telefone', $id);
            $_SESSION['error'] = "Erro ao excluir registro.";
        }
        header('Location: telefone.php');
        exit;
    }
}
