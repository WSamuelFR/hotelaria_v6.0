<?php
// Arquivo: app/controllers/produto_process.php

require_once(__DIR__ . '/../models/produtoModel.php');
require_once(__DIR__ . '/../models/loggerModel.php');

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- BLOCO POST (CADASTRO OU VENDA) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $_GET['action'] ?? '';

    // 1. PRIORIDADE: LANÇAMENTO DE CONSUMO (VENDA)
    if ($action === 'lancar_consumo') {
        require_once(__DIR__ . '/../models/hospedagemModel.php');
        try {
            $hModel = new HospedagemModel();
            $sucesso = $hModel->registrarConsumo($data);

            if ($sucesso) {
                $usuario_id = $_SESSION['user_id'] ?? 0;
                loggerModel::registrar($usuario_id, 'VENDA_PRODUTO', "Venda de item ID: {$data['produto_id']} para hospedagem ID: {$data['hospedagem_id']}");
            }

            echo json_encode(['success' => $sucesso, 'message' => $sucesso ? 'Venda realizada!' : 'Erro ao registrar no banco.']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit; // Encerra aqui para não validar campos de cadastro de produto
    }

    // 2. CADASTRO DE NOVO PRODUTO (Lógica original)
    if (empty($data['nome']) || empty($data['preco'])) {
        echo json_encode(['success' => false, 'message' => 'Nome e Preço são obrigatórios para cadastro.']);
        exit;
    }

    try {
        $model = new ProdutoModel();
        $resultado = $model->insertProduto($data);

        if (isset($resultado['success']) && $resultado['success']) {
            $usuario_id = $_SESSION['user_id'] ?? 0;
            loggerModel::registrar($usuario_id, 'NOVO_PRODUTO', "Cadastrou o produto: " . $data['nome']);
        }

        echo json_encode($resultado);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// --- BLOCO GET (LISTAGEM) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $model = new ProdutoModel();
        $produtos = $model->listarTodos();
        echo json_encode(['success' => true, 'data' => $produtos]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// --- BLOCO DELETE (EXCLUSÃO) ---
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id || !is_numeric($id)) {
        echo json_encode(['success' => false, 'message' => 'ID do produto inválido.']);
        exit;
    }

    try {
        $model = new ProdutoModel();
        $sucesso = $model->deleteProduto((int)$id);

        if ($sucesso) {
            $usuario_id = $_SESSION['user_id'] ?? 0;
            loggerModel::registrar($usuario_id, 'EXCLUIR_PRODUTO', "Removeu o produto ID: $id do estoque.");
        }

        echo json_encode([
            'success' => $sucesso,
            'message' => $sucesso ? 'Produto removido com sucesso!' : 'Erro ao remover produto.'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
    }
    exit;
}