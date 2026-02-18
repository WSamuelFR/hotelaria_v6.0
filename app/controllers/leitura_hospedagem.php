<?php
// Arquivo: app/controllers/leitura_hospedagem.php
require_once(__DIR__ . '/../models/hospedagemModel.php');

header('Content-Type: application/json');

// Define a ação (leitura padrão, consumo individual ou consumo total para checkout)
$action = $_GET['action'] ?? 'read';

try {
    $model = new HospedagemModel();

    switch ($action) {
        case 'get_consumo_total':
            // Busca todo o consumo da hospedagem para o Modal de Check-out
            $hospedagem_id = $_GET['hospedagem_id'] ?? null;
            if (!$hospedagem_id) {
                echo json_encode(['success' => false, 'message' => 'ID da hospedagem ausente.']);
                exit;
            }

            // CORREÇÃO: Usamos o método getConsumoTotal() que deve estar no Model ou executamos aqui via conexão da model
            // Para não quebrar, vamos buscar os dados através da conexão já aberta na classe Model
            $consumoTotal = $model->getConsumoTotalHospedagem((int)$hospedagem_id);
            
            echo json_encode(['success' => true, 'data' => $consumoTotal]);
            break;

        case 'get_consumo':
            $hospedagem_id = $_GET['hospedagem_id'] ?? null;
            $hospede_id = $_GET['hospede_id'] ?? null;

            if (!$hospedagem_id || !$hospede_id) {
                echo json_encode(['success' => false, 'message' => 'Parâmetros de consumo ausentes.']);
                exit;
            }

            $consumo = $model->getConsumoPorHospede((int)$hospedagem_id, (int)$hospede_id);
            echo json_encode(['success' => true, 'data' => $consumo]);
            break;

        case 'read':
        default:
            $id = $_GET['id'] ?? null;

            if (!$id || !is_numeric($id)) {
                echo json_encode(['success' => false, 'message' => 'ID de hospedagem inválido.']);
                exit;
            }

            $dados = $model->getHospedagemById((int)$id);

            if ($dados) {
                echo json_encode(['success' => true, 'data' => $dados]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Hospedagem não encontrada.']);
            }
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}