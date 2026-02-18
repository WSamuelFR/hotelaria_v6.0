<?php
// Arquivo: app/controllers/cadastro_process.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Usando caminhos absolutos e o nome correto
require_once(__DIR__ . '/../models/cadastroModel.php');
require_once(__DIR__ . '/../models/loggerModel.php'); 

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Método de requisição inválido.'
];

// Rota de verificação de duplicidade (via GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['check_duplicity'])) {
    $valor = $_GET['value'] ?? '';
    try {
        $model = new CadastroModel();
        $existe = $model->checkCpfCnpjExists($valor);
        echo json_encode(['success' => true, 'exists' => $existe]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 2. Captura hibrida (JSON ou POST comum)
    $json_data = file_get_contents('php://input');
    $data_decoded = json_decode($json_data, true);
    $data = !empty($data_decoded) ? $data_decoded : $_POST;

    // 3. Validações básicas
    $tipo = $data['tipo'] ?? '';
    
    if (empty($data['email']) || empty($data['phone'])) {
        echo json_encode(['success' => false, 'message' => 'E-mail e telefone são obrigatórios.']);
        exit;
    }
    
    // Validação específica por tipo
    if ($tipo == 'hospede' && (empty($data['full_name']) || empty($data['cpf']))) {
         echo json_encode(['success' => false, 'message' => 'Nome e CPF são obrigatórios para hóspedes.']);
         exit;
    }

    if ($tipo == 'empresa' && (empty($data['company_name']) || empty($data['cnpj']))) {
         echo json_encode(['success' => false, 'message' => 'Nome da Empresa e CNPJ são obrigatórios.']);
         exit;
    }
    
    try {
        $model = new CadastroModel();
        $result = $model->insertCadastro($data);

        if (is_numeric($result)) {
            $response['success'] = true;
            $response['message'] = 'Cadastro realizado com sucesso!';
            $response['cadastro_id'] = $result;

            // --- REGISTRO DE AUDITORIA (LOG) ---
            // 4. SOLUÇÃO PARA O ERRO: Se não houver user_id (auto-cadastro), usamos 0
            $usuario_id = $_SESSION['user_id'] ?? 0;
            
            $nome_registro = ($tipo == 'hospede') ? ($data['full_name'] ?? 'PF') : ($data['company_name'] ?? 'PJ');
            
            // Define se é uma criação interna ou externa
            $prefixo = ($usuario_id === 0) ? "AUTO_CADASTRO_" : "NOVO_";
            $origem = ($usuario_id === 0) ? " (Via Site/Externo)" : " (Por Funcionário ID: $usuario_id)";

            $acao_log = $prefixo . strtoupper($tipo);
            $detalhes_log = "Cadastrou um novo " . $tipo . ": " . $nome_registro . " (ID: " . $result . ")" . $origem;
            
            // Registra silenciosamente
            loggerModel::registrar($usuario_id, $acao_log, $detalhes_log);

        } else {
            $response['message'] = $result;
        }

    } catch (Exception $e) {
        $response['message'] = 'Erro interno: ' . $e->getMessage();
    }
}

echo json_encode($response);