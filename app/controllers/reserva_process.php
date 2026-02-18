<?php
// Arquivo: app/controllers/reserva_process.php
require_once(__DIR__ . '/../models/reservaModel.php');

// Define que a resposta será sempre um JSON
header('Content-Type: application/json');

// Captura o corpo da requisição enviada pelo fetch (JSON)
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Nenhum dado recebido pelo servidor.']);
    exit;
}

try {
    $model = new ReservaModel();

    /**
     * NORMALIZAÇÃO DE DADOS (Blindagem anti-erro 0)
     * Aqui garantimos que as chaves usadas pelo Model existam, 
     * independente de como vieram do JavaScript.
     */
    $reserva_data = [
        'id'             => $data['id'] ?? $data['reserva_id'] ?? null,
        'quarto'         => $data['quarto'] ?? null,
        'data_checkin'   => $data['data_checkin'] ?? null,
        'data_checkout'  => $data['data_checkout'] ?? null,
        'titular_nome'   => $data['titular_nome'] ?? null,
        'titular_cpf'    => $data['titular_cpf'] ?? $data['titularCpf_cnpj'] ?? null,
        'titular_phone'  => $data['titular_phone'] ?? null,
        'email'          => $data['email'] ?? $data['titular_email'] ?? null,
        'acompanhantes'  => $data['acompanhantes'] ?? $data['hospedes'] ?? ''
    ];

    // Validação básica de campos obrigatórios
    if (empty($reserva_data['titular_nome']) || empty($reserva_data['quarto'])) {
        echo json_encode(['success' => false, 'message' => 'Nome do titular e quarto são obrigatórios.']);
        exit;
    }

    // Verifica se é uma atualização (UPDATE) ou nova reserva (INSERT)
    if (!empty($reserva_data['id'])) {
        // Tenta atualizar
        $sucesso = $model->updateReserva($reserva_data);
        
        echo json_encode([
            'success' => $sucesso, 
            'message' => $sucesso ? "Reserva atualizada com sucesso!" : "Nenhuma alteração foi realizada."
        ]);
    } else {
        // Tenta inserir
        $result = $model->insertReserva($reserva_data);
        
        // O Model retorna o ID (numérico) em caso de sucesso ou uma string em caso de erro
        if (is_numeric($result)) {
            echo json_encode([
                'success' => true, 
                'message' => "Reserva criada com sucesso!", 
                'id' => $result
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => "Erro ao criar reserva: " . $result
            ]);
        }
    }

} catch (Exception $e) {
    // Captura erros inesperados e retorna de forma amigável
    echo json_encode([
        'success' => false, 
        'message' => 'Erro interno no servidor: ' . $e->getMessage()
    ]);
}