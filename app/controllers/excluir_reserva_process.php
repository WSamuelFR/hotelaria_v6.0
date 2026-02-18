<?php
// Arquivo: app/controllers/excluir_reserva_process.php

// Inicia a sessão para auditoria
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Usamos __DIR__ para garantir que o caminho seja absoluto e evite erros de inclusão
require_once(__DIR__ . '/../models/reservaModel.php');
require_once(__DIR__ . '/../models/loggerModel.php');

header('Content-Type: application/json');

// Captura o corpo da requisição JSON (enviado pelo lobby.js ou reserva.js)
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

$reserva_id = $data['id'] ?? null;

// 1. Validação do ID
if (empty($reserva_id) || !is_numeric($reserva_id)) {
    echo json_encode(['success' => false, 'message' => 'ID da reserva inválido ou ausente.']);
    exit;
}

try {
    $model = new ReservaModel();
    
    // O Model já foi atualizado para fazer a exclusão lógica (SET situacao = 'cancelado')
    $sucesso = $model->excluirReserva((int)$reserva_id);

    if ($sucesso) {
        // --- LOG DE AUDITORIA ---
        // Registra quem fez a ação para segurança do hotel
        $usuario_id = $_SESSION['user_id'] ?? 0;
        loggerModel::registrar($usuario_id, 'CANCELAR_RESERVA', "Cancelou a reserva ID: $reserva_id");

        echo json_encode(['success' => true, 'message' => 'Reserva cancelada com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Não foi possível localizar ou atualizar a reserva no banco de dados.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro interno ao processar cancelamento: ' . $e->getMessage()]);
}