<?php
// Arquivo: app/controllers/edicao_reserva_process.php
// require_once(__DIR__ . '/../models/reservaModel.php');

header('Content-Type: application/json');

// Captura o JSON enviado pelo up_reserva.js
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Nenhum dado recebido para a edição.']);
    exit;
}

try {
    $model = new ReservaModel();

    /**
     * PADRONIZAÇÃO DE DADOS (Blindagem anti-erro 0)
     * Aqui garantimos que as chaves usadas pelo Model existam e estejam corretas,
     * mesmo que o JS use 'reserva_id' ou 'id'.
     */
    $reserva_id = $data['id'] ?? $data['reserva_id'] ?? null;

    if (!$reserva_id) {
        echo json_encode(['success' => false, 'message' => 'ID da reserva não identificado.']);
        exit;
    }

    $reserva_data = [
        'id'             => (int)$reserva_id,
        'quarto'         => $data['quarto'] ?? null,
        'data_checkin'   => $data['data_checkin'] ?? null,
        'data_checkout'  => $data['data_checkout'] ?? null,
        'titular_nome'   => $data['titular_nome'] ?? null,
        'titular_cpf'    => $data['titular_cpf'] ?? $data['titularCpf_cnpj'] ?? null,
        'titular_phone'  => $data['titular_phone'] ?? null,
        'email'          => $data['email'] ?? $data['titular_email'] ?? null,
        'acompanhantes'  => $data['acompanhantes'] ?? $data['hospedes'] ?? ''
    ];

    // Validação lógica de datas
    if (strtotime($reserva_data['data_checkin']) >= strtotime($reserva_data['data_checkout'])) {
        echo json_encode(['success' => false, 'message' => 'A data de check-out deve ser posterior ao check-in.']);
        exit;
    }

    // Chamada ao método updateReserva no Model passando o array padronizado
    $sucesso = $model->updateReserva($reserva_data);

    if ($sucesso) {
        echo json_encode([
            'success' => true, 
            'message' => 'Reserva atualizada com sucesso!'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Nenhuma alteração foi realizada ou reserva não encontrada.'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao processar edição: ' . $e->getMessage()
    ]);
}